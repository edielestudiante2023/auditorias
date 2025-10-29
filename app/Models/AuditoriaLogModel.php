<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo AuditoriaLogModel
 *
 * Gestiona el registro de acciones (bitácora) en auditorías para trazabilidad completa.
 * Todas las acciones importantes son registradas automáticamente con usuario, timestamp y detalles.
 *
 * Tipos de acciones registradas:
 * - comentario_global_guardado
 * - comentario_cliente_guardado
 * - evidencia_global_subida
 * - evidencia_cliente_subida
 * - evidencia_global_eliminada
 * - evidencia_cliente_eliminada
 * - auditoria_finalizada
 * - auditoria_cerrada
 * - clientes_asignados
 *
 * @author Sistema de Auditorías
 */
class AuditoriaLogModel extends Model
{
    protected $table            = 'auditoria_log';
    protected $primaryKey       = 'id_log';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_auditoria',
        'id_users',
        'accion',
        'detalle',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Registra una acción en la bitácora de auditoría
     *
     * @param int $idAuditoria ID de la auditoría
     * @param string $accion Tipo de acción (max 60 caracteres)
     * @param string|array|null $detalle Detalles adicionales (se convierte a JSON si es array)
     * @param int|null $idUsers ID del usuario (NULL = acción del sistema)
     * @return int|false ID del log creado o false en caso de error
     */
    public function registrar(int $idAuditoria, string $accion, $detalle = null, ?int $idUsers = null)
    {
        // Si no se proporciona usuario, intentar obtener el actual
        if ($idUsers === null) {
            $idUsers = function_exists('userId') ? userId() : null;
        }

        // Convertir arrays a JSON
        if (is_array($detalle)) {
            $detalle = json_encode($detalle, JSON_UNESCAPED_UNICODE);
        }

        $data = [
            'id_auditoria' => $idAuditoria,
            'id_users'     => $idUsers,
            'accion'       => substr($accion, 0, 60), // Limitar a 60 caracteres
            'detalle'      => $detalle,
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        if ($this->insert($data)) {
            return $this->getInsertID();
        }

        return false;
    }

    /**
     * Registra guardado de comentario GLOBAL
     *
     * @param int $idAuditoria
     * @param int $idAuditoriaItem
     * @param string $itemTitulo
     * @param int|null $idUsers
     * @return int|false
     */
    public function registrarComentarioGlobal(int $idAuditoria, int $idAuditoriaItem, string $itemTitulo, ?int $idUsers = null)
    {
        return $this->registrar(
            $idAuditoria,
            'comentario_global_guardado',
            [
                'id_auditoria_item' => $idAuditoriaItem,
                'item_titulo' => $itemTitulo,
            ],
            $idUsers
        );
    }

    /**
     * Registra guardado de comentario POR CLIENTE
     *
     * @param int $idAuditoria
     * @param int $idAuditoriaItem
     * @param int $idCliente
     * @param string $itemTitulo
     * @param string $clienteNombre
     * @param int|null $idUsers
     * @return int|false
     */
    public function registrarComentarioCliente(
        int $idAuditoria,
        int $idAuditoriaItem,
        int $idCliente,
        string $itemTitulo,
        string $clienteNombre,
        ?int $idUsers = null
    ) {
        return $this->registrar(
            $idAuditoria,
            'comentario_cliente_guardado',
            [
                'id_auditoria_item' => $idAuditoriaItem,
                'id_cliente' => $idCliente,
                'item_titulo' => $itemTitulo,
                'cliente_nombre' => $clienteNombre,
            ],
            $idUsers
        );
    }

    /**
     * Registra subida de evidencia GLOBAL
     *
     * @param int $idAuditoria
     * @param int $idAuditoriaItem
     * @param string $nombreArchivo
     * @param int $tamanoBytes
     * @param int|null $idUsers
     * @return int|false
     */
    public function registrarEvidenciaGlobalSubida(
        int $idAuditoria,
        int $idAuditoriaItem,
        string $nombreArchivo,
        int $tamanoBytes,
        ?int $idUsers = null
    ) {
        return $this->registrar(
            $idAuditoria,
            'evidencia_global_subida',
            [
                'id_auditoria_item' => $idAuditoriaItem,
                'nombre_archivo' => $nombreArchivo,
                'tamano_bytes' => $tamanoBytes,
                'tamano_mb' => round($tamanoBytes / 1048576, 2),
            ],
            $idUsers
        );
    }

    /**
     * Registra subida de evidencia POR CLIENTE
     *
     * @param int $idAuditoria
     * @param int $idAuditoriaItem
     * @param int $idCliente
     * @param string $nombreArchivo
     * @param int $tamanoBytes
     * @param string $clienteNombre
     * @param int|null $idUsers
     * @return int|false
     */
    public function registrarEvidenciaClienteSubida(
        int $idAuditoria,
        int $idAuditoriaItem,
        int $idCliente,
        string $nombreArchivo,
        int $tamanoBytes,
        string $clienteNombre,
        ?int $idUsers = null
    ) {
        return $this->registrar(
            $idAuditoria,
            'evidencia_cliente_subida',
            [
                'id_auditoria_item' => $idAuditoriaItem,
                'id_cliente' => $idCliente,
                'nombre_archivo' => $nombreArchivo,
                'tamano_bytes' => $tamanoBytes,
                'tamano_mb' => round($tamanoBytes / 1048576, 2),
                'cliente_nombre' => $clienteNombre,
            ],
            $idUsers
        );
    }

    /**
     * Registra eliminación de evidencia GLOBAL
     *
     * @param int $idAuditoria
     * @param int $idAuditoriaItem
     * @param string $nombreArchivo
     * @param int|null $idUsers
     * @return int|false
     */
    public function registrarEvidenciaGlobalEliminada(
        int $idAuditoria,
        int $idAuditoriaItem,
        string $nombreArchivo,
        ?int $idUsers = null
    ) {
        return $this->registrar(
            $idAuditoria,
            'evidencia_global_eliminada',
            [
                'id_auditoria_item' => $idAuditoriaItem,
                'nombre_archivo' => $nombreArchivo,
            ],
            $idUsers
        );
    }

    /**
     * Registra eliminación de evidencia POR CLIENTE
     *
     * @param int $idAuditoria
     * @param int $idAuditoriaItem
     * @param int $idCliente
     * @param string $nombreArchivo
     * @param string $clienteNombre
     * @param int|null $idUsers
     * @return int|false
     */
    public function registrarEvidenciaClienteEliminada(
        int $idAuditoria,
        int $idAuditoriaItem,
        int $idCliente,
        string $nombreArchivo,
        string $clienteNombre,
        ?int $idUsers = null
    ) {
        return $this->registrar(
            $idAuditoria,
            'evidencia_cliente_eliminada',
            [
                'id_auditoria_item' => $idAuditoriaItem,
                'id_cliente' => $idCliente,
                'nombre_archivo' => $nombreArchivo,
                'cliente_nombre' => $clienteNombre,
            ],
            $idUsers
        );
    }

    /**
     * Registra finalización de auditoría (cambio a estado 'cerrada')
     *
     * @param int $idAuditoria
     * @param float $porcentajeGlobal
     * @param int $cantidadClientes
     * @param int|null $idUsers
     * @return int|false
     */
    public function registrarAuditoriaCerrada(
        int $idAuditoria,
        float $porcentajeGlobal,
        int $cantidadClientes,
        ?int $idUsers = null
    ) {
        return $this->registrar(
            $idAuditoria,
            'auditoria_cerrada',
            [
                'porcentaje_global' => $porcentajeGlobal,
                'cantidad_clientes' => $cantidadClientes,
            ],
            $idUsers
        );
    }

    /**
     * Registra asignación de clientes a una auditoría
     *
     * @param int $idAuditoria
     * @param array $idsClientes Array de IDs de clientes asignados
     * @param int|null $idUsers
     * @return int|false
     */
    public function registrarClientesAsignados(
        int $idAuditoria,
        array $idsClientes,
        ?int $idUsers = null
    ) {
        return $this->registrar(
            $idAuditoria,
            'clientes_asignados',
            [
                'cantidad_clientes' => count($idsClientes),
                'ids_clientes' => $idsClientes,
            ],
            $idUsers
        );
    }

    /**
     * Obtiene la bitácora de una auditoría con paginación
     *
     * @param int $idAuditoria
     * @param int $perPage Cantidad de registros por página (default: 20)
     * @return array Resultado paginado con datos y paginador
     */
    public function getBitacoraPaginada(int $idAuditoria, int $perPage = 20): array
    {
        $logs = $this->select('auditoria_log.*, users.nombre as usuario_nombre, users.email as usuario_email')
                     ->join('users', 'users.id_users = auditoria_log.id_users', 'left')
                     ->where('auditoria_log.id_auditoria', $idAuditoria)
                     ->orderBy('auditoria_log.created_at', 'DESC')
                     ->paginate($perPage);

        // Decodificar JSON en detalle
        foreach ($logs as &$log) {
            if (!empty($log['detalle'])) {
                $decoded = json_decode($log['detalle'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $log['detalle_json'] = $decoded;
                }
            }
        }

        return [
            'logs' => $logs,
            'pager' => $this->pager,
        ];
    }

    /**
     * Obtiene la bitácora completa de una auditoría (sin paginación)
     *
     * @param int $idAuditoria
     * @return array
     */
    public function getBitacora(int $idAuditoria): array
    {
        $logs = $this->select('auditoria_log.*, users.nombre as usuario_nombre, users.email as usuario_email')
                     ->join('users', 'users.id_users = auditoria_log.id_users', 'left')
                     ->where('auditoria_log.id_auditoria', $idAuditoria)
                     ->orderBy('auditoria_log.created_at', 'DESC')
                     ->findAll();

        // Decodificar JSON en detalle
        foreach ($logs as &$log) {
            if (!empty($log['detalle'])) {
                $decoded = json_decode($log['detalle'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $log['detalle_json'] = $decoded;
                }
            }
        }

        return $logs;
    }

    /**
     * Obtiene estadísticas de acciones por auditoría
     *
     * @param int $idAuditoria
     * @return array
     */
    public function getEstadisticas(int $idAuditoria): array
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT
                accion,
                COUNT(*) as total
            FROM auditoria_log
            WHERE id_auditoria = ?
            GROUP BY accion
            ORDER BY total DESC
        ", [$idAuditoria]);

        return $query->getResultArray();
    }

    /**
     * Cuenta el total de acciones en una auditoría
     *
     * @param int $idAuditoria
     * @return int
     */
    public function contarAcciones(int $idAuditoria): int
    {
        return $this->where('id_auditoria', $idAuditoria)->countAllResults();
    }
}
