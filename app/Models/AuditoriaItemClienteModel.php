<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditoriaItemClienteModel extends Model
{
    protected $table = 'auditoria_item_cliente';
    protected $primaryKey = 'id_auditoria_item_cliente';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'id_auditoria_item',
        'id_cliente',
        'calificacion_ajustada',
        'comentario_proveedor_cliente',
        'comentario_cliente',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'id_auditoria_item' => 'required|integer',
        'id_cliente' => 'required|integer',
        'calificacion_ajustada' => 'permit_empty|in_list[cumple,parcial,no_cumple,no_aplica]',
    ];

    protected $validationMessages = [
        'id_auditoria_item' => [
            'required' => 'El ítem de auditoría es requerido',
            'integer' => 'El ítem de auditoría debe ser un número válido',
        ],
        'id_cliente' => [
            'required' => 'El cliente es requerido',
            'integer' => 'El cliente debe ser un número válido',
        ],
        'calificacion_ajustada' => [
            'required' => 'La calificación es requerida',
            'in_list' => 'La calificación debe ser: cumple, parcial, no_cumple o no_aplica',
        ],
    ];

    /**
     * Obtiene el override de un ítem específico para un cliente
     */
    public function getOverride(int $idAuditoriaItem, int $idCliente): ?array
    {
        return $this->where('id_auditoria_item', $idAuditoriaItem)
                    ->where('id_cliente', $idCliente)
                    ->first();
    }

    /**
     * Guarda o actualiza un override
     */
    public function saveOverride(int $idAuditoriaItem, int $idCliente, string $calificacion, ?string $comentario = null): bool
    {
        $existing = $this->getOverride($idAuditoriaItem, $idCliente);

        $data = [
            'id_auditoria_item' => $idAuditoriaItem,
            'id_cliente' => $idCliente,
            'calificacion_ajustada' => $calificacion,
            'comentario_cliente' => $comentario,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            return $this->update($existing['id_auditoria_item_cliente'], $data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            return (bool) $this->insert($data);
        }
    }

    /**
     * Obtiene todos los overrides de una auditoría para un cliente
     */
    public function getOverridesByCliente(int $idAuditoria, int $idCliente): array
    {
        return $this->select('auditoria_item_cliente.*')
                    ->join('auditoria_items', 'auditoria_items.id_auditoria_item = auditoria_item_cliente.id_auditoria_item')
                    ->where('auditoria_items.id_auditoria', $idAuditoria)
                    ->where('auditoria_item_cliente.id_cliente', $idCliente)
                    ->findAll();
    }
}
