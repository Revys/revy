<?php

namespace Revys\Revy\Tests;

use Revys\Revy\App\Language;
use Revys\Revy\App\Revy;

trait Languages
{
    public static function mockLocale($locale = null)
    {
        if (empty($locale)) {
            $locale = config("app.locale");
        }

        $lang = Language::create([
            'code' => $locale,
            'title' => $locale
        ]);

        Revy::setLanguage($lang->code);

        return $locale;
    }
}