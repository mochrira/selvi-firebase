<?php 

namespace Selvi\Firebase;
use Selvi\Route;

class Messaging {

    public static function setup() {
        Route::post('/messaging', '\\Selvi\\Firebase\\Controllers\\MessagingController@updateToken');
    }

}