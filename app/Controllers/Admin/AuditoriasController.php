<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditoriaModel;

class AuditoriasController extends BaseController
{
    protected $auditoriaModel;

    public function __construct()
    {
        $this->auditoriaModel = new AuditoriaModel();
        helper(['auth', 'format']);
    }

    /**
     * Vista 1: Auditorías completadas por proveedores (esperando revisión del consultor)
     * Equivalente a /proveedor/auditorias/completadas pero sin filtro de usuario
     */
    public function completadasProveedores()
    {
        $db = \Config\Database::connect();
        $anio = $this->request->getGet('anio') ?? date('Y');
        $filtroAnio = ($anio !== 'todos') ? "AND YEAR(a.created_at) = " . (int)$anio : "";

        // Todas las auditorías cerradas, sin filtro de usuario
        $auditorias = $db->query("
            SELECT DISTINCT a.*,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre,
                   u.nombre as usuario_responsable_nombre,
                   u.email as usuario_responsable_email
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            LEFT JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            LEFT JOIN users u ON u.id_users = cpc.id_usuario_responsable
            WHERE a.estado = 'cerrada'
              {$filtroAnio}
            ORDER BY a.updated_at DESC
        ")->getResultArray();

        return view('admin/auditorias/completadas_proveedores', [
            'title' => 'Auditorías Completadas por Proveedores',
            'auditorias' => $auditorias,
            'anio' => $anio,
        ]);
    }

    /**
     * Vista 2: Auditorías pendientes de diligenciamiento por proveedores
     * Equivalente a /proveedor/auditorias pero sin filtro de usuario
     */
    public function pendientesProveedores()
    {
        $db = \Config\Database::connect();
        $anio = $this->request->getGet('anio') ?? date('Y');
        $filtroAnio = ($anio !== 'todos') ? "AND YEAR(a.created_at) = " . (int)$anio : "";

        // Todas las auditorías que no están cerradas
        $auditorias = $db->query("
            SELECT DISTINCT a.*,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre,
                   u.nombre as usuario_responsable_nombre,
                   u.email as usuario_responsable_email
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            LEFT JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            LEFT JOIN users u ON u.id_users = cpc.id_usuario_responsable
            WHERE a.estado != 'cerrada'
              {$filtroAnio}
            ORDER BY a.created_at DESC
        ")->getResultArray();

        return view('admin/auditorias/pendientes_proveedores', [
            'title' => 'Auditorías Pendientes - Proveedores',
            'auditorias' => $auditorias,
            'anio' => $anio,
        ]);
    }

    /**
     * Vista 3: Auditorías en revisión por consultores
     * Equivalente a /consultor/auditorias pero sin filtro de usuario
     */
    public function revisionConsultores()
    {
        $db = \Config\Database::connect();
        $anio = $this->request->getGet('anio') ?? date('Y');
        $filtroAnio = ($anio !== 'todos') ? "AND YEAR(a.created_at) = " . (int)$anio : "";

        // Todas las auditorías de todos los consultores, sin filtro
        $auditorias = $db->query("
            SELECT a.*,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre,
                   u.nombre as usuario_responsable_nombre,
                   u.email as usuario_responsable_email,
                   COUNT(DISTINCT ai.id_auditoria_item) as total_items,
                   COUNT(DISTINCT CASE WHEN ai.calificacion_consultor != 'sin_revision' THEN ai.id_auditoria_item END) as items_calificados
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            LEFT JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            LEFT JOIN users u ON u.id_users = cpc.id_usuario_responsable
            LEFT JOIN auditoria_items ai ON ai.id_auditoria = a.id_auditoria
            WHERE a.estado IN ('borrador', 'asignada', 'en_progreso', 'en_revision_consultor', 'cerrada')
              {$filtroAnio}
            GROUP BY a.id_auditoria, p.razon_social, p.nit, c.nombre_completo, u.nombre, u.email
            ORDER BY a.created_at DESC
        ")->getResultArray();

        return view('admin/auditorias/revision_consultores', [
            'title' => 'Auditorías - Revisión Consultores',
            'auditorias' => $auditorias,
            'anio' => $anio,
        ]);
    }

    /**
     * Vista del historial completo de reaperturas
     */
    public function historialReaperturas()
    {
        if (!isSuperAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        $db = \Config\Database::connect();

        // Obtener historial completo de reaperturas con información detallada
        $historial = $db->query("
            SELECT al.*,
                   u.nombre as usuario_nombre,
                   u.email as usuario_email,
                   a.id_auditoria,
                   a.codigo_formato,
                   a.fecha_programada,
                   a.estado as estado_actual,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre
            FROM auditoria_log al
            JOIN users u ON u.id_users = al.id_users
            JOIN auditorias a ON a.id_auditoria = al.id_auditoria
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            WHERE al.accion = 'reapertura'
            ORDER BY al.created_at DESC
        ")->getResultArray();

        return view('admin/auditorias/historial_reaperturas', [
            'title' => 'Auditoría de Cambios - Historial de Reaperturas',
            'historial' => $historial,
        ]);
    }

    /**
     * Vista de gestión de auditorías cerradas (para reabrir)
     */
    public function cerradas()
    {
        if (!isSuperAdmin()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        $db = \Config\Database::connect();
        $anio = $this->request->getGet('anio') ?? date('Y');
        $filtroAnio = ($anio !== 'todos') ? "AND YEAR(a.created_at) = " . (int)$anio : "";

        // Obtener todas las auditorías cerradas con información completa
        $auditorias = $db->query("
            SELECT a.*,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre,
                   u.email as consultor_email,
                   COUNT(DISTINCT ac.id_cliente) as total_clientes
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            LEFT JOIN users u ON u.id_users = c.id_users
            LEFT JOIN auditoria_clientes ac ON ac.id_auditoria = a.id_auditoria
            WHERE a.estado = 'cerrada'
              {$filtroAnio}
            GROUP BY a.id_auditoria
            ORDER BY a.updated_at DESC
        ")->getResultArray();

        // Obtener historial de reaperturas
        $historial = $db->query("
            SELECT al.*,
                   u.nombre as usuario_nombre,
                   u.email as usuario_email,
                   a.codigo_formato
            FROM auditoria_log al
            JOIN users u ON u.id_users = al.id_users
            JOIN auditorias a ON a.id_auditoria = al.id_auditoria
            WHERE al.accion = 'reapertura'
            ORDER BY al.created_at DESC
            LIMIT 50
        ")->getResultArray();

        return view('admin/auditorias/cerradas', [
            'title' => 'Auditorías Cerradas - Gestión',
            'auditorias' => $auditorias,
            'historial' => $historial,
            'anio' => $anio,
        ]);
    }

    /**
     * Reabre una auditoría cerrada
     */
    public function reabrir($idAuditoria = null)
    {
        if (!isSuperAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso denegado. Solo SuperAdmin puede reabrir auditorías.'
            ]);
        }

        if (!$idAuditoria) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de auditoría no proporcionado'
            ]);
        }

        $motivo = $this->request->getPost('motivo');

        $resultado = $this->auditoriaModel->reabrirAuditoria(
            $idAuditoria,
            userId(),
            $motivo
        );

        return $this->response->setJSON($resultado);
    }

    /**
     * Muestra vista para adicionar clientes a auditoría existente
     */
    public function adicionarClientes($idAuditoria = null)
    {
        if (!isSuperAdmin() && !isConsultor()) {
            return redirect()->to('/')->with('error', 'Acceso denegado');
        }

        if (!$idAuditoria) {
            return redirect()->back()->with('error', 'ID de auditoría no proporcionado');
        }

        $auditoria = $this->auditoriaModel->getAuditoriaCompleta($idAuditoria);

        if (!$auditoria) {
            return redirect()->back()->with('error', 'Auditoría no encontrada');
        }

        // Verificar permisos: consultor solo puede editar sus auditorías
        if (isConsultor() && $auditoria['id_consultor'] != session()->get('id_consultor')) {
            return redirect()->back()->with('error', 'No tienes permiso para editar esta auditoría');
        }

        $db = \Config\Database::connect();

        // Obtener clientes del proveedor que NO están en esta auditoría
        $clientesDisponibles = $db->query("
            SELECT DISTINCT c.*
            FROM clientes c
            JOIN contratos_proveedor_cliente cpc ON cpc.id_cliente = c.id_cliente
            WHERE cpc.id_proveedor = ?
            AND c.id_cliente NOT IN (
                SELECT id_cliente
                FROM auditoria_clientes
                WHERE id_auditoria = ?
            )
            ORDER BY c.razon_social ASC
        ", [$auditoria['id_proveedor'], $idAuditoria])->getResultArray();

        return view('admin/auditorias/adicionar_clientes', [
            'title' => 'Adicionar Clientes a Auditoría',
            'auditoria' => $auditoria,
            'clientesDisponibles' => $clientesDisponibles,
        ]);
    }

    /**
     * Procesa la adición de clientes a auditoría
     */
    public function procesarAdicionClientes($idAuditoria = null)
    {
        if (!isSuperAdmin() && !isConsultor()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso denegado'
            ]);
        }

        if (!$idAuditoria) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de auditoría no proporcionado'
            ]);
        }

