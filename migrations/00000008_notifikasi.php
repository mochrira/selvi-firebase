<?php 

return function ($schema, $direction) {

    if($direction == 'up') {
        $schema->create('notifikasi', [
            'idNotifikasi' => 'INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'uid' => 'VARCHAR(150)',
            'tanggal' => 'DATETIME',
            'title' => 'VARCHAR(150)',
            'body' => 'TEXT',
            'data' => 'TEXT',
            'dibaca' => 'DATETIME'
        ]);
    }

    if($direction == 'down') {
        $schema->drop('notifikasi');
    }

};