<?php

use Illuminate\Database\Seeder;
use App\Models\User as User;
use Illuminate\Support\Facades\DB;

class UserTableSeeder extends Seeder {

	public function run()
	{	

		$webdev = ['gjmh','cs462','eg270','jna','msf4','jt353','tg264'];
		$ems = ['cs338', 'mlr'];

		// For each connection
		foreach (array_keys(array_diff_key(Config::get('database.connections'), array_flip(preg_grep('/^test_/', array_keys(Config::get('database.connections')))))) as $connection) {

			DB::connection($connection)->table('users')->truncate();

			// Add all the users
			$users = array_merge($webdev, $ems);
			foreach($users as $username){
				$user = new User();
				$user->setConnection($connection)->fill([
					'username'	=> $username,
					'email' 	=> $username.'@kent.ac.uk',
					'name' 		=> $username,
				])->save();
			}
		}
	}
}