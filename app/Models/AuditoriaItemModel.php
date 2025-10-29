<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditoriaItemModel extends Model
{
    protected $table            = 'auditoria_items';
    protected $primaryKey       = 'id_auditoria_item';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_auditoria',
        'id_item',
        'comentario_proveedor',
        'comentario_consultor',
        'calificacion_consultor',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getItemsWithDetails(int $idAuditoria): array
    {
        return $this->select('auditoria_items.*, items_banco.codigo_item, items_banco.titulo, items_banco.descripcion, items_banco.aplica_alto_riesgo')
                    ->join('items_banco', 'items_banco.id_item = auditoria_items.id_item')
                    ->where('auditoria_items.id_auditoria', $idAuditoria)
                    ->orderBy('items_banco.orden', 'ASC')
                    ->findAll();
    }
}
