<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientesModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id_cliente';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // SoftDeletes opcional

    protected $protectFields    = true;
    protected $allowedFields    = [
        'razon_social',
        'nit',
        'estado',
        'logo_path',
    ];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'razon_social' => 'required|min_length[3]',
        'nit'          => 'required|is_unique[clientes.nit,id_cliente,{id_cliente}]',
    ];
}

