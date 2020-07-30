<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class FcmToken extends Model {
    protected $schema = 'main';
    protected $table = 'fcmToken';
    protected $primary = 'id';
    protected $increment = true;

    function getByAkses($idLembaga, $akses) {
        $query = $this->db
            ->innerJoin('akses', 'akses.uid = fcmToken.uid')
            ->where([
                ['akses.idLembaga', $idLembaga],
                ['akses.tipe', $akses]
            ])
            ->get('fcmToken');
        $dataToken = $query->result();
        $tokens = [];
        foreach($dataToken as $fcmToken) {
            $tokens[] = $fcmToken->token;
        }
        return $tokens;
    }

    function getByUid($uid) {
        $query = $this->db
            ->where([['fcmToken.uid', $uid]])
            ->get('fcmToken');
        $dataToken = $query->result();
        $tokens = [];
        foreach($dataToken as $fcmToken) {
            $tokens[] = $fcmToken->token;
        }
        return $tokens;
    }

    function getByLembaga($idLembaga) {
        $query = $this->db
            ->innerJoin('akses', 'akses.uid = fcmToken.uid')
            ->where([
                ['akses.idLembaga', $idLembaga]
            ])
            ->get('fcmToken');
        $dataToken = $query->result();
        $tokens = [];
        foreach($dataToken as $fcmToken) {
            $tokens[] = $fcmToken->token;
        }
        return $tokens;
    }

}