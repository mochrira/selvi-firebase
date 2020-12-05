<?php 

namespace Selvi\Firebase;
use Kreait\Firebase\Factory;

class Manager {

    private static $firebaseFactory;

    static function setup($config = []) {
        if(isset($config['serviceAccountFile'])) {
            self::$firebaseFactory = (new Factory())->withServiceAccount($config['serviceAccountFile']);
        }
    }

    static function getFactory() {
        return self::$firebaseFactory;
    }
    
}