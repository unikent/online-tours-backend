<?php

$factory->define('App\Models\Location', function ($faker) {
    return [
	    'name' => $faker->sentence(3),
	    'lat' => $faker->randomFloat(5,-90,90),
	    'lng' => $faker->randomFloat(5,-180,180),
    ];
});