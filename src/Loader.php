<?php 

namespace Selvi\Firebase;

use Selvi\Factory as SelviFactory;
use Selvi\Database\Manager as Database;
use Selvi\Database\Migration;
use Selvi\Exception;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

use Selvi\Firebase\Models\Pengguna;
use Selvi\Firebase\Models\Lembaga;
use Selvi\Firebase\Models\Origin;
use Selvi\Firebase\Models\Akses;

class Loader {

    private static $dbConfig;
    private static $firebaseFactory;
    private static $clientMigrations = [];
    private static $validateOrigin = false;
    public static $dbPrefix = '';

    /**
     * Config Structure
     * ['dbConfig'] => Database Configuration
     *      ['host'] => Database Host
     *      ['username'] => Database Username
     *      ['password'] => Database Password
     *      ['database'] => Database Name
     * ['serviceAccountFile'] => Service account location
     * ['validateOrigin'] => Validate origin or not (boolean)
     */

    public static function setup($config) {
        self::$dbConfig = $config['dbConfig'];
        Database::add(self::$dbConfig, 'main')
            ->addMigration(__DIR__.'/../migrations');

        self::$firebaseFactory = (new Factory())->withServiceAccount($config['serviceAccountFile']);
        self::$validateOrigin = $config['validateOrigin'];
        self::$dbPrefix = $config['dbPrefix'];
    }

    public static function getDatabase() {
        return Database::get('main');
    }

    public static function addMainMigration($path) {
        self::getDatabase()->addMigration($path);
    }

    public static function addClientMigration($path) {
        if(!in_array($path, self::$clientMigrations)) {
            self::$clientMigrations[] = $path;
        }
    }

    private static $instance;

    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public $firebaseAuth;
    public $firebaseMessaging;
    public $firebaseToken;

    public $originAktif;
    public $penggunaAktif;
    public $lembagaAktif;
    public $aksesAktif;

    function __construct() {
        $this->firebaseAuth = self::$firebaseFactory->createAuth();
        $this->firebaseMessaging = self::$firebaseFactory->createMessaging();
        SelviFactory::load(Pengguna::class, [], 'pengguna');
        SelviFactory::load(Lembaga::class, [], 'lembaga');
        SelviFactory::load(Akses::class, [], 'akses');
    }

    public function getFactory() {
        return self::$firebaseFactory;
    }

    function validateRequest() {
        if(self::$validateOrigin == true) {
            $this->validateOrigin();
        }
        $this->validateToken();
        $this->validatePengguna();
        $this->validatePhoneNumber();
        $this->validateAkses();
        $this->validateLembaga();
        $this->setupDatabase();
    }

    function validatePublicRequest() {
        $this->validateOrigin();
        $this->validateLembaga();
        $this->setupDatabase();
    }

    function validateOrigin() {
        $input = SelviFactory::load('input');
        $input_origin = str_replace('https://', '', str_replace('http://', '', $input->server('HTTP_ORIGIN')));
        $origin = SelviFactory::load(Origin::class, [], 'origin');
        $this->originAktif = $origin->row([['origin', $input_origin]]);
        if(!$this->originAktif) {
            Throw new Exception('Origin not allowed to access this resources', 'app/invalid-origin', 400);
        }
    }

    function validateToken() {
        $input = SelviFactory::load('input');
        $token = $input->header('authorization');
        if(!$token) {
            $token = $input->header('Authorization');
        }
        try {
            $this->firebaseToken = $this->firebaseAuth->verifyIdToken($token);
        } catch(\Exception $e) {
            Throw new Exception($e->getMessage(), 'firebase-auth/invalid-token', 400);
        }
    }

    function validatePengguna() {
        $uid = $this->firebaseToken->getClaim('sub');
        $pengguna = SelviFactory::load(Pengguna::class, [], 'pengguna');
        $this->penggunaAktif = $pengguna->row([['uid', $uid]]);
        if(!$this->penggunaAktif) {
            if(!$pengguna->insert([
                'uid' => $uid,
                'email' => $this->firebaseToken->getClaim('email')
            ])) {
                Throw new Exception('Gagal menambahkan pengguna', 'firebase-auth/insert-failed', 500);
            }
            $this->penggunaAktif = $pengguna->row([['uid', $uid]]);
        }
    }

    function validatePhoneNumber() {
        if($this->penggunaAktif->phoneNumber == null) {
            Throw new Exception('Nomor HP belum diverifikasi', 'firebase-auth/unverified-number', 400);
        }
    }

    function validateAkses() {
        $akses = SelviFactory::load(Akses::class, [], 'akses');
        $this->aksesAktif = $akses->row([
            ['uid', $this->penggunaAktif->uid], 
            ['isDefault', true]
        ]);

        if(!$this->aksesAktif) {
            Throw new Exception('Anda belum memiliki akses ke lembaga manapun', 'firebase-auth/invalid-akses', 400);
        }
    }

    function validateLembaga() {
        if($this->aksesAktif) {
            $idLembaga = $this->aksesAktif->idLembaga;
            if(!$idLembaga) {
                Throw new Exception('Pengguna tidak terdaftar pada lembaga manapun', 'firebase-auth/invalid-lembaga', 400);
            }
        } else {
            $idLembaga = $this->originAktif->idLembaga;
            if(!$idLembaga) {
                Throw new Exception('Pengguna tidak terdaftar pada lembaga manapun', 'firebase-auth/invalid-lembaga', 400);
            }
        }

        $lembaga = SelviFactory::load(Lembaga::class, [], 'lembaga');
        $this->lembagaAktif = $lembaga->row([['idLembaga', $idLembaga]]);
        if(!$this->lembagaAktif) {
            Throw new Exception('Lembaga tidak ditemukan', 'firebase-auth/lembaga-not-found', 404);
        }
    }

    function setupDatabase() {
        $db = Database::add(array_merge(self::$dbConfig, ['database' => $this->lembagaAktif->basisData]), 'client');
        foreach(self::$clientMigrations as $path) {
            $db->addMigration($path);
        }
    }

    function checkDependency($schema, $file) {
        $db = Database::get($schema);
        if(!$db) { Throw new Exception('Instance database tidak dikenali', 'db/unknown-schema', 404); }

        $records = $db->where([['filename', basename($file)]])->limit(1)->order(['start' => 'desc'])->get('_migration');
        $latest = $cek->row();
        if($records->num_rows() == 0 || ($records->num_rows() > 0 && ($latest->output !== "success" || $latest->direction !== 'up'))) {
            Throw new Exception('Database butuh diupdate. Hubungi pemilik/pengelola lembaga untuk melakukan update', 'db/need-upgrade', 400);
        }
    }

}