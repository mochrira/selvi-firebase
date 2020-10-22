<?php 

return function ($schema, $direction) {
    if($direction == 'up') :
        $schema->create('origin', [
            'idOrigin' => 'INT(11) AUTO_INCREMENT PRIMARY KEY',
            'name' => 'VARCHAR(150)',
            'idLembaga' => 'INT(11)'
        ]);
    endif;

    if($direction == 'down') :
        $schema->drop('origin');
    endif;
};