<?php 

namespace Selvi\Firebase;

use Selvi\Factory as SelviFactory;
use Selvi\Database\Manager as Database;
use Selvi\Exception;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

use Selvi\Firebase\Models\Pengguna;
use Selvi\Firebase\Models\Lembaga;
use Selvi\Firebase\Models\Origin;

class Loader {

    private static $dbConfig;
    private static $firebaseFactory;
    private static $clientMigrations = [];

    public static function setup($dbConfig, $serviceAccountFile) {
        self::$dbConfig = $dbConfig;
        $db = Database::add(self::$dbConfig, 'main')->addMigration(__DIR__.'/../migrations');
        self::$firebaseFactory = (new Factory())->withServiceAccount($serviceAccountFile);
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

    function __construct() {
        $this->firebaseAuth = self::$firebaseFactory->createAuth();
        $this->firebaseMessaging = self::$firebaseFactory->createMessaging();
        SelviFactory::load(Pengguna::class, [], 'pengguna');
        SelviFactory::load(Lembaga::class, [], 'lembaga');
    }

    public function getFactory() {
        return self::$firebaseFactory;
    }

    function validateRequest() {
        $this->validateOrigin();
        $this->validateToken();
        $this->validatePengguna();
        $this->validatePhoneNumber();
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

    function validateLembaga() {
        if($this->penggunaAktif) {
            $idLembaga = $this->penggunaAktif->idLembaga;
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

}