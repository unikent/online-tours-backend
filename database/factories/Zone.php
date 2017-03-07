<?php 

$factory->define('App\Models\Zone', function ($faker) {
    return [
		'leaf_id' => $faker->unique()->randomNumber(3),
		'name' => $faker->sentence(3),
		'slug' => $faker->unique()->slug(),
    ];
});