        $clientesIds = $this->request->getPost('clientes');

        if (empty($clientesIds) || !is_array($clientesIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debes seleccionar al menos un cliente'
            ]);
        }

        $resultado = $this->auditoriaModel->adicionarClientes($idAuditoria, $clientesIds);

        // Guardar los IDs de los clientes recién agregados en sesión
        // para que el reenvío de credenciales los use
        if ($resultado['success']) {
            session()->set('ultimos_clientes_agregados_' . $idAuditoria, $clientesIds);
        }

        return $this->response->setJSON($resultado);
    }

    /**
     * Obtiene la lista de clientes asignados a una auditoría
     */
    public function getClientes($idAuditoria = null)
    {
        if (!isSuperAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso denegado'
            ]);
        }

        if (!$idAuditoria) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de auditoría no proporcionado'
            ]);
        }

        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Auditoría no encontrada'
            ]);
        }

        $db = \Config\Database::connect();

        // Obtener clientes asignados
        $clientes = $db->query("
            SELECT cl.razon_social, cl.nit
            FROM auditoria_clientes ac
            JOIN clientes cl ON cl.id_cliente = ac.id_cliente
            WHERE ac.id_auditoria = ?
            ORDER BY cl.razon_social ASC
        ", [$idAuditoria])->getResultArray();

        // Contar total de clientes del proveedor
        $totalProveedor = $db->query("
            SELECT COUNT(DISTINCT id_cliente) as total
            FROM contratos_proveedor_cliente
            WHERE id_proveedor = ?
            AND estado = 'activo'
        ", [$auditoria['id_proveedor']])->getRow()->total ?? 0;

        return $this->response->setJSON([
            'success' => true,
            'clientes' => $clientes,
            'total_proveedor' => $totalProveedor
        ]);
    }

    /**
     * Elimina una auditoría (solo SuperAdmin)
     */
    public function eliminar($idAuditoria = null)
    {
        if (!isSuperAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso denegado. Solo SuperAdmin puede eliminar auditorías.'
            ]);
        }

        if (!$idAuditoria) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de auditoría no proporcionado'
            ]);
        }

        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Auditoría no encontrada'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Deshabilitar temporalmente las verificaciones de claves foráneas
            $db->query("SET FOREIGN_KEY_CHECKS = 0");

            // Eliminar registros relacionados en orden

            // 1. Obtener IDs de items de esta auditoría
            $itemsIds = $db->query("SELECT id_auditoria_item FROM auditoria_items WHERE id_auditoria = ?", [$idAuditoria])->getResultArray();

            if (!empty($itemsIds)) {
                $itemsIdsArray = array_column($itemsIds, 'id_auditoria_item');

                // 1a. Obtener IDs de auditoria_item_cliente para eliminar evidencias
                $itemClienteIds = $db->query("SELECT id_auditoria_item_cliente FROM auditoria_item_cliente WHERE id_auditoria_item IN (" . implode(',', $itemsIdsArray) . ")")->getResultArray();

                if (!empty($itemClienteIds)) {
                    $itemClienteIdsArray = array_column($itemClienteIds, 'id_auditoria_item_cliente');

                    // Eliminar evidencias por cliente
                    $db->table('evidencias_cliente')
                       ->whereIn('id_auditoria_item_cliente', $itemClienteIdsArray)
                       ->delete();
                }

                // 1b. Eliminar evidencias globales
                $db->table('evidencias')
                   ->whereIn('id_auditoria_item', $itemsIdsArray)
                   ->delete();

                // 1c. Eliminar respuestas de items por cliente
                $db->table('auditoria_item_cliente')
                   ->whereIn('id_auditoria_item', $itemsIdsArray)
                   ->delete();
            }

            // 2. Eliminar items de auditoría
            $db->table('auditoria_items')
               ->where('id_auditoria', $idAuditoria)
               ->delete();

            // 3. Eliminar clientes de auditoría
            $db->table('auditoria_clientes')
               ->where('id_auditoria', $idAuditoria)
               ->delete();

            // 4. Eliminar log de auditoría
            $db->table('auditoria_log')
               ->where('id_auditoria', $idAuditoria)
               ->delete();

            // 5. Finalmente, eliminar la auditoría
            $db->table('auditorias')
               ->where('id_auditoria', $idAuditoria)
               ->delete();

            // Reactivar las verificaciones de claves foráneas
            $db->query("SET FOREIGN_KEY_CHECKS = 1");

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al eliminar la auditoría. Transacción fallida.'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Auditoría eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            // Asegurarse de reactivar las claves foráneas incluso si hay error
            $db->query("SET FOREIGN_KEY_CHECKS = 1");
            log_message('error', 'Error al eliminar auditoría: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar la auditoría: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reporte de progreso de auditorías en tiempo real
     */
    public function reporteProgreso()
    {
        $db = \Config\Database::connect();
        $anio = $this->request->getGet('anio') ?? date('Y');
        $filtroAnio = ($anio !== 'todos') ? "WHERE YEAR(a.created_at) = " . (int)$anio : "";

        // Obtener todas las auditorías con sus datos básicos
        $auditorias = $db->query("
            SELECT a.id_auditoria, a.codigo_formato, a.estado, a.fecha_programada,
                   a.fecha_envio_proveedor, a.fecha_envio_consultor, a.created_at, a.updated_at,
                   p.razon_social as proveedor,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            {$filtroAnio}
            ORDER BY
                CASE a.estado
                    WHEN 'en_proveedor' THEN 1
                    WHEN 'en_revision_consultor' THEN 2
                    WHEN 'cerrada' THEN 3
                    ELSE 4
                END,
                a.fecha_programada ASC
        ")->getResultArray();

        // Calcular progreso para cada auditoría
        $reporteAuditorias = [];
        $resumen = [
            'total' => 0,
            'completas_sin_enviar' => 0,
            'en_progreso_alto' => 0,    // 70-99%
            'en_progreso_medio' => 0,   // 30-69%
            'en_progreso_bajo' => 0,    // 1-29%
            'sin_iniciar' => 0,         // 0%
            'enviadas_consultor' => 0,
            'cerradas' => 0,
            'vencidas' => 0,
        ];

        foreach ($auditorias as $aud) {
            $idAuditoria = $aud['id_auditoria'];
            $resumen['total']++;

            // Calcular progreso igual que el controlador del proveedor

            // Globales total
            $stmt = $db->query("SELECT COUNT(*) as total FROM auditoria_items ai
                                JOIN items_banco ib ON ib.id_item = ai.id_item
                                WHERE ai.id_auditoria = ? AND ib.alcance IN ('global', 'mixto')", [$idAuditoria]);
            $globalesTotal = $stmt->getRow()->total ?? 0;

            // Globales completados
            $stmt = $db->query("SELECT COUNT(DISTINCT ai.id_auditoria_item) as completados
                                FROM auditoria_items ai
                                JOIN items_banco ib ON ib.id_item = ai.id_item
                                LEFT JOIN evidencias e ON e.id_auditoria_item = ai.id_auditoria_item
                                WHERE ai.id_auditoria = ?
                                  AND ib.alcance IN ('global', 'mixto')
                                  AND ((ai.comentario_proveedor IS NOT NULL AND ai.comentario_proveedor != '')
                                       OR e.id_evidencia IS NOT NULL)", [$idAuditoria]);
            $globalesCompletos = $stmt->getRow()->completados ?? 0;

            // Clientes
            $stmt = $db->query("SELECT COUNT(*) as total_clientes FROM auditoria_clientes WHERE id_auditoria = ?", [$idAuditoria]);
            $totalClientes = $stmt->getRow()->total_clientes ?? 0;

            // Items por cliente
            $stmt = $db->query("SELECT COUNT(*) as total_items FROM auditoria_items ai
                                JOIN items_banco ib ON ib.id_item = ai.id_item
                                WHERE ai.id_auditoria = ? AND ib.alcance = 'por_cliente'", [$idAuditoria]);
            $itemsPorCliente = $stmt->getRow()->total_items ?? 0;
            $porClienteTotal = $itemsPorCliente * $totalClientes;

            // Por cliente completados
            $stmt = $db->query("SELECT COUNT(DISTINCT aic.id_auditoria_item_cliente) as completados
                                FROM auditoria_item_cliente aic
                                JOIN auditoria_items ai ON ai.id_auditoria_item = aic.id_auditoria_item
                                JOIN items_banco ib ON ib.id_item = ai.id_item
                                LEFT JOIN evidencias_cliente ec ON ec.id_auditoria_item_cliente = aic.id_auditoria_item_cliente
                                WHERE ai.id_auditoria = ?
                                  AND ib.alcance = 'por_cliente'
                                  AND ((aic.comentario_proveedor_cliente IS NOT NULL AND aic.comentario_proveedor_cliente != '')
                                       OR ec.id_evidencia_cliente IS NOT NULL)", [$idAuditoria]);
            $porClienteCompletos = $stmt->getRow()->completados ?? 0;

            // Totales
            $total = $globalesTotal + $porClienteTotal;
            $completados = $globalesCompletos + $porClienteCompletos;
            $porcentaje = $total > 0 ? round(($completados / $total) * 100, 2) : 0;

            // Determinar estado descriptivo
            $estadoDescriptivo = '';
            $badgeClass = '';
            $categoria = '';
            $prioridad = 0;

            // Verificar vencimiento
            $vencida = strtotime($aud['fecha_programada']) < strtotime('today');

            if ($aud['estado'] === 'cerrada') {
                $estadoDescriptivo = 'Cerrada';
                $badgeClass = 'success';
                $categoria = 'cerradas';
                $resumen['cerradas']++;
                $prioridad = 5;
            } elseif ($aud['estado'] === 'en_revision_consultor') {
                $estadoDescriptivo = 'En revisión consultor';
                $badgeClass = 'info';
                $categoria = 'en_revision';
                $resumen['enviadas_consultor']++;
                $prioridad = 4;
            } elseif ($porcentaje >= 100 && $aud['estado'] === 'en_proveedor') {
                $estadoDescriptivo = '100% - PENDIENTE ENVIAR';
                $badgeClass = 'danger';
                $categoria = 'completas_sin_enviar';
                $resumen['completas_sin_enviar']++;
                $prioridad = 1;
            } elseif ($porcentaje >= 70) {
                $estadoDescriptivo = 'En progreso alto';
                $badgeClass = 'primary';
                $categoria = 'en_progreso';
                $resumen['en_progreso_alto']++;
                $prioridad = 2;
            } elseif ($porcentaje >= 30) {
                $estadoDescriptivo = 'En progreso';
                $badgeClass = 'warning';
                $categoria = 'en_progreso';
                $resumen['en_progreso_medio']++;
                $prioridad = 3;
            } elseif ($porcentaje > 0) {
                $estadoDescriptivo = 'Recién iniciada';
                $badgeClass = 'secondary';
                $categoria = 'en_progreso';
                $resumen['en_progreso_bajo']++;
                $prioridad = 3;
            } else {
                $estadoDescriptivo = 'Sin iniciar';
                $badgeClass = 'dark';
                $categoria = 'sin_iniciar';
                $resumen['sin_iniciar']++;
                $prioridad = 3;
            }

            if ($vencida && $aud['estado'] === 'en_proveedor') {
                $estadoDescriptivo .= ' (VENCIDA)';
                $badgeClass = 'danger';
                $resumen['vencidas']++;
            }

            $reporteAuditorias[] = [
                'id_auditoria' => $idAuditoria,
                'codigo_formato' => $aud['codigo_formato'],
                'proveedor' => $aud['proveedor'],
                'proveedor_nit' => $aud['proveedor_nit'],
                'consultor' => $aud['consultor'],
                'estado' => $aud['estado'],
                'estado_descriptivo' => $estadoDescriptivo,
                'badge_class' => $badgeClass,
                'categoria' => $categoria,
                'fecha_programada' => $aud['fecha_programada'],
                'fecha_envio_proveedor' => $aud['fecha_envio_proveedor'],
                'fecha_envio_consultor' => $aud['fecha_envio_consultor'],
                'fecha_cierre' => $aud['estado'] === 'cerrada' ? $aud['updated_at'] : null,
                'vencida' => $vencida,
                'progreso' => $porcentaje,
                'globales_completos' => $globalesCompletos,
                'globales_total' => $globalesTotal,
                'por_cliente_completos' => $porClienteCompletos,
                'por_cliente_total' => $porClienteTotal,
                'total_clientes' => $totalClientes,
                'items_completados' => $completados,
                'items_total' => $total,
                'prioridad' => $prioridad,
            ];
        }

        // Ordenar por prioridad (problemas primero)
        usort($reporteAuditorias, function($a, $b) {
            if ($a['prioridad'] !== $b['prioridad']) {
                return $a['prioridad'] - $b['prioridad'];
            }
            return $b['progreso'] - $a['progreso'];
        });

        return view('admin/auditorias/reporte_progreso', [
            'title' => 'Reporte de Progreso de Auditorías',
            'auditorias' => $reporteAuditorias,
            'resumen' => $resumen,
            'anio' => $anio,
        ]);
    }

    /**
     * Reenvía credenciales al proveedor después de adicionar clientes
     */
    public function reenviarCredenciales($idAuditoria = null)
    {
        if (!isSuperAdmin() && !isConsultor()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso denegado'
            ]);
        }

        if (!$idAuditoria) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de auditoría no proporcionado'
            ]);
        }

        $auditoria = $this->auditoriaModel->getAuditoriaCompleta($idAuditoria);

        if (!$auditoria) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Auditoría no encontrada'
            ]);
        }

        // Obtener el primer cliente para buscar el contrato y usuario responsable
        if (empty($auditoria['clientes'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay clientes asignados a esta auditoría'
            ]);
        }

        $db = \Config\Database::connect();

        // Obtener el contrato para acceder al usuario responsable
        $contrato = $db->table('contratos_proveedor_cliente')
            ->select('contratos_proveedor_cliente.*,
                      users.email as usuario_responsable_email,
                      users.nombre as usuario_responsable_nombre,
                      users.id_users as usuario_responsable_id')
            ->join('users', 'users.id_users = contratos_proveedor_cliente.id_usuario_responsable')
            ->where('contratos_proveedor_cliente.id_proveedor', $auditoria['id_proveedor'])
            ->where('contratos_proveedor_cliente.id_cliente', $auditoria['clientes'][0]['id_cliente'])
            ->where('contratos_proveedor_cliente.estado', 'activo')
            ->get()
            ->getRowArray();

        if (!$contrato) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se encontró un contrato activo con usuario responsable'
            ]);
        }

        try {
            // Generar nueva contraseña
            helper('auth');
            $claveTemporal = generateSecurePassword(12);
            $hash = password_hash($claveTemporal, PASSWORD_DEFAULT);

            // Actualizar contraseña del usuario
            $db->table('users')
                ->where('id_users', $contrato['usuario_responsable_id'])
                ->update(['password_hash' => $hash]);

            // Obtener los IDs de clientes recién agregados desde la sesión
            $clientesIdsAgregados = session()->get('ultimos_clientes_agregados_' . $idAuditoria);

            $clientesNuevos = [];

            if (!empty($clientesIdsAgregados) && is_array($clientesIdsAgregados)) {
                // Obtener datos completos solo de los clientes recién agregados
                $clientesNuevos = $db->table('clientes')
                    ->whereIn('id_cliente', $clientesIdsAgregados)
                    ->orderBy('razon_social', 'ASC')
                    ->get()
                    ->getResultArray();

                // Limpiar la sesión después de usarla
                session()->remove('ultimos_clientes_agregados_' . $idAuditoria);
            } else {
                // Si no hay datos en sesión (reenvío manual), usar todos los clientes
                $clientesNuevos = $auditoria['clientes'];
            }

            // Obtener solo items POR CLIENTE (individuales)
            $itemsPorCliente = $db->table('auditoria_items ai')
                ->select('ai.*, ib.alcance, ib.titulo, ib.descripcion, ib.orden')
                ->join('items_banco ib', 'ib.id_item = ai.id_item')
                ->where('ai.id_auditoria', $idAuditoria)
                ->where('ib.alcance', 'por_cliente')
                ->orderBy('ib.orden', 'ASC')
                ->get()
                ->getResultArray();

            // Obtener datos del consultor asignado
            $consultorModel = new \App\Models\ConsultorModel();
            $consultorData = $consultorModel->find($auditoria['id_consultor']);

            // Enviar email específico para adición de clientes
            $emailService = new \App\Services\EmailService();
            $urlLogin = site_url('login');
            $urlAuditoria = site_url('proveedor/auditoria/' . $idAuditoria);

            $resultado = $emailService->sendAdicionClientesProveedor(
                $contrato['usuario_responsable_email'],
                $contrato['usuario_responsable_email'],
                $claveTemporal,
                $urlLogin,
                $urlAuditoria,
                $contrato['usuario_responsable_nombre'],
                $clientesNuevos, // Solo clientes nuevos (últimos 10 min)
                $itemsPorCliente, // Solo items individuales
                $consultorData // Datos del consultor
            );

            if ($resultado['ok']) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Credenciales reenviadas exitosamente a ' . $contrato['usuario_responsable_nombre']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Error al enviar el email: ' . $resultado['error']
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al reenviar credenciales: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al procesar el reenvío: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reporte centrado en clientes
     * Muestra todos los clientes y el estado de sus auditorías
     */
    public function reporteClientes()
    {
        $db = \Config\Database::connect();
        $anio = $this->request->getGet('anio') ?? date('Y');
        $filtroAnio = ($anio !== 'todos') ? "AND YEAR(a.created_at) = " . (int)$anio : "";

        // Obtener todos los clientes con sus auditorías del año seleccionado
        $clientes = $db->query("
            SELECT
                cl.id_cliente,
                cl.razon_social as cliente,
                cl.nit as cliente_nit,
                p.razon_social as proveedor,
                p.nit as proveedor_nit,
                a.id_auditoria,
                a.codigo_formato,
                a.estado,
                a.porcentaje_cumplimiento,
                c.nombre_completo as consultor
            FROM clientes cl
            LEFT JOIN auditoria_clientes ac ON ac.id_cliente = cl.id_cliente
            LEFT JOIN auditorias a ON a.id_auditoria = ac.id_auditoria {$filtroAnio}
            LEFT JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            LEFT JOIN consultores c ON c.id_consultor = a.id_consultor
            ORDER BY cl.razon_social ASC, a.created_at DESC
        ")->getResultArray();

        // Agrupar por cliente
        $clientesAgrupados = [];
        foreach ($clientes as $row) {
            $idCliente = $row['id_cliente'];

            if (!isset($clientesAgrupados[$idCliente])) {
                $clientesAgrupados[$idCliente] = [
                    'id_cliente' => $idCliente,
                    'cliente' => $row['cliente'],
                    'cliente_nit' => $row['cliente_nit'],
                    'auditorias' => [],
                    'total_auditorias' => 0,
                    'cerradas' => 0,
                    'en_revision' => 0,
                    'en_proveedor' => 0,
                    'promedio_cumplimiento' => 0,
                ];
            }

            if ($row['id_auditoria']) {
                $clientesAgrupados[$idCliente]['auditorias'][] = [
                    'id_auditoria' => $row['id_auditoria'],
                    'codigo_formato' => $row['codigo_formato'],
                    'estado' => $row['estado'],
                    'proveedor' => $row['proveedor'],
                    'proveedor_nit' => $row['proveedor_nit'],
                    'consultor' => $row['consultor'],
                    'porcentaje_cumplimiento' => $row['porcentaje_cumplimiento'],
                ];

                $clientesAgrupados[$idCliente]['total_auditorias']++;

                if ($row['estado'] === 'cerrada') {
                    $clientesAgrupados[$idCliente]['cerradas']++;
                } elseif ($row['estado'] === 'en_revision_consultor') {
                    $clientesAgrupados[$idCliente]['en_revision']++;
                } elseif ($row['estado'] === 'en_proveedor') {
                    $clientesAgrupados[$idCliente]['en_proveedor']++;
                }
            }
        }

        // Calcular promedio de cumplimiento para clientes con auditorías cerradas
        foreach ($clientesAgrupados as &$cliente) {
            $totalPorcentaje = 0;
            $countCerradas = 0;
            foreach ($cliente['auditorias'] as $aud) {
                if ($aud['estado'] === 'cerrada' && $aud['porcentaje_cumplimiento'] !== null) {
                    $totalPorcentaje += $aud['porcentaje_cumplimiento'];
                    $countCerradas++;
                }
            }
            $cliente['promedio_cumplimiento'] = $countCerradas > 0
                ? round($totalPorcentaje / $countCerradas, 1)
                : null;
        }

        // Resumen general
        $resumen = [
            'total_clientes' => count($clientesAgrupados),
            'con_auditorias' => count(array_filter($clientesAgrupados, fn($c) => $c['total_auditorias'] > 0)),
            'sin_auditorias' => count(array_filter($clientesAgrupados, fn($c) => $c['total_auditorias'] === 0)),
            'total_auditorias' => array_sum(array_column($clientesAgrupados, 'total_auditorias')),
            'total_cerradas' => array_sum(array_column($clientesAgrupados, 'cerradas')),
            'total_en_revision' => array_sum(array_column($clientesAgrupados, 'en_revision')),
            'total_en_proveedor' => array_sum(array_column($clientesAgrupados, 'en_proveedor')),
        ];

        return view('admin/auditorias/reporte_clientes', [
            'title' => 'Reporte por Clientes',
            'clientes' => array_values($clientesAgrupados),
            'resumen' => $resumen,
            'anio' => $anio,
        ]);
    }
}
