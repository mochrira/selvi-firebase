<?php 

namespace Selvi\Firebase;
use Selvi\Route;

class Undangan {

    public static function setup() {
        Route::post('/undangan/verify', '\\Selvi\\Firebase\\Controllers\\UndanganController@verify');
        Route::apiResource('undangan', '\\Selvi\\Firebase\\Controllers\\UndanganController', ['get', 'post', 'delete']);
    }

}