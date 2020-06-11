<?php 

return function($db, $direction) {

    if($direction == 'up') {
        $db->create('origin', [
            'id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY',
            'origin' => 'VARCHAR(150) NOT NULL',
            'idLembaga' => 'INT(11)'
        ]);
    }

    if($direction == 'down') {
        $db->drop('origin');
    }

};