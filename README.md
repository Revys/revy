# Revy Framework

Wrapper for Laravel Framework.

## Features
- Multilanguage routes
- Images management

## Installation

1. Add to your composer.json
    ```
    "repositories": [
         {
             "type": "git",
             "url": "https://github.com/Revys/revy"
         }
    ]
    ```
3. Install package via *composer require*
    ```
    composer require revys/revy
    ```
    or add to your composer.json to **autoload** section and update your dependencies
    ```
    "revys/revy": "^0.0.1"
    ```
4. Run migrations
    ```
    php artisan migrate
    ```
5. Run seeder
    ```
    php artisan db:seed --class="Revys\Revy\Database\Seeds\DatabaseSeeder"
    ```
    
You are ready to go!




## Basic usage

### Routes 
You can easily set Multilanguage routes:
```php
use Revys\Revy\App\Routes;

// Set up the default route
Route::get('/', 'PageController@page');

// Specifies 'lang' middleware and '/{locale}' prefix 
Routes::withLanguage(function () {
    // Next line will set up routes like /{locale}/{page} to PageController@page
    Routes::definePageRoutes();

    // Set up you multilang routes
    // ...
    Route::get('example/page', 'ExampleController@method');
});
```