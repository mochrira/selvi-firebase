<?php 

namespace Selvi\Firebase;
use Selvi\Firebase\Manager;
use Kreait\Firebase\Messaging\CloudMessage;

class Messaging {

    private $messaging;

    function __construct() {
        $this->messaging = Manager::getFactory()->createMessaging();
    }

    function send($args = []) {
        $message = CloudMessage::fromArray($args);
        $this->messaging->send($message);
    }

}