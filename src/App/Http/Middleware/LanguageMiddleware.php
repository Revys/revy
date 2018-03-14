<?php

namespace Revys\Revy\App\Http\Middleware;

use Closure;
use App;
use Revys\Revy\App\Revy;
use Revys\Revy\App\Language;
use Illuminate\Support\Facades\Route;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $request->segment(1);

        $language = Language::findByCode($locale);

        if ($language) {
            App::setLocale($language->code);
            $request->setDefaultLocale($language->code);
        } else {
            $request->setDefaultLocale(config('app.locale'));
        }

        return $next($request);
    }
}
