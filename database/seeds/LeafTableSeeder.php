<?php

use Illuminate\Database\Seeder;
use App\Models\Leaf;
use App\Models\Location;

class LeafTableSeeder extends Seeder {

    public function run()
    {

        Leaf::truncate();


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
        if($data && !empty($data)) {
            $locations = json_decode($data);

            $leaves = [];
            foreach ($locations as $loc) {
                if ($loc->depth < 4) {
                    $location = Location::where('remote_id', '=', $loc->id)->first();
                    if (!empty($location)) {
                        $leaves[$loc->id] = Leaf::create([
                            'name'=>$location->name,
                            'location_id' => $location->id,
                            'slug' => str_random(6)
                        ]);
                    }
                }
            }
            foreach ($locations as $k => $loc) {
                if (array_key_exists($loc->id, $leaves) && array_key_exists($loc->parent_id, $leaves)) {
                    Leaf::find($leaves[$loc->id]->id)->makeChildOf(Leaf::find($leaves[$loc->parent_id]->id));
                }
            }
        }
    }
}
