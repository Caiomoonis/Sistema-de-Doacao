<?php

namespace App\Models;

use CodeIgniter\Model;

class Cliente_Model extends Model
{
    protected $table = 'cliente';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nome',
        'email',
        'celular',
        'data_nas',
        'senha',
        'codigo_verificacao',
        'verificado',
        'ultimo_acesso'
    ];
}