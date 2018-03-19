<?php

namespace Revys\Revy\App;

use Revys\Revy\App\Revy;

class LanguageBase extends Entity
{
    protected static $locales;
    protected static $languages;
    protected static $languagesPublished;

    public function isSelected()
    {
        return ($this->code == Revy::getLanguage()->code);
    }

    public static function findByCode($code)
    {
        if (strlen($code) <> 2)
            return false;
            
        return self::where('code', $code)->first();
    }

    public static function getLocales($force = false)
    {
        if (self::$locales !== null and ! $force)
            return self::$locales;

        self::$locales = Language::get()->pluck('code')->toArray(); 

        return self::$locales;
    }
        
    public static function getLanguages($force = false)
    {
        if (self::$languagesPublished !== null and ! $force)
            return self::$languagesPublished;

        self::$languagesPublished = Language::published()->get(); 

        return self::$languagesPublished;
    }

    /**
     * @param bool $forse
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getLanguagesAll($forse = false)
    {
        if (self::$languages !== null and ! $forse)
            return self::$languages;

        self::$languages = Language::all(); 

        return self::$languages;
    }

    public static function translatePath($locale, $uri = false)
    {
        $count = 0;

        if ($uri == false)
            $uri = request()->path();

        if ($uri == '' or $uri == '/')
            return '/' . $locale;

        $uri = preg_replace(
            '/^\/([a-z]{2})(\/|$)/',
            '/' . $locale . '$2',
            $uri,
            1,
            $count
        );

        if ($count == 0)
            return '/' . $locale . $uri;

        return $uri;
    }
}
