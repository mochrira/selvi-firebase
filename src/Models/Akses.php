<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class Akses extends Model {

    protected $schema = 'main';
    protected $table = 'akses';
    protected $primary = 'id';
    protected $increment = true;
    protected $selectable = ['akses.*', 'pengguna.displayName', 'pengguna.email', 'lembaga.nmLembaga'];
    protected $searchable = ['pengguna.displayName', 'pengguna.email'];
    protected $join = [
        'inner' => [
            'pengguna' => 'pengguna.uid = akses.uid',
            'lembaga' => 'lembaga.idLembaga = akses.idLembaga'
        ]
    ];

    function getByLembaga($idLembaga, $where = [], $q = "", $offset = 0, $limit = 30) {
        $query = $this->db->select($this->selectable)
            ->innerJoin('pengguna', 'pengguna.uid = akses.uid')
            ->innerJoin('lembaga', 'lembaga.idLembaga = akses.idLembaga')
            ->where([['akses.idLembaga', $idLembaga]])
            ->where($where)->orWhere($this->buildSearchable($q));
        if($limit > -1) {
            $query->limit($limit)->offset($offset);
        }
        return $query->get('akses')->result();
    }

}