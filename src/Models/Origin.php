<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class Origin extends Model {
    protected $schema = 'main';
    protected $table = 'origin';
    protected $increment = true;
    protected $primary = 'idOrigin';
}