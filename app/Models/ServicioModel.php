<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicioModel extends Model
{
    protected $table            = 'servicios';
    protected $primaryKey       = 'id_servicio';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nombre', 'activo'];

    public function getServiciosActivos(): array
    {
        return $this->where('activo', 1)->orderBy('nombre', 'ASC')->findAll();
    }
}
