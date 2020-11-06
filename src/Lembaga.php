<?php 

namespace Selvi\Firebase;
use Selvi\Firebase\Pengguna;
use Selvi\Database\Manager as Database;
use Selvi\Exception;

class Lembaga {

    private static $schema;
    private static $lembagaAktif;
    private static $aksesAktif;
    private static $dbName;

    static function setup($config = []) {
        if(isset($config['schema'])) {
            self::$schema = Database::get($config['schema']);
            self::$schema->addMigration(__DIR__.'/../migrations/lembaga');
        }

        if(isset($config['dbName'])) {
            self::$dbName = $config['dbName'];
        }
    }

    static function setupDatabase() {
        if(!self::$lembagaAktif) {
            throw new Exception('Pengguna belum terdaftar pada lembaga manapun', 'lembaga/invalid-akses');
        }
        
        try {
            $config = self::$schema->getConfig();
            Database::add([
                'host' => $config['host'],
                'username' => $config['username'],
                'password' => $config['password'],
                'database' => self::$lembagaAktif->basisData
            ], self::$dbName);
        } catch(Exception $e) {
            throw $e;
        } catch(\Exception $e) {
            throw new Exception($e->getMessage(), 'lembaga/unknown-error');
        }
    }

    static function validateLembaga() {
        if(!self::$aksesAktif) {
            throw new Exception('Akses tidak ditemukan', 'lembaga/invalid-akses');
        }

        try {
            self::$lembagaAktif = self::$schema->where([
                ['idLembaga', self::$aksesAktif->idLembaga]
            ]);
            if(!self::$lembagaAktif) {
                throw new Exception('Pengguna belum terdaftar di lembaga manapun', 'lembaga/invalid-akses');
            }
        } catch(Exception $e) {
            throw $e;
        } catch(\Exception $e) {
            throw new Exception($e->getMessage(), 'lembaga/unknown-error');
        }
    }

    static function validateAkses() {
        try {
            $pengguna = Pengguna::$penggunaAktif;
            self::$aksesAktif = self::$schema->where([
                ['uid', $pengguna->uid],
                ['default', 1]
            ]);
            if(!self::$aksesAktif) {
                throw new Exception('Pengguna belum terdaftar pada lembaga manapun', 'lembaga/invalid-akses');
            }
        } catch(Exception $e) {
            throw $e;
        } catch(\Exception $e) {
            throw new Exception($e->getMessage(), 'lembaga/unknown-error');
        }
    }

}