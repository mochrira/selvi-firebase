<?php


namespace Selvi\Firebase\Controllers;

use Selvi\Firebase\Resource;
use Selvi\Exception;
use Selvi\Firebase\Models\Pengguna;

class PenggunaController extends Resource { 

    protected $modelClass = Pengguna::class;
    protected $modelAlias = 'Pengguna';
    
    function __construct() {
        parent::__construct();
        $this->validateRequest();
    }

    protected function afterUpdate($object = null) {
        if(!isset($object)) {
            Throw new Exception('pengguna/update-failed', 'Gagal mengambil object yang diupdate');
        }

        try {
            $dataUpdate = [];
            if(isset($object->displayName)) {
                $dataUpdate['displayName'] = $object->displayName;
            }

            if(isset($object->photoUrl)) {
                $dataUpdate['photoUrl'] = $object->photoUrl;
            }

            $this->firebaseAuth->updateUser($object->uid, $dataUpdate);
        } catch(\Exception $e) {
            Throw new Exception('pengguna/update-failed', $e->getMessage());
        }
    }

}