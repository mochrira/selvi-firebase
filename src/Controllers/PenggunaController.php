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

    function buildWhere() {
        $where = [];
        $this->emitEvent('buildWhere', [&$where]);
        return $where;
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
        $validatedData = [];
        if($this->input->method() == 'PATCH') {
            try {
                $validatedData = [
                    'displayName' => $object->displayName,
                    'photoUrl' => $object->photoUrl
                ];
                
                if(isset($data['displayName'])) {
                    $validatedData['displayName'] = $data['displayName'];
                }
    
                if(isset($data['photoUrl'])) {
                    $validatedData['photoUrl'] = $data['photoUrl'];
                }
    
                $this->firebaseAuth->updateUser($object->uid, $validatedData);

                if(isset($data['email'])) {
                    $this->firebaseAuth->changeUserEmail($object->uid, $data['email']); 
                    $validatedData['email'] = $data['email'];
                }

                if(isset($data['password'])) {
                    $this->firebaseAuth->changeUserPassword($object->uid, $data['password']);
                }

            } catch(Exception $e) {
                Throw $e;
            } catch(\Exception $e) {
                Throw new Exception('pengguna/update-failed', $e->getMessage(), 500);
            }
        } 
        
        if($this->input->method() == 'DELETE') {
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
            } catch(Exception $e) {
                Throw $e;
            } catch(\Exception $e) {
                Throw new Exception('pengguna/update-failed', $e->getMessage());
            }
        }

        $this->emitEvent('pengguna', 'validateData', [&$validatedData]);
        return $validatedData;
    }

    function afterInsert($pengguna, &$response = null) {
        $this->emitEvent('pengguna', 'afterInsert', [$pengguna, &$response]);
    }

    function afterUpdate($pengguna, &$response = null) {
        $this->emitEvent('pengguna', 'afterUpdate', [$pengguna, &$response]);
    }

    function afterDelete($pengguna, &$response = null) {
        $this->emitEvent('pengguna', 'afterDelete', [$pengguna, &$response]);
    }

}