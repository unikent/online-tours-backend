<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use App\Models\Location;

class LocationTableSeeder extends Seeder
{

    public function run()
    {
        Model::unguard();
        Location::truncate();
        Artisan::call('sync:locations');
    }
}