<?php

namespace App\Models;

use CodeIgniter\Model;

class DoadorModel extends Model
{
    protected $table = 'doador';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nome',
        'email',
        'celular',
        'data_nas',
        'senha',
        'codigo_verificacao', // 🔥 ADICIONA
        'verificado',
        'ultimo_acesso' // 🔥 ADICIONA
    ];


}