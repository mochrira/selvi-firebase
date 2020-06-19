<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class Akses extends Model {
    protected $schema = 'main';
    protected $table = 'akses';
    protected $primary = 'id';
    protected $increment = true;
}