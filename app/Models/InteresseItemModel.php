<?php

namespace App\Models;

use CodeIgniter\Model;

class InteresseItemModel extends Model
{
    protected $table = 'interesse';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'idRoupa',
        'idCliente',
        'data_interesse'
    ];

    public function inserir($data)
    {
        return $this->insert($data);
    }

    public function get_interessados_por_item($item_id)
{
    return $this->select('
            cliente.nome,
            cliente.celular,
            cliente.email,
            interesse.data_interesse,
            roupas.tipo,
            roupas.cor,
            roupas.imagem,
            roupas.id as idRoupa
        ')
        ->join('cliente', 'cliente.id = interesse.idCliente')
        ->join('roupas', 'roupas.id = interesse.idRoupa') // 🔥 NOVO JOIN
        ->where('interesse.idRoupa', $item_id)
        ->findAll();
}

    public function historicoCliente($idCliente)
    {
        return $this->select('
                interesse.data_interesse,
                roupas.id as idRoupa,
                roupas.tipo,
                roupas.cor,
                roupas.imagem,
                roupas.status
            ')
            ->join('roupas', 'roupas.id = interesse.idRoupa')
            ->where('interesse.idCliente', $idCliente)
            ->orderBy('interesse.data_interesse', 'DESC')
            ->findAll();
    }
}