<?php

use Faker\Generator as Faker;
use Revys\Revy\App\Language;

$factory->define(Language::class, function (Faker $faker) {
     return [
        'id' => 1,
        'name' => $faker->name,
        'field' => $faker->name
    ];
});