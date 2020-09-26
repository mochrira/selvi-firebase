<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class Config extends Model {
    protected $schema = 'main';
    protected $table = 'config';
    protected $primary = 'name';
    protected $increment = false;

    public function get($name, $idLembaga = null) {
        $where = [['name', $name]];
        if($idLembaga !== null) {
            $where[] = ['idLembaga', $idLembaga];
        } else {
            $where[] = ['idLembaga', 'IS', null];
        }
        $row = $this->row($where);
        return $row->value;
    }

}