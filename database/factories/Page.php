<?php

$factory->define('App\Models\Page', function ($faker) {
    return [
	    'title' => $faker->sentence(3),
    ];
});