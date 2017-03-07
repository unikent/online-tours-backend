<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

foreach(scandir(__DIR__) as $filename){
	if($filename !== 'ModelFactory.php' && !preg_match('/^\.+/',$filename)){
		require_once(realpath(__DIR__ . '/' . $filename));
	}
}
