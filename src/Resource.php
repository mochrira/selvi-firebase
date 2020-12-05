<?php 

namespace Selvi\Firebase;

use Selvi\Resource as SelviResource;
use Selvi\Firebase\Loader as Firebase;

class Resource extends SelviResource {

    private $firebase;

    public function __construct($autoloadModel = true) {
        parent::__construct($autoloadModel);
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