<?php 

return function($schema, $direction) {

    if($direction == 'up') : 
        $schema->create('pengguna', [
            'uid' => 'varchar(150) NOT NULL PRIMARY KEY',
            'email' => 'varchar(255) NOT NULL',
            'displayName' => 'varchar(255) DEFAULT NULL',
            'photoUrl' => 'text'
        ], [
            'ENGINE' => 'InnoDB',
            'DEFAULT CHARSET' => 'latin1'
        ]);
    endif;

    if($direction == 'down') : 
        $schema->drop('pengguna');
    endif;

};