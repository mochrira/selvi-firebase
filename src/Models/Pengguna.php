<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class Pengguna extends Model {
    protected $schema = 'main';
    protected $table = 'pengguna';
    protected $primary = 'uid';
    protected $increment = false;
    protected $searchable = ['pengguna.displayName', 'pengguna.email'];

    function getByLembaga($idLembaga, $where = [], $offset = 0, $limit = -1, $q = null) {
        $query = $this->db->select('pengguna.*, akses.tipe')
            ->innerJoin('akses', 'akses.uid = pengguna.uid AND akses.isDefault = 1')
            ->where([['akses.idLembaga', $idLembaga]])
            ->where($where)->orWhere($this->buildSearchable($q));
        if($limit > -1) {
            $query->limit($limit)->offset($offset);
        }
        return $query->get('pengguna')->result();
    }
}