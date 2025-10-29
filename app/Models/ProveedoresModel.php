<?php

namespace App\Models;

use CodeIgniter\Model;

class ProveedoresModel extends Model
{
    protected $table            = 'proveedores';
    protected $primaryKey       = 'id_proveedor';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $protectFields    = true;
    protected $allowedFields    = [
        'razon_social',
        'nit',
        'email_contacto',
        'telefono_contacto',
        'observaciones',
        'responsable_nombre',
        'responsable_email',
        'responsable_telefono',
        'responsable_cargo',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'razon_social' => 'required|min_length[3]',
        'nit'          => 'required',
    ];

    protected $validationMessages = [
        'razon_social' => [
            'required' => 'La razón social es obligatoria',
            'min_length' => 'La razón social debe tener al menos 3 caracteres',
        ],
        'nit' => [
            'required' => 'El NIT es obligatorio',
        ],
    ];
}

