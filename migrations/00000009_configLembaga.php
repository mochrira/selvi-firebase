<?php 

return function ($schema, $direction) {
    
    if($direction == 'up') {
        $schema->dropPrimary()
            ->addColumnFirst('id', 'INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY')
            ->addColumn('idLembaga', 'INT(11)')
            ->alter('config');
        $schema->addColumn('joinCode', 'VARCHAR(50)')
            ->alter('lembaga');
        $schema->dropColumn('idUndangan')
            ->alter('akses');
        $schema->modifyColumn('tipe', 'VARCHAR(150) DEFAULT NULL')
            ->alter('akses');
    }

    if($direction == 'down') {
        $schema->where([['idLembaga', 'IS NOT', null]])->delete('config');
        $schema->dropPrimary()
            ->dropColumn('id')
            ->dropColumn('idLembaga')
            ->addPrimary('name')
            ->alter('config');
        $schema->dropColumn('joinCode')
            ->alter('lembaga');
        $schema->addColumn('idUndangan', 'INT(11)')
            ->alter('akses');
        $schema->modifyColumn('tipe', 'VARCHAR(150) NOT NULL')
            ->alter('akses');
    }

};