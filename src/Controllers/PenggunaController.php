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

    function validateData() {
        $data = json_decode($this->input->raw(), true);
        if(!isset($data)) {
            Throw new Exception('Invalid request', 'pengguna/invalid-request', 400);
        } else {
            if($this->input->method() == 'POST') {
                return $data;
            }
        }
    }
}