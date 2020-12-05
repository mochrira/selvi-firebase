<?php 

namespace Selvi\Firebase;
use Selvi\Route;

class Akses {

    public static function setup() {
        Route::apiResource('akses', '\\Selvi\\Firebase\\Controllers\\AksesController');
    }

}