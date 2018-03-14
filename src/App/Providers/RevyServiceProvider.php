<?php

namespace Revys\Revy\App\Providers;

use Illuminate\Foundation\AliasLoader;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageServiceProvider;
use Revys\Revy\App\Revy;
use Revys\Revy\App\Overrides;
use Illuminate\Support\ServiceProvider;


/**
 * @todo Move providers initialization in composer.json
 */
class RevyServiceProvider extends ServiceProvider
{
    private static $packagePath = __DIR__.'/../../';
    private static $packageAlias = 'revy';

    public static function getPackageAlias()
    {
        return self::$packageAlias;
    }

    public static function getPackagePath()
    {
        return self::$packagePath;
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    {
        $this->load();

        $this->loadCommands();

        // Middlewares
        $router->aliasMiddleware('lang', \Revys\Revy\App\Http\Middleware\LanguageMiddleware::class);

        $this->initProviders();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Revy::class);
        $this->app->singleton(Overrides::class);

        $overrides = $this->app->make(Overrides::class);
        $overrides->register();
    }

    public function initProviders()
    {
        $this->app->register(
            ImageServiceProvider::class
        );

        $loader = AliasLoader::getInstance();
        $loader->alias('Image', Image::class);
        $loader->alias('Revy', Revy::class);
    }

    public function load()
    {
        // Routes
        $this->loadRoutesFrom(self::$packagePath.'/routes.php');

        // Views
        $this->publishes([
            self::$packagePath.'/resources/views' => resource_path('views/vendor/'.self::$packageAlias),
        ], 'views');

        // Translations    
        // $this->publishes([
        //     self::$packagePath.'/translations' => resource_path('lang/vendor/'.self::$packageAlias),
        // ], 'translations');

        $this->loadTranslationsFrom(self::$packagePath . '/translations', self::$packageAlias);
        $this->loadJsonTranslationsFrom(self::$packagePath . '/translations', self::$packageAlias);

        // Config
        $this->publishes([
            self::$packagePath.'/config/config.php' => config_path(self::$packageAlias.'/config.php'),
            self::$packagePath.'/config/translatable.php' => config_path(self::$packageAlias.'/translatable.php'),
        ], 'config');

        // Assets
        $this->publishes([
            self::$packagePath.'/assets' => public_path('vendor/'.self::$packageAlias),
        ], 'public');

        // Migrations
        $this->loadMigrationsFrom(self::$packagePath.'/database/migrations');
        $this->publishes([
            self::$packagePath.'/database/migrations/' => database_path('migrations')
        ], 'migrations');

        // Factories
        $this->publishes([
            self::$packagePath.'/database/factories' => database_path('factories')
        ], 'factories');
    }

    public function loadCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                'Revys\Revy\App\Console\Commands\Overrides\MakeOverrideClass',
                'Revys\Revy\App\Console\Commands\Overrides\IndexOverridesClass',
            ]);
        }
    }
}
