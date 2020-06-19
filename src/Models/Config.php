<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class Config extends Model {
    protected $schema = 'main';
    protected $table = 'config';
    protected $primary = 'name';
    protected $increment = false;

    public function get($name) {
        $row = $this->row([['name', $name]]);
        return $row->value;
    }

}