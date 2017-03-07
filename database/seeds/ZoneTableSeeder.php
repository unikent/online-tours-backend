<?php

use Illuminate\Database\Seeder;
use App\Models\Zone as Zone;

class ZoneTableSeeder extends Seeder {

    public function run()
    {

        Zone::truncate();

        Zone::create([
            'leaf_id'=>2,
            'name'=>'Canterbury',
            'slug'=>'canterbury',
            'sequence'=>1
        ]);
        Zone::create([
            'leaf_id'=>96,
            'name'=>'Medway Campus',
            'slug'=>'medway',
            'sequence'=>2
        ]);
    }
}