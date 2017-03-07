<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class PublishToLive extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronises Live DB with Staging';

    /**
     * Create a new command instance.
     *
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire(){

        $tables = (DB::select('SHOW TABLES'));
        $staging_prefix = Config::get('database.connections.staging.prefix');
        $live_prefix = Config::get('database.connections.live.prefix');

        $files = array();
        foreach (glob(storage_path() . "/app/seeds/__[0-9]*.sql") as $file) {
            $ts = str_replace(storage_path() . "/app/seeds/__",'',$file);
            $ts = str_replace('.sql','',$ts);

            $files[$ts] = $file;
        }
        ksort($files,SORT_NUMERIC);

        while(count($files) > 4){
            @unlink(array_shift($files));
        }

        $timestamp = time();
        $filename = storage_path() . "/app/seeds/__" . $timestamp . ".sql";


        $db_table_name = 'Tables_in_'.env('DB_DATABASE');
        
        $staging_tables = array_values(array_filter(array_map(function($t) use ($staging_prefix, $db_table_name ){
            return preg_match('#^' . $staging_prefix . '.*#', $t->{$db_table_name} ) ? $t->{$db_table_name} : null;
        },$tables)));

        $live_tables = array_map(function($t) use ($staging_prefix, $live_prefix) {
            return $live_prefix . substr($t,strlen($staging_prefix));
        },$staging_tables);


        if(empty($files)){
            $this->info('No backups found so generating live backup');
            $backup_filename = storage_path() . "/app/seeds/__" . ($timestamp-10) . ".sql";
            exec("mysqldump -h" . Config::get('database.connections.live.host') . " -u". Config::get('database.connections.live.username')." -p". Config::get('database.connections.live.password')." ". Config::get('database.connections.live.database') . ' ' . implode(' ', $live_tables) . " > " . $backup_filename);
            if(!file_exists($backup_filename)) {
                $this->error('Failed to generate backup, aborting sync!');
                return 1;
            }
        }



        $this->info('Dumping Staging database to: '. $filename);

        exec("mysqldump -h" . Config::get('database.connections.staging.host') . " -u". Config::get('database.connections.staging.username')." -p". Config::get('database.connections.staging.password')." ". Config::get('database.connections.staging.database') . ' ' . implode(' ', $staging_tables) . " > " . $filename);

        $this->info('Dump Complete');

        if(file_exists($filename)) {

            $sql = file_get_contents($filename);

            if(empty($sql)){
                $this->error('Invalid dump, aborting sync!');
                @unlink($filename);
                return 1;
            }

            $this->info('Preparing Staging Dump for import');


            foreach($staging_tables as $i => $st){
                $sql = str_replace($st,$live_tables[$i] ,$sql);
            }
            file_put_contents($filename,$sql);

            $this->info('Importing Staging database to Live');

            $out = [];
            $return = null;
            exec("mysql -h " . Config::get('database.connections.live.host') . " -u ". Config::get('database.connections.live.username')." -p". Config::get('database.connections.live.password')." ". Config::get('database.connections.live.database')." < " . $filename, $out, $return);
            if($return!==0){
                $this->error('Import Failed. Rolling back.');
                $this->call('db:rollback');
            }else {
                $this->info('Import Complete. Live is up to date with Staging.');
            }
        }

    }

}
