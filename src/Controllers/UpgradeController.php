<?php 

namespace Selvi\Firebase\Controllers;
use Selvi\Firebase\Controller;
use Selvi\Database\Migration;
use Selvi\Exception;

class UpgradeController extends Controller {

    function __construct() {
        parent::__construct(false);
        $this->validateToken();
        $this->validatePengguna();
        $this->validateAkses();
        $this->validateLembaga();
        $this->setupDatabase();
        $this->load(Migration::class, 'Migration');
    }

    function info() {
        try {
            $needUpgrade = $this->Migration->needUpgrade('client');
            return jsonResponse([
                'needUpgrade' => $needUpgrade
            ]);
        } catch(\Exception $e) {
            Throw new Exception('Gagal mengambil informasi upgrade', 'db/info-upgrade-failed', 500);
        }
    }

    function upgrade() {
        if($this->aksesAktif->tipe !== 'OWNER') {
            Throw new Exception('Hubungi pemilik/pengelola lembaga untuk melakukan update', 'db/not-allowed', 500);
        }

        try {
            $this->Migration->run('client', 'up', '--silent');
            return response('', 204);
        } catch(\Exception $e) {
            Throw new Exception('Gagal melakukan upgrade', 'db/upgrade-failed', 500);
        }
    }

}