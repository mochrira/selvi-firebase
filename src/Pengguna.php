<?php 

namespace Selvi\Firebase;
use Selvi\Route;

class Pengguna {

    public static function setup() {
        Route::apiResource('pengguna', '\\Selvi\\Firebase\\Controllers\\PenggunaController', ['get', 'patch', 'delete']);
    }

}