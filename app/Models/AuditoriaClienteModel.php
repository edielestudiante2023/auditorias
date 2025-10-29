<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditoriaClienteModel extends Model
{
    protected $table            = 'auditoria_clientes';
    protected $primaryKey       = 'id_auditoria_cliente';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_auditoria',
        'id_cliente',
        'id_contrato',
        'id_servicio',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    public function getClientesByAuditoria(int $idAuditoria): array
    {
        return $this->select('auditoria_clientes.*, clientes.razon_social, clientes.nit')
                    ->join('clientes', 'clientes.id_cliente = auditoria_clientes.id_cliente')
                    ->where('auditoria_clientes.id_auditoria', $idAuditoria)
                    ->findAll();
    }
}
