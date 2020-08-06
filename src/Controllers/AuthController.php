<?php 

namespace Selvi\Firebase\Controllers;
use Selvi\Firebase\Controller;

class AuthController extends Controller {

    function __construct() {
        parent::__construct();
        $this->validateRequest();
    }

    function get() {
        $data = (array) $this->penggunaAktif;
        $data['lembaga'] = (array) $this->lembagaAktif;
        $data['akses'] = (array) $this->aksesAktif;
        return jsonResponse($data);
    }

}