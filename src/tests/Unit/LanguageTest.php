<?php

namespace Revys\Revy\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Revys\Revy\App\Entity;
use Revys\Revy\App\Language;
use Revys\Revy\App\Revy;
use Revys\Revy\Tests\TestCase;

class LanguageTest extends TestCase
{
    use DatabaseMigrations;

    public static function createLanguages()
    {
        Language::create([
            'code'  => 'ru',
            'title' => 'Russian'
        ]);

        Language::create([
            'code'  => 'ro',
            'title' => 'Romanian'
        ]);
    }

    public function testFindByCode()
    {
        self::createLanguages();

        $language = Language::first();

        $this->assertEquals(Language::findByCode($language->code), $language);
    }

    public function testIsSelected()
    {
        self::createLanguages();

        $languages = Language::all();

        foreach ($languages as $language) {
            $selectedLanguage = Revy::setLanguage($language);
            $this->assertEquals($selectedLanguage, $language);
        }
    }

    public function test_getLocales()
    {
        self::createLanguages();

        $this->assertEquals([
            'ru', 'ro'
        ], Language::getLocales(true));

        // Create other
        Language::create([
            'code'  => 'en',
            'title' => 'English'
        ]);

        // Call without force attribute
        $this->assertEquals([
            'ru', 'ro'
        ], Language::getLocales());

        // Call with force attribute
        $this->assertEquals([
            'ru', 'ro', 'en'
        ], Language::getLocales(true));
    }


    public function test_getLanguages()
    {
        // Create published language
        $lang = Language::create([
            'code'   => 'ru',
            'title'  => 'Russian',
            'status' => Entity::STATUS_PUBLISHED
        ]);

        $this->assertEquals(
            [
                $lang->toArray()
            ],
            Language::getLanguages(true)->toArray()
        );

        // Create hidden language
        Language::create([
            'code'   => 'en',
            'title'  => 'English',
            'status' => Entity::STATUS_HIDDEN
        ]);

        // Without force attribute
        $this->assertEquals(
            [
                $lang->toArray()
            ],
            Language::getLanguages(true)->toArray()
        );

        // Create published language
        $lang2 = Language::create([
            'code'   => 'ro',
            'title'  => 'Romanian',
            'status' => Entity::STATUS_PUBLISHED
        ]);

        // Without force attribute
        $this->assertEquals(
            [
                $lang->toArray()
            ],
            Language::getLanguages()->toArray()
        );

        // With force attribute
        $this->assertEquals(
            [
                $lang->toArray(),
                $lang2->toArray()
            ],
            Language::getLanguages(true)->toArray()
        );
    }

    public function test_getLanguagesAll()
    {
        // Create language
        $lang = Language::create([
            'code'   => 'ru',
            'title'  => 'Russian',
            'status' => Entity::STATUS_PUBLISHED
        ]);

        $this->assertEquals(
            [
                $lang->toArray()
            ],
            Language::getLanguagesAll(true)->toArray()
        );

        // Create another language
        $lang2 = Language::create([
            'code'   => 'en',
            'title'  => 'English',
            'status' => Entity::STATUS_PUBLISHED
        ]);

        // Without force attribute
        $this->assertEquals(
            [
                $lang->toArray()
            ],
            Language::getLanguagesAll()->toArray()
        );

        // With force attribute
        $this->assertEquals(
            [
                $lang->toArray(),
                $lang2->toArray()
            ],
            Language::getLanguagesAll(true)->toArray()
        );
    }

    public function test_translatePath()
    {
        $this->assertEquals(
            '/ru',
            Language::translatePath('ru')
        );

        $this->assertEquals(
            '/ru',
            Language::translatePath('ru', '/')
        );

        $this->assertEquals(
            '/ru',
            Language::translatePath('ru', '/ro')
        );

        $this->assertEquals(
            '/ru/some/page',
            Language::translatePath('ru', '/ro/some/page')
        );

        $this->assertEquals(
            '/ru/some/page/',
            Language::translatePath('ru', '/ro/some/page/')
        );

        $this->assertEquals(
            '/ru/somepage',
            Language::translatePath('ru', '/somepage')
        );
    }


}
