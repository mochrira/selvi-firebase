<?php 

namespace Selvi\Firebase\Controllers;
use Selvi\Firebase\Resource;
use Selvi\Firebase\Models\Akses;
use Selvi\Firebase\Models\Lembaga;

class AksesController extends Resource {

    protected $modelClass = Akses::class;
    protected $modelAlias = 'Akses';

    function __construct() {
        parent::__construct(false);
        if($this->input->method() == 'POST') {
            $this->validateToken();
            $this->validatePengguna();
        } else {
            $this->validateRequest();
        }
        $this->loadModel();
        $this->load(Lembaga::class, 'Lembaga');
    }

    function post() {
        $data = json_decode($this->input->raw(), true);
        if(!isset($data['joinCode'])) {
            throw new Exception('Masukkan kode join terlebih dahulu', 'join/invalid-request', 400);
        }
        
        $lembaga = $this->Lembaga->row([['joinCode', $data['joinCode']]]);
        if($lembaga == null) {
            throw new Exception('Kode tidak valid', 'join/invalid-code', 400);
        }

        $idAkses = $this->Akses->insert([
            'uid' => $this->penggunaAktif->uid,
            'idLembaga' => $lembaga->idLembaga,
            'isDefault' => 1
        ]);
        return jsonResponse(['idAkses' => $idAkses]);
    }

}