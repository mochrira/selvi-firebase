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

}