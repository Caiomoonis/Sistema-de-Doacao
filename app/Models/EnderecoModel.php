<?php

namespace App\Models;

use CodeIgniter\Model;

class EnderecoModel extends Model
{
    protected $table = 'endereco';

    protected $allowedFields = [
        'idReferencia',
        'tipo',
        'rua',
        'complemento',
        'bairro',
        'municipio',
        'estado',
        'local_doacao',
        'cep'
    ];
}