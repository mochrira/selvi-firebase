<?php 

return function ($db, $direction) {
    if($direction == 'up') :
        $db->create('lembaga', [
            'idLembaga' => 'int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'nmLembaga' => 'varchar(150) NOT NULL',
            'alamat' => 'text NOT NULL',
            'kota' => 'varchar(150) NOT NULL',
            'basisData' => 'varchar(150)',
            'photoUrl' => 'text',
            'tglRegistrasi' => 'datetime NOT NULL DEFAULT CURRENT_TIMESTAMP'
        ], [
            'ENGINE' => 'InnoDB',
            'DEFAULT CHARSET' => 'latin1'
        ]);

        $db->create('pengguna', [
            'uid' => 'varchar(150) NOT NULL PRIMARY KEY',
            'email' => 'varchar(255) NOT NULL',
            'emailVerified' => 'tinyint(1) NOT NULL DEFAULT \'0\'',
            'displayName' => 'varchar(255) DEFAULT NULL',
            'photoUrl' => 'text',
            'idLembaga' => 'int(11) DEFAULT NULL',
            'phoneNumber' => 'varchar(50) DEFAULT NULL',
            'akses' => 'varchar(50) DEFAULT NULL',
            'fcmToken' => 'text'
        ], [
            'ENGINE' => 'InnoDB',
            'DEFAULT CHARSET' => 'latin1'
        ]);
    endif;

    if($direction == 'down') :
        $db->drop('pengguna');
        $db->drop('lembaga');
    endif;
};