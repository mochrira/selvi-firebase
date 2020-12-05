<?php 

return function ($schema, $direction) {

    if($direction == 'up') :
        $cek = $schema->where([['name', 'TEMPLATE_UNDANGAN']])->get('config')->row();
        if(!$cek) {
            $schema->insert('config', [
                'name' => 'TEMPLATE_UNDANGAN', 
                'value' => 'Gunakan kode {{code}} untuk bergabung ke aplikasi'
            ]);
        }

        $schema->dropColumn('akses')
               ->dropColumn('idLembaga')
               ->dropColumn('fcmToken')
               ->alter('pengguna');

        $schema->create('akses', [
            'id' => 'INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY',
            'uid' => 'VARCHAR(150) NOT NULL',
            'idLembaga' => 'INT(11) NOT NULL',
            'tipe' => 'VARCHAR(150) NOT NULL',
            'idUndangan' => 'INT(11)',
            'isDefault' => 'TINYINT(1) NOT NULL DEFAULT 0'
        ]);

        $schema->create('fcmToken', [
            'id' => 'INT(11) AUTO_INCREMENT NOT NULL PRIMARY KEY',
            'uid' => 'VARCHAR(150) NOT NULL',
            'platform' => 'VARCHAR(150) NOT NULL',
            'token' => 'VARCHAR(150) NOT NULL'
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
    endif;

    if($direction == 'down') :
        $schema->where([['name', 'TEMPLATE_UNDANGAN']])->delete('config');

        $schema->addColumn('akses', 'VARCHAR(50)')
               ->addColumn('idLembaga', 'INT(11)')
               ->addColumn('fcmToken', 'TEXT')
               ->alter('pengguna');

        $schema->drop('akses');
        $schema->drop('fcmToken');
        $schema->drop('undangan');
    endif;

};