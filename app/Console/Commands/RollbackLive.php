<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class RollbackLive extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rolls back Live DB to previous revision';

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


        $files = array();
        foreach (glob(storage_path() . "/app/seeds/__[0-9]*.sql") as $file) {
            $ts = str_replace(storage_path() . "/app/seeds/__",'',$file);
            $ts = str_replace('.sql','',$ts);

            $files[$ts] = $file;
        }
        ksort($files,SORT_NUMERIC);

        if(count($files) > 1){
            @unlink(array_pop($files));
            $this->info('Restoring live database to last backup.');
            exec("mysql -h " . Config::get('database.connections.live.host') . " -u ". Config::get('database.connections.live.username')." -p". Config::get('database.connections.live.password')." ". Config::get('database.connections.live.database')." < " . array_pop($files));
            $this->info('Restore Complete');
        }else{
            $this->error('No backups found! Nothing to rollback to.');
            die(1);
        }

    }

}
