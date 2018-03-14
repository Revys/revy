<?php

namespace Revys\Revy\App;

use Illuminate\Support\Facades\Request;
use Revys\Revy\Tests\TestCase;
use Route;

/**
 * Routes helper
 *
 * @package Revys\Revy\App
 */
class RoutesBase
{
    /**
     * Define routes for pages
     *
     * @return void
     */
    public static function definePageRoutes()
    {
        Route::get('/', 'PageController@page');

        Route::get('/{page}', 'PageController@page')->where([
            'page' => '[a-z_\-0-9]+'
        ])->name('page');
    }

    /**
     * Wraps routes with language prefix
     *
     * @param \Closure $callback
     * @param array $config
     */
    public static function withLanguage(\Closure $callback, $config = [])
    {
        if (\App::runningUnitTests()) {
            $locale = \App::getLocale();
        } else {
            $locale = request()->segment(1);
        }

        if (strlen($locale) <= 2) {
            Route::group(['prefix' => $locale, 'middleware' => 'lang'], function () use ($callback) {
                $callback();
            });
        }
    }
}

class Request2 extends Request
{

}