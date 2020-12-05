<?php 

return function ($schema, $direction) {

    if($direction == 'up') {
        $schema->create('config', [
            'name' => 'VARCHAR(150) NOT NULL PRIMARY KEY',
            'value' => 'TEXT'
        ]);
    }

    if($direction == 'down') {
        $schema->drop('config');
    }

};