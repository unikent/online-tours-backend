<?php

$factory->define('App\Models\Leaf', function ($faker) {
    return [
        'name' => $faker->sentence(3),
	    'location_id' => $faker->unique()->randomNumber(3),
	    'slug' => $faker->unique()->lexify('??????'),
    ];
});