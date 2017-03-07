<?php namespace App\Console\Commands;

use App\Models\Location;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Unikent\Curl\Facades\Curl;

class SyncLocations extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'sync:locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronises Locations table with data from the Maps API';

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

        if(!Config::has('app.maps_api')){
            $this->error('Maps API not configured');
            die();
        }
        $curl = Curl::create(Config::get('app.maps_api') . '/locations');
        $curl->http_method('get');
        $curl->http_header('ACCEPT','application/json');
        $curl->ssl(false);
        $proxy = Config::get('app.maps_proxy',false);
        if(!empty($proxy) && strpos($proxy,':') > -1){
            list($proxyurl,$proxyport) = explode(':',$proxy);
            $curl->proxy($proxyurl, $proxyport);
        }
        $data = $curl->execute();
        if($data && !empty($data)){
            $data = json_decode($data);
            $existing = Location::lists('remote_id','id')->all();
            foreach($data as $location){
                if(!is_null($location->osm_data)) {
                    $osm = json_decode($location->osm_data);

                    $exists = array_search($location->id, $existing);
                    if ($exists) {
                        $l = Location::find($exists);
                        $this->info(' - Updating Location: ' . $l->name . ' (' . $exists . ')');
                    } else {
                        $l = new Location();
                        $l->remote_id = $location->id;
                        $this->info(' - Adding Location: ' . $location->name);
                    }
                    $l->name = $location->name;
                    $l->lat = $location->lat;
                    $l->lng = $location->lng;
                    $l->polygon = json_encode($osm, JSON_NUMERIC_CHECK);
                    $l->disabled_go_url = $location->disabled_go_url;
                    $l->save();
                }
            }
        }else{
            Curl::debug();
            $this->error('Empty or invalid data received from Maps API');
        }
    }

}
