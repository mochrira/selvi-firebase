<?php 

namespace Selvi\Firebase;
use Selvi\Route;

class Upgrade {

    public static function setup() {
        Route::get('/upgrade', '\\Selvi\\Firebase\\Controllers\\UpgradeController@info');
        Route::post('/upgrade', '\\Selvi\\Firebase\\Controllers\\UpgradeController@upgrade');
    }

}