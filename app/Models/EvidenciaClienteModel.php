<?php

namespace App\Models;

use CodeIgniter\Model;

class EvidenciaClienteModel extends Model
{
    protected $table = 'evidencias_cliente';
    protected $primaryKey = 'id_evidencia_cliente';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'id_auditoria_item_cliente',
        'nombre_archivo_original',
        'ruta_archivo',
        'tipo_mime',
        'tamanio_bytes',
        'hash_archivo',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';

    protected $validationRules = [
        'id_auditoria_item_cliente' => 'required|integer',
        'nombre_archivo_original' => 'required|max_length[255]',
        'ruta_archivo' => 'required|max_length[500]',
    ];

    protected $validationMessages = [
        'id_auditoria_item_cliente' => [
            'required' => 'El ítem de auditoría del cliente es requerido',
            'integer' => 'El ítem debe ser un número válido',
        ],
        'nombre_archivo_original' => [
            'required' => 'El nombre del archivo es requerido',
            'max_length' => 'El nombre del archivo no puede superar 255 caracteres',
        ],
        'ruta_archivo' => [
            'required' => 'La ruta del archivo es requerida',
            'max_length' => 'La ruta del archivo no puede superar 500 caracteres',
        ],
    ];

    /**
     * Obtiene todas las evidencias de un ítem-cliente
     */
    public function getEvidenciasByItemCliente(int $idAuditoriaItemCliente): array
    {
        return $this->where('id_auditoria_item_cliente', $idAuditoriaItemCliente)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene todas las evidencias de un cliente específico en una auditoría
     */
    public function getEvidenciasByClienteAuditoria(int $idAuditoria, int $idCliente): array
    {
        return $this->select('evidencias_cliente.*, auditoria_item_cliente.id_auditoria_item')
                    ->join('auditoria_item_cliente', 'auditoria_item_cliente.id_auditoria_item_cliente = evidencias_cliente.id_auditoria_item_cliente')
                    ->join('auditoria_items', 'auditoria_items.id_auditoria_item = auditoria_item_cliente.id_auditoria_item')
                    ->where('auditoria_items.id_auditoria', $idAuditoria)
                    ->where('auditoria_item_cliente.id_cliente', $idCliente)
                    ->orderBy('evidencias_cliente.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Elimina una evidencia y su archivo físico
     */
    public function deleteWithFile(int $idEvidenciaCliente): bool
    {
        $evidencia = $this->find($idEvidenciaCliente);

        if (!$evidencia) {
            return false;
        }

        // Eliminar archivo físico
        $filePath = WRITEPATH . $evidencia['ruta_archivo'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // Eliminar registro
        return $this->delete($idEvidenciaCliente);
    }

    /**
     * Cuenta evidencias por ítem-cliente
     */
    public function countByItemCliente(int $idAuditoriaItemCliente): int
    {
        return $this->where('id_auditoria_item_cliente', $idAuditoriaItemCliente)
                    ->countAllResults();
    }

    /**
     * Obtiene el tamaño total de archivos por ítem-cliente (en bytes)
     */
    public function getTotalSizeByItemCliente(int $idAuditoriaItemCliente): int
    {
        $result = $this->selectSum('tamanio_bytes')
                       ->where('id_auditoria_item_cliente', $idAuditoriaItemCliente)
                       ->first();

        return (int) ($result['tamanio_bytes'] ?? 0);
    }
}
