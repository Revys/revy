<?php

namespace Revys\Revy\Database\Seeds;

use Illuminate\Database\Seeder;
use Revys\Revy\App\Entity;
use Revys\Revy\App\Language;
use Revys\Revy\App\Page;

class PageSeeder extends Seeder
{
    public function run()
    {
        Page::create([
            Entity::getUrlIDField() => 'index',
            Entity::getStringIdField() => 'index',
            'title' => 'Index page',
            'meta_title' => 'Index page',
            'meta_description' => 'The main page of the site',
            'meta_keywords' => 'index page'
        ]);
    }
}