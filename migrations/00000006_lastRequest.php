<?php 

return function($schema, $direction) {

    if($direction == 'up') {
        $schema->addColumn('lastRequest', 'INT(11)')->alter('pengguna');
    }

    if($direction == 'down') {
        $schema->dropColumn('lastRequest');
    }

};