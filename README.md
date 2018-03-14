# Revy Framework

Wrapper for Laravel Framework.

## Features
- CRUD
- Translations management

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
3. Run 
    ```
    composer require revys/revy
    ```
    or add to your composer.json to **autoload** section and update your dependencies
    ```
    "revys/revy": "^0.0.1"
    ```
    
You are ready to go!



## Basic usage

### Routes 
You can easily set multilang routes:
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


## TODO
- Tests
- Sort images
- Resize images by specified types
- Cache instance
