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
        parent::__construct(false);
        $this->validateRequest();
        $this->loadModel();
    }

    function get() {
        $uid = $this->uri->segment(2);
        if($uid == null) {
            $offset = $this->input->get("offset") ?: 0;
            $limit = $this->input->get("limit") ?: 30;
            $search = $this->input->get("search") ?: "";
            return jsonResponse($this->Pengguna->getByLembaga($this->lembagaAktif->idLembaga, $this->buildWhere(), $offset, $limit, $search));
        }
        return parent::get();
    }

    function validateData($data, $object = null) { 
        if($this->input->method() == 'PATCH') {
            try {
                $dataUpdate = [
                    'displayName' => $object->displayName,
                    'photoUrl' => $object->photoUrl
                ];
                
                if(isset($data['displayName'])) {
                    $dataUpdate['displayName'] = $data['displayName'];
                }
    
                if(isset($data['photoUrl'])) {
                    $dataUpdate['photoUrl'] = $data['photoUrl'];
                }
    
                $this->firebaseAuth->updateUser($object->uid, $dataUpdate);

                if(isset($data['email'])) {
                    $this->firebaseAuth->changeUserEmail($object->uid, $data['email']); 
                    $dataUpdate['email'] = $data['email'];
                }

                if(isset($data['password'])) {
                    $this->firebaseAuth->changeUserPassword($object->uid, $data['password']);
                }

                return $dataUpdate;
            } catch(\Exception $e) {
                Throw new Exception('pengguna/update-failed', $e->getMessage(), 500);
            }
        } else if($this->input->method == 'DELETE') {
            try {
                $this->load(Akses::class, 'Akses');
                $aksesOwner = $this->Akses->row([['uid', $object->uid], ['tipe', 'OWNER']]);
                if($aksesOwner !== null) {
                    $this->load(Lembaga::class, 'Lembaga');
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

}