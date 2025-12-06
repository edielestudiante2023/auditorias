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
     * @param int|null $anio Año a filtrar (null = todos)
     * @return array
     */
    public function getAuditoriasByConsultor(int $idConsultor, ?int $anio = null): array
    {
        $builder = $this->select('auditorias.*,
                              proveedores.razon_social as proveedor_nombre,
                              proveedores.nit as proveedor_nit,
                              proveedores.email_contacto as proveedor_email')
                    ->join('proveedores', 'proveedores.id_proveedor = auditorias.id_proveedor')
                    ->where('auditorias.id_consultor', $idConsultor);

        if ($anio) {
            $builder->where('YEAR(auditorias.created_at)', $anio);
        }

        $auditorias = $builder->orderBy('auditorias.created_at', 'DESC')
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
            ->select('ai.*, ib.codigo_item, ib.titulo, ib.descripcion, ib.alcance')
            ->join('items_banco ib', 'ib.id_item = ai.id_item')
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
        $totalItems = 0;

        foreach ($todosLosItems as $item) {
            $calificacion = $item['calificacion'];

            // Excluir ítems no aplicables del cálculo
            if ($calificacion === 'no_aplica') {
                continue;
            }

            $totalItems++;

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

    /**
     * Reabre una auditoría cerrada (solo SuperAdmin)
     * Cambia el estado de 'cerrada' a 'en_revision'
     *
     * @param int $idAuditoria
     * @param int $idUsuarioReapertura ID del usuario que reabre (debe ser superadmin)
     * @param string|null $motivo Motivo de la reapertura
     * @return array ['success' => bool, 'message' => string, 'auditoria' => array|null]
     */
    public function reabrirAuditoria(int $idAuditoria, int $idUsuarioReapertura, ?string $motivo = null): array
    {
        $auditoria = $this->find($idAuditoria);

        if (!$auditoria) {
            return [
                'success' => false,
                'message' => 'Auditoría no encontrada',
                'auditoria' => null
            ];
        }

        if ($auditoria['estado'] !== 'cerrada') {
            return [
                'success' => false,
                'message' => 'Solo se pueden reabrir auditorías cerradas. Estado actual: ' . $auditoria['estado'],
                'auditoria' => null
            ];
        }

        // Cambiar estado a en_revision
        $updated = $this->update($idAuditoria, [
            'estado' => 'en_revision',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (!$updated) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el estado de la auditoría',
                'auditoria' => null
            ];
        }

        // Registrar en log de reaperturas
        $db = \Config\Database::connect();
        $detalle = "Reapertura: cerrada → en_revision";
        if ($motivo) {
            $detalle .= " | Motivo: " . $motivo;
        }

        $db->table('auditoria_log')->insert([
            'id_auditoria' => $idAuditoria,
            'id_users' => $idUsuarioReapertura,
            'accion' => 'reapertura',
            'detalle' => $detalle,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => 'Auditoría reabierta exitosamente',
            'auditoria' => $this->find($idAuditoria)
        ];
    }

    /**
     * Adiciona clientes a una auditoría existente
     *
     * @param int $idAuditoria
     * @param array $clientesIds Array de IDs de clientes a agregar
     * @return array ['success' => bool, 'message' => string, 'clientes_agregados' => int]
     */
    public function adicionarClientes(int $idAuditoria, array $clientesIds): array
    {
        $auditoria = $this->find($idAuditoria);

        if (!$auditoria) {
            return [
                'success' => false,
                'message' => 'Auditoría no encontrada',
                'clientes_agregados' => 0
            ];
        }

        if ($auditoria['estado'] === 'anulada') {
            return [
                'success' => false,
                'message' => 'No se pueden agregar clientes a una auditoría anulada',
                'clientes_agregados' => 0
            ];
        }

        $db = \Config\Database::connect();

        // Obtener clientes ya asignados
        $clientesExistentes = $db->table('auditoria_clientes')
            ->where('id_auditoria', $idAuditoria)
            ->select('id_cliente')
            ->get()
            ->getResultArray();

        $idsExistentes = array_column($clientesExistentes, 'id_cliente');

        // Filtrar solo clientes nuevos
        $clientesNuevos = array_diff($clientesIds, $idsExistentes);

        if (empty($clientesNuevos)) {
            return [
                'success' => false,
                'message' => 'Todos los clientes ya están asignados a esta auditoría',
                'clientes_agregados' => 0
            ];
        }

        // Insertar nuevos clientes
        $insertados = 0;
        foreach ($clientesNuevos as $idCliente) {
            $inserted = $db->table('auditoria_clientes')->insert([
                'id_auditoria' => $idAuditoria,
                'id_cliente' => $idCliente,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if ($inserted) {
                $insertados++;
            }
        }

        return [
            'success' => $insertados > 0,
            'message' => $insertados > 0
                ? "Se agregaron {$insertados} cliente(s) exitosamente"
                : 'No se pudo agregar ningún cliente',
            'clientes_agregados' => $insertados
        ];
    }
}
