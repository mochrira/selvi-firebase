<?php 

namespace Selvi\Firebase;
use Selvi\Route;

class Lembaga {

    public static function setup() {
        Route::apiResource('lembaga', '\\Selvi\\Firebase\\Controllers\\LembagaController');
    }

}