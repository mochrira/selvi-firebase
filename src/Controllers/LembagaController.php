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
        $this->validatePhoneNumber();
        if($this->input->method() !== 'POST') {
            $this->validateAkses();
            $this->validateLembaga();
            $this->setupDatabase();
        }
        $this->loadModel();
    }

    protected function validateData() {
        $data = json_decode($this->input->raw(), true);
        if(!isset($data['nmLembaga'])) {
            Throw new Exception('Periksa kembali isian anda', 'lembaga/insert-failed', 400);
        }
        if(!isset($data['alamat'])) {
            Throw new Exception('Periksa kembali isian anda', 'lembaga/insert-failed', 400);
        }
        if(!isset($data['kota'])) {
            Throw new Exception('Periksa kembali isian anda', 'lembaga/invalid-data', 400);
        }

        if($this->input->method() == 'POST') {
            try {
                $dbName = $this->generateDbName($data['nmLembaga']);
                $db = Firebase::getDatabase();
                $db->createDb($dbName);
            } catch(Exception $e) {
                Throw new Exception('Gagal membuat database', 'lembaga/create-db-failed', 500);
            }

            return array_merge($data, [
                'basisData' => $dbName,
                'tglRegistrasi' => date('Y-m-d H:i:s')
            ]);
        }
    }

    protected function afterInsert($object) {
        try {
            $this->load(Akses::class, 'Akses');
            $this->Akses->insert([
                'uid' => $this->penggunaAktif->uid,
                'idLembaga' => $object->idLembaga,
                'tipe' => 'OWNER'
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
    }

    private function generateDbName($nmLembaga) {
        $dbName = preg_replace('/[^a-zA-Z0-9\']/', '', $nmLembaga);
        $dbName = str_replace("'", '', $dbName);
        return strtolower(Firebase::$dbPrefix.substr(str_replace(' ', '', $dbName), 0, 5).'_'.substr(md5(time()), 0, 5));
    }

}