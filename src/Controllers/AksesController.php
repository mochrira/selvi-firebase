<?php 

namespace Selvi\Firebase\Controllers;
use Selvi\Firebase\Resource;
use Selvi\Firebase\Models\Akses;

class AksesController extends Resource {

    protected $modelClass = Akses::class;
    protected $modelAlias = 'Akses';

    function __construct() {
        parent::__construct();
        $this->validateRequest();
        $this->loadModel();
    }

    function buildWhere() {
        return [['uid', $this->penggunaAktif->uid]];
    }

    function get() {
        $aktif = $this->input->get('aktif');
        if($aktif !== null) {
            $akses = $this->Akses->row([['isDefault', 1]]);
            return jsonResponse($akses);
        }
        return parent::get();
    }

}