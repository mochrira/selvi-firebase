<?php 

namespace Selvi\Firebase;
use Selvi\Route;
use Kreait\Firebase\Messaging\RawMessageFromArray;
use Selvi\Firebase\Loader as SelviFirebase;

class Messaging {

    public static function setup() {
        Route::post('/messaging', '\\Selvi\\Firebase\\Controllers\\MessagingController@updateToken');
        Route::delete('/messaging', '\\Selvi\\Firebase\\Controllers\\MessagingController@revokeToken');
    }

    public static function getRawMessage($message) {
        return new RawMessageFromArray($message);
    }

    public static function sendToUID($uid) {
        
    }

}