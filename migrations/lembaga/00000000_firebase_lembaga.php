<?php 

return function ($schema, $direction) {
    if($direction == 'up') :
        $schema->create('lembaga', [
            'idLembaga' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'nmLembaga' => 'varchar(150) NOT NULL',
            'alamat' => 'text NOT NULL',
            'kota' => 'varchar(150) NOT NULL',
            'basisData' => 'varchar(150)'
        ], [
            'ENGINE' => 'InnoDB',
            'DEFAULT CHARSET' => 'latin1'
        ]);

        
        $schema->create('akses', [
            'id' => 'INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY',
            'uid' => 'VARCHAR(150) NOT NULL',
            'idLembaga' => 'INT(11) NOT NULL',
            'tipe' => 'VARCHAR(150) NOT NULL',
            'isDefault' => 'TINYINT(1) NOT NULL DEFAULT 0'
        ]);
    endif;

    if($direction == 'down') :
        $schema->drop('lembaga');
        $schema->drop('akses');
    endif;
};