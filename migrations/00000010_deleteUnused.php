<?php 

return function ($schema, $direction) {
    
    if($direction == 'up') {
        $schema->drop('config');
        $schema->drop('origin');
        $schema->drop('fcmToken');
        $schema->drop('notifikasi');
        $schema->drop('undangan');
    }

    if($direction == 'down') {
        $schema->create('config', [
            'name' => 'VARCHAR(150) NOT NULL PRIMARY KEY',
            'value' => 'TEXT'
        ]);
        $schema->create('origin', [
            'id' => 'INT(11) AUTO_INCREMENT PRIMARY KEY',
            'origin' => 'VARCHAR(150) NOT NULL',
            'idLembaga' => 'INT(11)'
        ]);
        $schema->create('fcmToken', [
            'id' => 'INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY',
            'uid' => 'VARCHAR(150) NOT NULL',
            'platform' => 'VARCHAR(150) NOT NULL',
            'token' => 'VARCHAR(150) NOT NULL'
        ]);
        $schema->create('notifikasi', [
            'idNotifikasi' => 'INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'uid' => 'VARCHAR(150)',
            'tanggal' => 'DATETIME',
            'title' => 'VARCHAR(150)',
            'body' => 'TEXT',
            'data' => 'TEXT',
            'dibaca' => 'DATETIME'
        ]);
        $schema->create('undangan', [
            'id' => 'INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY',
            'phoneNumber' => 'VARCHAR(50)',
            'email' => 'VARCHAR(255)',
            'code' => 'VARCHAR(50) NOT NULL',
            'idLembaga' => 'INT(11) NOT NULL',
            'akses' => 'VARCHAR(150) NOT NULL',
            'status' => 'VARCHAR(150) NOT NULL DEFAULT \'SENT\'',
            'iat' => 'DATETIME NOT NULL',
            'uat' => 'DATETIME'
        ]);
    }

};