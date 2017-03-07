<?php

$factory->define('App\Models\User', function ($faker) {
    return [
		'username' => $faker->userName(),
		'name' => $faker->name(),
		'email' => $faker->email(),
    ];
});