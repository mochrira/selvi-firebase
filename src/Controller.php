<?php 

namespace Selvi\Firebase;

use Selvi\Controller as SelviController;
use Selvi\Firebase\Loader as Firebase;

class Controller extends SelviController {

    private $firebase;

    public function __construct() {
        parent::__construct();
        $this->firebase = Firebase::getInstance();
    }

    public function __get($name) {
        if(isset($this->firebase->{$name})) {
            return $this->firebase->{$name};
        }
        return parent::__get($name);
    }

    public function __call($name, $args = []) {
        if(method_exists($this->firebase, $name)) {
            $result = $this->firebase->{$name}(...$args);
            if($result !== null) {
                return $result;
            }
        }
    }

}