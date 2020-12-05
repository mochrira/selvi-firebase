<?php 

namespace Selvi\Firebase;
use Selvi\Route;

class Photo {

    public static $path;
    public static function setup($path) {
        self::$path = $path;
        Route::post('/photos', '\\Selvi\\Firebase\\Controllers\\PhotoController@upload');
        Route::get('/photos/(:any)', '\\Selvi\\Firebase\\Controllers\\PhotoController@get');
    }

}