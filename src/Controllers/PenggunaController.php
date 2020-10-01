<?php


namespace Selvi\Firebase\Controllers;

use Selvi\Firebase\Resource;
use Selvi\Exception;
use Selvi\Firebase\Models\Pengguna;
use Selvi\Firebase\Loader as Firebase;
use Selvi\Firebase\Models\Akses;
use Selvi\Firebase\Models\Lembaga;

class PenggunaController extends Resource { 

    protected $modelClass = Pengguna::class;
    protected $modelAlias = 'Pengguna';
    
    function __construct() {
        parent::__construct();
        $this->validateRequest();        
        $this->load(Akses::class, 'Akses');
        $this->load(Lembaga::class, 'Lembaga');
    }

    protected function beforeUpdate($object) {
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

    protected function beforeDelete($object) {
        try {
            $aksesOwner = $this->Akses->row([['uid', $object->uid], ['tipe', 'OWNER']]);
            if($aksesOwner !== null) {
                $lembaga = $this->Lembaga->row([['idLembaga', $aksesOwner->idLembaga]]);
                $db = Firebase::getDatabase();
                $db->dropSchema($lembaga->basisData);
                $this->Akses->delete([['id', $aksesOwner->id]]);
                $this->Lembaga->delete([['idLembaga', $lembaga->idLembaga]]);
            }
            $this->Akses->delete([['uid', $object->uid]]);
            $this->firebaseAuth->deleteUser($object->uid);
        } catch(\Exception $e) {
            Throw new Exception('pengguna/update-failed', $e->getMessage());
        }
    }

}