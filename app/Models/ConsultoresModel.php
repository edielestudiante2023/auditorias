<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsultoresModel extends Model
{
    protected $table            = 'consultores';
    protected $primaryKey       = 'id_consultor';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'email',
        'telefono',
        'estado',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'nombre'  => 'required|min_length[3]',
        'email'   => 'required|valid_email|is_unique[consultores.email,id_consultor,{id_consultor}]',
        'telefono'=> 'permit_empty|max_length[50]',
        'estado'  => 'permit_empty|in_list[activo,inactivo]',
    ];
}

