<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class Pengguna extends Model {
    protected $schema = 'main';
    protected $table = 'pengguna';
    protected $primary = 'uid';
    protected $increment = false;
}