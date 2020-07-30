<?php 

namespace Selvi\Firebase;
use Selvi\Route;
use Kreait\Firebase\Messaging\RawMessageFromArray;

class Messaging {

    public static function setup() {
        Route::post('/messaging', '\\Selvi\\Firebase\\Controllers\\MessagingController@updateToken');
        Route::delete('/messaging', '\\Selvi\\Firebase\\Controllers\\MessagingController@revokeToken');
    }

    public static function getRawMessage($message) {
        return new RawMessageFromArray($message);
    }

}