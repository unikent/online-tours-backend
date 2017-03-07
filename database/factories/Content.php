<?php

$factory->define('App\Models\Content', function ($faker) use ($factory) {
    return [
        'type' => 'text',
        'name' => $faker->sentence(3),
        'value' => implode("\r\n", $faker->paragraphs(rand(1, 4))),
        'meta' => null,
    ];
});

$factory->define('App\Models\Content\Text', function ($faker) use ($factory) {
    return [
        'type' => 'text',
        'name' => $faker->sentence(3),
        'value' => implode("\r\n", $faker->paragraphs(rand(1, 4))),
        'meta' => null,
    ];
});

$factory->define('App\Models\Content\Image', function ($faker) use ($factory) {
    return [
        'type' => 'image',
        'name' => $faker->sentence(3),
        'value' => 'cornwallis_1234567890.jpg',
        'meta' => [
            'width' => 1960,
            'height' => 1307,
            'title' => $faker->sentence(rand(1, 3)),
            'caption' => $faker->sentence(rand(5, 10)),
            'copyright' => $faker->sentence(rand(2, 6))
        ],
    ];
});

$factory->define('App\Models\Content\Audio', function ($faker) use ($factory) {
    return [
        'type' => 'audio',
        'name' => $faker->sentence(3),
        'value' => 'cornwallis_1234567890.mp3',
        'meta' => [
            'title' => $faker->sentence(rand(1, 3)),
            'transcription' => implode(' ', $faker->paragraphs(rand(1, 4)))
        ],
    ];
});

$factory->define('App\Models\Content\Video', function ($faker) use ($factory) {
    return [
        'type' => 'video',
        'name' => $faker->sentence(3),
        'value' => "https://www.youtube.com/embed/MBJXoeqXGks",
        'meta' => [
            'title' => $faker->sentence(rand(1, 3)),
            'transcription' => implode(' ', $faker->paragraphs(rand(1, 4)))
        ],
    ];
});
