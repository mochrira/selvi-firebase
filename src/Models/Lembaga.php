<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class Lembaga extends Model {
    protected $schema = 'main';
    protected $table = 'lembaga';
    protected $primary = 'idLembaga';
    protected $increment = true;
}