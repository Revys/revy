<?php

namespace Revys\Revy\App;

use App;
use Revys\Revy\App\Language;
use Revys\Revy\App\Providers\RevyServiceProvider;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class RevyBase
{
    protected static $language;

    public static function getPackageAlias()
    {
        return RevyServiceProvider::getPackageAlias();
    }

    public static function getPackagePath($path = '')
    {
        return RevyServiceProvider::getPackagePath() . ($path != '' ? $path : '');
    }

    /**
     * Get application Language instance
     * @return Language
     */
    public static function getLanguage()
    {
        if (! self::$language) {
            self::$language = Language::findByCode(App::getLocale());
        }

        if (! self::$language) {
            abort(404, 'Language could not be found');
        }

        return self::$language;
    }

    /**
     * Set application Language instance
     * @return Language
     */
    public static function setLanguage($language)
    {
        if (! ($language instanceof Language)) {
            $language = Language::findByCode($language);
        }

        if (! $language) {
            return false;
        }

        App::setLocale($language->code);
        request()->setDefaultLocale($language->code);
        self::$language = $language;

        return $language;
    }

    /**
     * Get application Language code
     */
    public static function getLocale()
    {
        return self::getLanguage()->code;
    }

    public static function assertAjax()
    {
        if (! \Request::ajax()) {
            throw new MethodNotAllowedHttpException([], 'Only for Ajax request');
        }
    }
}
