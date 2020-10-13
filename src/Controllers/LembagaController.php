<?php 

namespace Selvi\Firebase\Controllers;
use Selvi\Database\Migration;
use Selvi\Firebase\Resource;
use Selvi\Firebase\Loader as Firebase;
use Selvi\Firebase\Models\Lembaga;
use Selvi\Firebase\Models\Akses;
use Selvi\Exception;

class LembagaController extends Resource {

    protected $modelClass = Lembaga::class;
    protected $modelAlias = 'Lembaga';

    function __construct() {
        parent::__construct(false);
        $this->validateToken();
        $this->validatePengguna();
        if($this->input->method() !== 'POST') {
            $this->validateAkses();
            $this->validateLembaga();
            $this->setupDatabase();
        }
        $this->loadModel();
    }

    function validateData($data, $lembaga = null) {
        if($this->input->method() == 'POST') {
            if(!isset($data['nmLembaga'])) {
                Throw new Exception('Periksa kembali isian anda', 'lembaga/invalid-request', 400);
            }
            if(!isset($data['alamat'])) {
                Throw new Exception('Periksa kembali isian anda', 'lembaga/invalid-request', 400);
            }
            if(!isset($data['kota'])) {
                Throw new Exception('Periksa kembali isian anda', 'lembaga/invalid-request', 400);
            }

            $this->load(Akses::class, 'Akses');
            $cek = $this->Akses->row([['uid', $this->penggunaAktif->uid]]);
            if($cek !== null) {
                if(!isset($data['kota'])) {
                    Throw new Exception('Anda telah tergabung dalam lembaga', 'lembaga/akses-exist', 400);
                }
            }

            try {
                $dbName = $this->generateDbName($data['nmLembaga']);
                $db = Firebase::getDatabase();
                $db->createDb($dbName);
            } catch(Exception $e) {
                Throw new Exception('Gagal membuat database', 'lembaga/create-db-failed', 500);
            }

            $data['basisData'] = $dbName;
            $data['tglRegistrasi'] = date('Y-m-d H:i:s');
            $data['joinCode'] = $this->generateJoinCode();
        }

        if($this->input->method() == 'PATCH') {
            $action = $this->input->get('action');
            if($action !== null && $action == 'resetJoinCode') {
                $data['joinCode'] = $this->generateJoinCode();
            } else {
                if(!isset($data['nmLembaga'])) {
                    Throw new Exception('Periksa kembali isian anda', 'lembaga/invalid-request', 400);
                }
                if(!isset($data['alamat'])) {
                    Throw new Exception('Periksa kembali isian anda', 'lembaga/invalid-request', 400);
                }
                if(!isset($data['kota'])) {
                    Throw new Exception('Periksa kembali isian anda', 'lembaga/invalid-request', 400);
                }
            }
        }

        if($this->input->method() == 'DELETE') {
            $db = Firebase::getDatabase();
            $db->dropSchema($object->basisData);
            $this->load(Akses::class, 'Akses');
            $this->Akses->delete([['idLembaga', $object->idLembaga]]);
        }

        $this->emitEvent('OnLembagaValidateData', [$data]);
        return $data;
    }

    function afterUpdate($lembaga, &$response = null) {
        if($this->input->method() == 'PATCH' && $this->input->get('action') == 'resetJoinCode') {
            $response->setCode(200);
            $response->setContent(json_encode([
                'joinCode' => $lembaga->joinCode
            ]));
        }
    }

    function afterInsert($lembaga, &$response = null) {
        try {
            $this->load(Akses::class, 'Akses');
            $this->Akses->insert([
                'uid' => $this->penggunaAktif->uid,
                'idLembaga' => $lembaga->idLembaga,
                'tipe' => 'OWNER',
                'isDefault' => true
            ]);
        } catch(Exception $e) {
            Throw new Exception('Gagal menambahkan akses pengguna', 'lembaga/insert-akses-failed', 500);
        }

        try {
            $this->validateAkses();
            $this->validateLembaga();
            $this->setupDatabase();

            $this->load(Migration::class, 'Migration');
            $this->Migration->run('client', 'up', '--silent');
        } catch(Exception $e) {
            Throw new Exception('Gagal menambahkan akses pengguna', 'lembaga/insert-akses-failed', 500);
        }

        $this->emitEvent('OnLembagaAfterInsert', [$lembaga, $response]);
    }

    private function generateJoinCode() {
        $allowedChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $found = true;
        while($found) {
            $code = '';
            for($i = 0; $i <= 4; $i++) {
                $code .= substr($allowedChars, rand(0, strlen($allowedChars) - 1), 1);
            }
            $cek = $this->Lembaga->row([['joinCode', $code]]);
            if($cek == null) {
                $found = false;
            } else {
                $found = true;
            }
        }
        return $code;
    }

    private function generateDbName($nmLembaga) {
        $dbName = preg_replace('/[^a-zA-Z0-9\']/', '', $nmLembaga);
        $dbName = str_replace("'", '', $dbName);
        return strtolower(Firebase::$dbPrefix.substr(str_replace(' ', '', $dbName), 0, 5).'_'.substr(md5(time()), 0, 5));
    }

}