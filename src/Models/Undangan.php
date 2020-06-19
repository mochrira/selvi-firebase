<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class Undangan extends Model {
    protected $schema = 'main';
    protected $table = 'undangan';
    protected $primary = 'id';
    protected $increment = true;
}