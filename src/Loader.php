<?php 

namespace Selvi\Firebase;

use Selvi\Factory as SelviFactory;
use Selvi\Database\Manager as Database;
use Selvi\Exception;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

use Selvi\Firebase\Models\Pengguna;
use Selvi\Firebase\Models\Lembaga;

class Loader {

    private static $dbConfig;
    private static $firebaseFactory;

    public static function setup($dbConfig, $serviceAccountFile, $dbMigration) {
        self::$dbConfig = $dbConfig;
        $db = Database::add(self::$dbConfig, 'main');
        if(isset($dbMigration['main'])) {
            foreach($dbMigration['main'] as $path) {
                $db->addMigration($path);
            }
        }
        self::$firebaseFactory = (new Factory())->withServiceAccount($serviceAccountFile);
    }

    public static function getDatabase() {
        return Database::get('main');
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
        $this->validateToken();
        $this->validatePengguna();
        $this->validatePhoneNumber();
        $this->validateLembaga();
        $this->setupDatabase();
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
        $idLembaga = $this->penggunaAktif->idLembaga;
        if(!$idLembaga) {
            Throw new Exception('Pengguna tidak terdaftar pada lembaga manapun', 'firebase-auth/invalid-lembaga', 400);
        }

        $lembaga = SelviFactory::load(Lembaga::class, [], 'lembaga');
        $this->lembagaAktif = $lembaga->row([['idLembaga', $idLembaga]]);
        if(!$this->lembagaAktif) {
            Throw new Exception('Lembaga tidak ditemukan', 'firebase-auth/lembaga-not-found', 404);
        }
    }

    function setupDatabase() {
        $db = Database::add(
            array_merge(self::$dbConfig, ['database' => $this->lembagaAktif->basisData]), 'client'
        )->addMigration();
        if(isset($dbMigration['client'])) {
            foreach($dbMigration['client'] as $path) {
                $db->addMigration($path);
            }
        }
    }

}