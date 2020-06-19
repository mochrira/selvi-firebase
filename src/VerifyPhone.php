<?php 

namespace Selvi\Firebase;
use Selvi\Route;
use Selvi\Database\Manager as Database;

class VerifyPhone {

    public static $method;
    public static $key;
    public static $iv;

    public static function setup($config = []) {
        self::$method = isset($config['method']) ? $config['method'] : 'AES-256-CBC';
        self::$key = isset($config['key']) ? $config['key'] : 'RDF?jq8eec';
        self::$iv = isset($config['iv']) ? $config['iv'] : "6akdpd29q9x65zqx";
        Route::post('/verify/phone', '\\Selvi\\Firebase\\Controllers\\VerifyPhoneController@getToken');
        Route::patch('/verify/phone', '\\Selvi\\Firebase\\Controllers\\VerifyPhoneController@verifyToken');
    }

}