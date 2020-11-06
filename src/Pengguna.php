<?php 

namespace Selvi\Firebase;
use Selvi\Exception;
use Selvi\Factory;
use Selvi\Input;
use Selvi\Database\Manager as Database;
use Selvi\Firebase\Manager as FirebaseManager;

class Pengguna {

    private static $schema;
    static $firebaseAuth;
    static $firebaseToken;
    static $penggunaAktif;

    static function setup($config = []) {
        if(isset($config['schema'])) {
            self::$schema = Database::get($config['schema']);
            self::$schema->addMigration(__DIR__.'/../migrations/pengguna');
        }
    }

    static function validateToken() {
        try {
            $input = Factory::load(Input::class, [], 'input');
            $token = $input->header('authorization');
            if(!$token) {
                $token = $input->header('Authorization');
            }
            self::$firebaseAuth = FirebaseManager::getFactory()->createAuth();
            self::$firebaseToken = self::$firebaseAuth->verifyIdToken($token);
        } catch(Exception $e) {
            throw $e;
        } catch(\Exception $e) {
            throw new Exception($e->getMessage(), 'auth/invalid-token');
        }
    }

    static function validatePengguna() {
        try {
            $uid = self::$firebaseToken->getClaim('sub');
            self::$penggunaAktif = self::$schema->where([['uid', $uid]])->get('pengguna')->row();
            if(!self::$penggunaAktif) {
                $firebaseUser = self::$firebaseAuth->getUser($uid);
                if(!self::$schema->insert('pengguna', [
                    'uid' => $uid,
                    'displayName' => $firebaseUser->displayName,
                    'photoUrl' => $firebaseUser->photoUrl,
                    'email' => $firebaseUser->email
                ])) {
                    Throw new Exception('Gagal menambahkan pengguna', 'firebase-auth/insert-failed', 500);
                }
                self::$penggunaAktif = self::$schema->where([['uid', $uid]])->get('pengguna')->row();
            }
        } catch(Exception $e) {
            throw e;
        } catch(\Exception $e) {
            throw new Exception($e->getMessage(), 'auth/invalid-token');
        }
    }

}