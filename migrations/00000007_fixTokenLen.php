<?php 

return function ($schema, $direction) {

    if($direction == 'up') {
        $schema->modifyColumn('token', 'TEXT')->alter('fcmToken');
    }

    if($direction == 'down') {
        $schema->modifyColumn('token', 'VARCHAR(150)')->alter('fcmToken');
    }

};