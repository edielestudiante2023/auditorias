<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditoriaModel extends Model
{
    protected $table            = 'auditorias';
    protected $primaryKey       = 'id_auditoria';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_proveedor',
        'id_consultor',
        'codigo_formato',
        'version_formato',
        'estado',
        'porcentaje_cumplimiento',
        'fecha_programada',
        'fecha_envio_proveedor',
        'fecha_envio_consultor',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [];
    protected $validationMessages = [];

    /**
     * Obtiene auditorias con informacion de proveedor y consultor
     */
    public function getAuditoriasWithRelations(): array
    {
        return $this->select('auditorias.*,
                              proveedores.razon_social as proveedor_nombre,
                              proveedores.nit as proveedor_nit,
                              consultores.nombre_completo as consultor_nombre,
                              users.email as consultor_email')
                    ->join('proveedores', 'proveedores.id_proveedor = auditorias.id_proveedor')
                    ->join('consultores', 'consultores.id_consultor = auditorias.id_consultor')
                    ->join('users', 'users.id_users = consultores.id_users', 'left')
                    ->orderBy('auditorias.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Obtiene auditorias creadas por un usuario especifico (consultor)
     */
    public function getAuditoriasByUsuario(int $idUsuario): array
    {
        return $this->select('auditorias.*,
                              proveedores.razon_social as proveedor_nombre,
                              proveedores.nit as proveedor_nit,
                              proveedores.email_contacto as proveedor_email')
                    ->join('proveedores', 'proveedores.id_proveedor = auditorias.id_proveedor')
                    ->join('consultores', 'consultores.id_consultor = auditorias.id_consultor')
                    ->where('consultores.id_users', $idUsuario)
                    ->orderBy('auditorias.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Obtiene auditorias de un consultor especifico con porcentaje de cumplimiento calculado
     * @param int $idConsultor ID del consultor
     * @return array
     */
    public function getAuditoriasByConsultor(int $idConsultor): array
    {
        $auditorias = $this->select('auditorias.*,
                              proveedores.razon_social as proveedor_nombre,
                              proveedores.nit as proveedor_nit,
                              proveedores.email_contacto as proveedor_email')
                    ->join('proveedores', 'proveedores.id_proveedor = auditorias.id_proveedor')
                    ->where('auditorias.id_consultor', $idConsultor)
                    ->orderBy('auditorias.created_at', 'DESC')
                    ->findAll();

        // Calcular porcentaje de cumplimiento para cada auditoría
        foreach ($auditorias as &$auditoria) {
            $auditoria['porcentaje_cumplimiento'] = $this->calcularPorcentajeCumplimiento($auditoria['id_auditoria']);
        }

        return $auditorias;
    }

    /**
     * Obtiene auditorias de un proveedor especifico
     */
    public function getAuditoriasByProveedor(int $idProveedor): array
    {
        return $this->select('auditorias.*,
                              consultores.nombre_completo as consultor_nombre,
                              users.email as consultor_email')
                    ->join('consultores', 'consultores.id_consultor = auditorias.id_consultor')
                    ->join('users', 'users.id_users = consultores.id_users', 'left')
                    ->where('auditorias.id_proveedor', $idProveedor)
                    ->orderBy('auditorias.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Obtiene una auditoria con toda su informacion relacionada
     */
    public function getAuditoriaCompleta(int $idAuditoria): ?array
    {
        $auditoria = $this->select('auditorias.*,
                                    proveedores.razon_social as proveedor_nombre,
                                    proveedores.nit as proveedor_nit,
                                    proveedores.email_contacto as proveedor_email,
                                    proveedores.logo_path as proveedor_logo,
                                    consultores.nombre_completo as consultor_nombre,
                                    consultores.firma_path as consultor_firma,
                                    users.email as consultor_email')
                          ->join('proveedores', 'proveedores.id_proveedor = auditorias.id_proveedor')
                          ->join('consultores', 'consultores.id_consultor = auditorias.id_consultor')
                          ->join('users', 'users.id_users = consultores.id_users', 'left')
                          ->where('auditorias.id_auditoria', $idAuditoria)
                          ->first();

        if (!$auditoria) {
            return null;
        }

        // Obtener clientes asignados
        $db = \Config\Database::connect();
        $auditoria['clientes'] = $db->table('auditoria_clientes ac')
            ->select('c.*, ac.porcentaje_cumplimiento, ac.id_contrato')
            ->join('clientes c', 'c.id_cliente = ac.id_cliente')
            ->where('ac.id_auditoria', $idAuditoria)
            ->get()
            ->getResultArray();

        // Obtener items de la auditoria
        $auditoria['items'] = $db->table('auditoria_items ai')
            ->select('ai.*, ib.codigo, ib.nombre, ib.descripcion, ib.es_por_cliente, ib.evidencia_requerida')
            ->join('items_banco ib', 'ib.id_item_banco = ai.id_item_banco')
            ->where('ai.id_auditoria', $idAuditoria)
            ->orderBy('ib.orden', 'ASC')
            ->get()
            ->getResultArray();

        return $auditoria;
    }

    /**
     * Calcula el porcentaje de cumplimiento de una auditoría
     * Considera TODOS los ítems calificados (globales y por cliente)
     *
     * Lógica de puntuación:
     * - cumple = 1.0 (100%)
     * - parcial = 0.5 (50%)
     * - no_cumple = 0.0 (0%)
     * - no_aplica = 1.0 (100%) - Se considera como cumplido
     *
     * @param int $idAuditoria
     * @return float Porcentaje de cumplimiento (0-100)
     */
    public function calcularPorcentajeCumplimiento(int $idAuditoria): float
    {
        $db = \Config\Database::connect();

        // Obtener ítems globales calificados
        $queryGlobales = $db->query("
            SELECT ai.calificacion_consultor as calificacion
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance IN ('global', 'mixto')
              AND ai.calificacion_consultor IS NOT NULL
              AND ai.calificacion_consultor != 'sin_revision'
        ", [$idAuditoria]);

        $itemsGlobales = $queryGlobales->getResultArray();

        // Obtener ítems por cliente calificados
        $queryPorCliente = $db->query("
            SELECT aic.calificacion_ajustada as calificacion
            FROM auditoria_item_cliente aic
            JOIN auditoria_items ai ON ai.id_auditoria_item = aic.id_auditoria_item
            JOIN items_banco ib ON ib.id_item = ai.id_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance = 'por_cliente'
              AND aic.calificacion_ajustada IS NOT NULL
              AND aic.calificacion_ajustada != 'sin_revision'
        ", [$idAuditoria]);

        $itemsPorCliente = $queryPorCliente->getResultArray();

        // Combinar todos los ítems
        $todosLosItems = array_merge($itemsGlobales, $itemsPorCliente);

        if (empty($todosLosItems)) {
            return 0.0;
        }

        $totalPuntos = 0;
        $totalItems = count($todosLosItems);

        foreach ($todosLosItems as $item) {
            $calificacion = $item['calificacion'];

            switch ($calificacion) {
                case 'cumple':
                    $totalPuntos += 1.0;
                    break;
                case 'parcial':
                    $totalPuntos += 0.5;
                    break;
                case 'no_cumple':
                    $totalPuntos += 0.0;
                    break;
                case 'no_aplica':
                    $totalPuntos += 1.0; // No aplica cuenta como 100% (ítem cumplido)
                    break;
            }
        }

        if ($totalItems === 0) {
            return 0.0;
        }

        return round(($totalPuntos / $totalItems) * 100, 2);
    }

    /**
     * Actualiza el estado de una auditoria
     */
    public function cambiarEstado(int $idAuditoria, string $nuevoEstado): bool
    {
        $estadosValidos = ['borrador', 'en_proveedor', 'en_revision', 'cerrada', 'anulada'];

        if (!in_array($nuevoEstado, $estadosValidos)) {
            return false;
        }

        return $this->update($idAuditoria, [
            'estado' => $nuevoEstado,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Verifica si un consultor puede editar una auditoria
     */
    public function puedeEditar(int $idAuditoria, int $idConsultor): bool
    {
        $auditoria = $this->find($idAuditoria);

        if (!$auditoria) {
            return false;
        }

        // Solo el consultor asignado puede editar si esta en borrador
        if ($auditoria['estado'] === 'borrador' && $auditoria['id_consultor'] == $idConsultor) {
            return true;
        }

        return false;
    }

    /**
     * Obtiene estadisticas de auditorias por estado
     */
    public function getEstadisticasPorEstado(?int $idConsultor = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('auditorias');

        if ($idConsultor) {
            $builder->where('id_consultor', $idConsultor);
        }

        $builder->select('estado, COUNT(*) as total')
                ->groupBy('estado');

        return $builder->get()->getResultArray();
    }
}
