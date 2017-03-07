<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DevelopmentSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$this->call('UserTableSeeder');
        $this->call('LocationTableSeeder');
        $this->call('LeafTableSeeder');
        $this->call('ZoneTableSeeder');
        $this->call('TourTableSeeder');
        $this->call('ContentTableSeeder');

	}

}
