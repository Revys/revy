<?php

use Faker\Generator as Faker;
use Revys\Revy\App\Entity;
use Revys\Revy\App\Page;

$factory->define(Page::class, function (Faker $faker) {
    return [
        Entity::getUrlIDField()    => $faker->slug,
        Entity::getStringIdField() => $faker->word,
        'title'                    => $faker->word,
        'meta_title'               => $faker->word,
        'meta_description'         => $faker->sentence,
        'meta_keywords'            => $faker->words(5, true)
    ];
});