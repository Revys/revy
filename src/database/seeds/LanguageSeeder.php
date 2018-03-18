<?php

namespace Revys\Revy\Database\Seeds;

use Illuminate\Database\Seeder;
use Revys\Revy\App\Language;

class LanguageSeeder extends Seeder
{
    private $languages;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->languages = ['ru', 'en'];

        foreach ($this->languages as $language) {
            Language::create(
                $this->languages()[$language]
            );
        }
    }

    private function languages()
    {
        return [
            'ru' => [
                'code'  => 'ru',
                'title' => 'Русский'
            ],
            'ro' => [
                'code'  => 'ro',
                'title' => 'Română'
            ],
            'en' => [
                'code'  => 'en',
                'title' => 'English'
            ]
        ];
    }
}
