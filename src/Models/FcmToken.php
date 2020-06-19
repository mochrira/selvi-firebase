<?php 

namespace Selvi\Firebase\Models;
use Selvi\Model;

class FcmToken extends Model {
    protected $schema = 'main';
    protected $table = 'fcmToken';
    protected $primary = 'id';
    protected $increment = true;
}