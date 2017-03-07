<?php

$factory->define('App\Models\Tour', function ($faker) {
    return [
		'name' => $faker->sentence(3),
		'description' => $faker->realText(),
		'leaf_id' => 1,
		'items' => '',
		'duration' => $faker->numberBetween(10, 120),
		'polyline' => '',
    ];
});