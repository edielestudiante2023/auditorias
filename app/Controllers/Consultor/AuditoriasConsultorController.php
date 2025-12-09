<?php

namespace App\Controllers\Consultor;

use App\Controllers\BaseController;
use App\Models\AuditoriaModel;
use App\Models\ConsultorModel;
use App\Models\AuditoriaItemModel;
use App\Models\EvidenciaModel;
use App\Models\EvidenciaClienteModel;
use App\Models\AuditoriaClienteModel;
use App\Models\ContratoModel;
use App\Models\AuditoriaItemClienteModel;
use App\Models\ItemsBancoModel;
use App\Models\AuditoriaLogModel;
use App\Services\PdfService;
use App\Services\EmailService;

class AuditoriasConsultorController extends BaseController
{
    protected $auditoriaModel;
    protected $consultorModel;
    protected $auditoriaItemModel;
    protected $evidenciaModel;
    protected $evidenciaClienteModel;
    protected $auditoriaClienteModel;
    protected $contratoModel;
    protected $auditoriaItemClienteModel;
    protected $itemsBancoModel;
    protected $auditoriaLogModel;
    protected $pdfService;
    protected $emailService;
    protected $idConsultor;

    public function __construct()
    {
        $this->auditoriaModel = new AuditoriaModel();
        $this->consultorModel = new ConsultorModel();
        $this->auditoriaItemModel = new AuditoriaItemModel();
        $this->evidenciaModel = new EvidenciaModel();
        $this->evidenciaClienteModel = model('App\Models\EvidenciaClienteModel');
        $this->auditoriaClienteModel = new AuditoriaClienteModel();
        $this->contratoModel = new ContratoModel();
        $this->auditoriaItemClienteModel = new AuditoriaItemClienteModel();
        $this->itemsBancoModel = new ItemsBancoModel();
        $this->auditoriaLogModel = model('App\Models\AuditoriaLogModel');
        $this->pdfService = new PdfService();
        $this->emailService = new EmailService();
        helper(['auth', 'upload']);

        $consultor = $this->consultorModel->where('id_users', userId())->first();
        $this->idConsultor = $consultor['id_consultor'] ?? null;
    }

    public function index()
    {
        if (!$this->idConsultor) {
            return view('consultor/sin_consultor');
        }

        $anio = $this->request->getGet('anio') ?? date('Y');
        $anioParam = ($anio !== 'todos') ? (int)$anio : null;

        $auditorias = $this->auditoriaModel->getAuditoriasByConsultor($this->idConsultor, $anioParam);

        return view('consultor/auditorias/index', [
            'title' => 'Mis Auditorías',
            'auditorias' => $auditorias,
            'anio' => $anio,
        ]);
    }

    public function detalle(int $idAuditoria)
    {
        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            return redirect()->to('/consultor/auditorias')->with('error', 'Auditoría no encontrada');
        }

        // Obtener items con alcance
        $items = $this->getItemsConAlcanceConsultor($idAuditoria);
        $clientes = $this->auditoriaClienteModel->getClientesByAuditoria($idAuditoria);

        // Enriquecer clientes con personal asignado
        $personalAsignadoModel = new \App\Models\PersonalAsignadoModel();
        foreach ($clientes as &$cliente) {
            $cliente['personal'] = $personalAsignadoModel->getPersonalByProveedorCliente(
                $auditoria['id_proveedor'],
                $cliente['id_cliente']
            );
        }

        return view('consultor/auditorias/detalle', [
            'title' => 'Revisión de Auditoría',
            'auditoria' => $auditoria,
            'items' => $items,
            'clientes' => $clientes,
        ]);
    }

    /**
     * Califica ítem global
     */
    public function calificarItemGlobal(int $idAuditoriaItem)
    {
        // DEBUG: Log de lo que recibe
        log_message('info', 'calificarItemGlobal called - ID: ' . $idAuditoriaItem);
        log_message('info', 'POST data: ' . json_encode($this->request->getPost()));

        $item = $this->auditoriaItemModel->find($idAuditoriaItem);

        if (!$item) {
            log_message('error', 'Item not found: ' . $idAuditoriaItem);
            return redirect()->back()->with('error', 'Ítem no encontrado');
        }

        // Verificar que la auditoría pertenece al consultor
        $auditoria = $this->auditoriaModel->find($item['id_auditoria']);
        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            log_message('error', 'Permission denied for consultor: ' . $this->idConsultor);
            return redirect()->back()->with('error', 'No tiene permisos para calificar este ítem');
        }

        $calificacion = $this->request->getPost('calificacion_consultor');
        $comentario = $this->request->getPost('comentario_consultor');

        log_message('info', 'Updating item ' . $idAuditoriaItem . ' with calificacion: ' . $calificacion);

        $this->auditoriaItemModel->update($idAuditoriaItem, [
            'calificacion_consultor' => $calificacion,
            'comentario_consultor' => $comentario,
        ]);

        log_message('info', 'Item updated successfully');

        return redirect()->to('consultor/auditoria/' . $item['id_auditoria'] . '#item-' . $idAuditoriaItem)->with('success', '✅ Calificación global guardada exitosamente');
    }

    /**
     * Califica ítem por cliente específico
     */
    public function calificarItemPorCliente(int $idAuditoriaItem, int $idCliente)
    {
        $item = $this->auditoriaItemModel->find($idAuditoriaItem);

        if (!$item) {
            return redirect()->back()->with('error', 'Ítem no encontrado');
        }

        // Verificar que la auditoría pertenece al consultor
        $auditoria = $this->auditoriaModel->find($item['id_auditoria']);
        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            return redirect()->back()->with('error', 'No tiene permisos para calificar este ítem');
        }

        // Verificar que el cliente está asignado a la auditoría
        $clienteAsignado = $this->auditoriaClienteModel
            ->where('id_auditoria', $auditoria['id_auditoria'])
            ->where('id_cliente', $idCliente)
            ->first();

        if (!$clienteAsignado) {
            return redirect()->back()->with('error', 'El cliente no está asignado a esta auditoría');
        }

        // Buscar o crear registro en auditoria_item_cliente
        $itemCliente = $this->auditoriaItemClienteModel
            ->where('id_auditoria_item', $idAuditoriaItem)
            ->where('id_cliente', $idCliente)
            ->first();

        $calificacion = $this->request->getPost('calificacion_ajustada');
        $comentario = $this->request->getPost('comentario_cliente');

        if (!$itemCliente) {
            // Crear registro
            $this->auditoriaItemClienteModel->insert([
                'id_auditoria_item' => $idAuditoriaItem,
                'id_cliente' => $idCliente,
                'calificacion_ajustada' => $calificacion,
                'comentario_cliente' => $comentario,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            // Actualizar registro
            $this->auditoriaItemClienteModel->update($itemCliente['id_auditoria_item_cliente'], [
                'calificacion_ajustada' => $calificacion,
                'comentario_cliente' => $comentario,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('consultor/auditoria/' . $item['id_auditoria'] . '#item-' . $idAuditoriaItem)->with('success', '✅ Calificación del cliente guardada exitosamente');
    }

    /**
     * Autosave - Guarda calificación vía AJAX sin recargar página
     */
    public function autosave(int $idAuditoria)
    {
        // Verificar que es una petición AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Petición inválida']);
        }

        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Auditoría no encontrada']);
        }

        // Solo permitir autosave en auditorías en revisión
        if ($auditoria['estado'] !== 'en_revision_consultor') {
            return $this->response->setJSON(['ok' => false, 'message' => 'La auditoría no está en estado de revisión']);
        }

        $tipo = $this->request->getPost('tipo'); // 'global' o 'cliente'
        $idAuditoriaItem = $this->request->getPost('id_auditoria_item');
        $calificacion = $this->request->getPost('calificacion');
        $comentario = $this->request->getPost('comentario');

        // Verificar que el ítem pertenece a esta auditoría
        $item = $this->auditoriaItemModel->find($idAuditoriaItem);
        if (!$item || $item['id_auditoria'] != $idAuditoria) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Ítem no válido']);
        }

        try {
            if ($tipo === 'global') {
                // Guardar calificación global
                $this->auditoriaItemModel->update($idAuditoriaItem, [
                    'calificacion_consultor' => $calificacion ?: null,
                    'comentario_consultor' => $comentario,
                ]);
            } else if ($tipo === 'cliente') {
                $idCliente = $this->request->getPost('id_cliente');

                // Verificar que el cliente está asignado
                $clienteAsignado = $this->auditoriaClienteModel
                    ->where('id_auditoria', $idAuditoria)
                    ->where('id_cliente', $idCliente)
                    ->first();

                if (!$clienteAsignado) {
                    return $this->response->setJSON(['ok' => false, 'message' => 'Cliente no asignado']);
                }

                // Buscar o crear registro
                $itemCliente = $this->auditoriaItemClienteModel
                    ->where('id_auditoria_item', $idAuditoriaItem)
                    ->where('id_cliente', $idCliente)
                    ->first();

                if (!$itemCliente) {
                    $this->auditoriaItemClienteModel->insert([
                        'id_auditoria_item' => $idAuditoriaItem,
                        'id_cliente' => $idCliente,
                        'calificacion_ajustada' => $calificacion ?: null,
                        'comentario_cliente' => $comentario,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                    $this->auditoriaItemClienteModel->update($itemCliente['id_auditoria_item_cliente'], [
                        'calificacion_ajustada' => $calificacion ?: null,
                        'comentario_cliente' => $comentario,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } else {
                return $this->response->setJSON(['ok' => false, 'message' => 'Tipo inválido']);
            }

            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Guardado',
                'timestamp' => date('H:i:s'),
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Autosave error: ' . $e->getMessage());
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Error al guardar',
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    public function asignarClientes(int $idAuditoria)
    {
        $auditoria = $this->auditoriaModel->find($idAuditoria);
        $clientes = $this->contratoModel->getClientesByProveedor($auditoria['id_proveedor']);
        $asignados = $this->auditoriaClienteModel->getClientesByAuditoria($idAuditoria);

        return view('consultor/auditorias/asignar_clientes', [
            'title' => 'Asignar Clientes',
            'auditoria' => $auditoria,
            'clientes' => $clientes,
            'asignados' => array_column($asignados, 'id_cliente'),
        ]);
    }

    public function guardarClientes(int $idAuditoria)
    {
        $clientesSeleccionados = $this->request->getPost('clientes') ?? [];

        // Eliminar asignaciones anteriores
        $this->auditoriaClienteModel->where('id_auditoria', $idAuditoria)->delete();

        // Crear nuevas asignaciones
        foreach ($clientesSeleccionados as $idCliente) {
            $this->auditoriaClienteModel->insert([
                'id_auditoria' => $idAuditoria,
                'id_cliente' => $idCliente,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/consultor/auditoria/' . $idAuditoria)->with('success', 'Clientes asignados');
    }

    /**
     * Aplicar override (ajuste específico) a un ítem para un cliente
     */
    public function override(int $idAuditoria)
    {
        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Auditoría no encontrada'
            ]);
        }

        $idAuditoriaItem = $this->request->getPost('id_auditoria_item');
        $idCliente = $this->request->getPost('id_cliente');
        $calificacionAjustada = $this->request->getPost('calificacion_ajustada');
        $comentarioCliente = $this->request->getPost('comentario_cliente');

        // Validar que el cliente esté asignado a la auditoría
        $clienteAsignado = $this->auditoriaClienteModel
            ->where('id_auditoria', $idAuditoria)
            ->where('id_cliente', $idCliente)
            ->first();

        if (!$clienteAsignado) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'El cliente no está asignado a esta auditoría'
            ]);
        }

        // Guardar override
        $saved = $this->auditoriaItemClienteModel->saveOverride(
            $idAuditoriaItem,
            $idCliente,
            $calificacionAjustada,
            $comentarioCliente
        );

        if ($saved) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Override aplicado correctamente'
            ]);
        } else {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Error al guardar el override'
            ]);
        }
    }

    /**
     * Obtiene items con alcance y todos sus datos para el consultor
     */
    private function getItemsConAlcanceConsultor(int $idAuditoria): array
    {
        $items = $this->auditoriaItemModel
            ->select('auditoria_items.*, items_banco.codigo_item, items_banco.titulo, items_banco.descripcion, items_banco.alcance')
            ->join('items_banco', 'items_banco.id_item = auditoria_items.id_item')
            ->where('auditoria_items.id_auditoria', $idAuditoria)
            ->orderBy('items_banco.orden', 'ASC')
            ->findAll();

        foreach ($items as &$item) {
            if ($item['alcance'] === 'global') {
                // Evidencias globales
                $item['evidencias'] = $this->evidenciaModel
                    ->where('id_auditoria_item', $item['id_auditoria_item'])
                    ->findAll();
            } else {
                // Items por cliente con evidencias y calificaciones
                $item['items_cliente'] = $this->auditoriaItemClienteModel
                    ->select('auditoria_item_cliente.*, clientes.razon_social, clientes.nit')
                    ->join('clientes', 'clientes.id_cliente = auditoria_item_cliente.id_cliente')
                    ->where('id_auditoria_item', $item['id_auditoria_item'])
                    ->findAll();

                // Cargar evidencias por cada item-cliente
                foreach ($item['items_cliente'] as &$itemCliente) {
                    $itemCliente['evidencias'] = $this->evidenciaClienteModel
                        ->where('id_auditoria_item_cliente', $itemCliente['id_auditoria_item_cliente'])
                        ->findAll();
                }
            }
        }

        return $items;
    }

    /**
     * Calcula porcentaje global del proveedor
     * Considera SOLO ítems globales (alcance='global' o 'mixto')
     * Mapea: cumple=1, parcial=0.5, no_cumple=0; no_aplica excluido
     */
    private function calcularPorcentajeGlobal(int $idAuditoria): float
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT ai.calificacion_consultor as calificacion
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance IN ('global', 'mixto')
              AND ai.calificacion_consultor IS NOT NULL
        ", [$idAuditoria]);

        $items = $query->getResultArray();

        if (empty($items)) {
            return 0.0;
        }

        $totalPuntos = 0;
        $totalItems = 0;

        foreach ($items as $item) {
            $calificacion = $item['calificacion'];

            if ($calificacion === 'no_aplica') {
                continue; // Excluir del denominador
            }

            $totalItems++;

            switch ($calificacion) {
                case 'cumple':
                    $totalPuntos += 1;
                    break;
                case 'parcial':
                    $totalPuntos += 0.5;
                    break;
                case 'no_cumple':
                    $totalPuntos += 0;
                    break;
            }
        }

        if ($totalItems === 0) {
            return 0.0;
        }

        return round(($totalPuntos / $totalItems) * 100, 2);
    }

    /**
     * Calcula porcentaje por cliente
     * Considera:
     * - Todos los ítems por_cliente evaluados (calificacion_ajustada)
     * - Ítems globales (aplican a todos los clientes)
     * Mapea: cumple=1, parcial=0.5, no_cumple=0; no_aplica excluido
     */
    private function calcularPorcentajePorCliente(int $idAuditoria, int $idCliente): float
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT
                ib.alcance,
                CASE
                    WHEN ib.alcance = 'por_cliente' THEN aic.calificacion_ajustada
                    ELSE ai.calificacion_consultor
                END as calificacion
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            LEFT JOIN auditoria_item_cliente aic ON aic.id_auditoria_item = ai.id_auditoria_item
                AND aic.id_cliente = ?
            WHERE ai.id_auditoria = ?
              AND (
                (ib.alcance = 'por_cliente' AND aic.calificacion_ajustada IS NOT NULL)
                OR
                (ib.alcance IN ('global', 'mixto') AND ai.calificacion_consultor IS NOT NULL)
              )
        ", [$idCliente, $idAuditoria]);

        $items = $query->getResultArray();

        if (empty($items)) {
            return 0.0;
        }

        $totalPuntos = 0;
        $totalItems = 0;

        foreach ($items as $item) {
            $calificacion = $item['calificacion'];

            if ($calificacion === 'no_aplica') {
                continue; // Excluir del denominador
            }

            $totalItems++;

            switch ($calificacion) {
                case 'cumple':
                    $totalPuntos += 1;
                    break;
                case 'parcial':
                    $totalPuntos += 0.5;
                    break;
                case 'no_cumple':
                    $totalPuntos += 0;
                    break;
            }
        }

        if ($totalItems === 0) {
            return 0.0;
        }

        return round(($totalPuntos / $totalItems) * 100, 2);
    }

    /**
     * Cerrar auditoría:
     * - Calcular porcentaje global (solo ítems globales/mixto)
     * - Calcular porcentaje por cliente (por_cliente + globales)
     * - Cambiar estado a cerrada
     * - Los PDFs se generan BAJO DEMANDA (no automáticamente)
     */
    public function cerrar(int $idAuditoria)
    {
        $db = \Config\Database::connect();

        $auditoria = $this->auditoriaModel->find($idAuditoria);
        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            return redirect()->to('/consultor/auditorias')->with('error', 'Auditoría no encontrada');
        }

        if ($auditoria['estado'] === 'cerrada') {
            return redirect()->back()->with('warning', 'La auditoría ya está cerrada');
        }

        try {
            $db->transStart();

            // PASO 1: Calcular porcentaje global (solo ítems globales/mixto)
            $porcentajeGlobal = $this->calcularPorcentajeGlobal($idAuditoria);

            // PASO 2: Procesar cada cliente (solo calcular porcentajes, NO generar PDFs)
            $clientes = $this->auditoriaClienteModel->getClientesByAuditoria($idAuditoria);

            foreach ($clientes as $cliente) {
                $idCliente = $cliente['id_cliente'];

                // Calcular porcentaje del cliente (por_cliente + globales)
                $porcentajeCliente = $this->calcularPorcentajePorCliente($idAuditoria, $idCliente);

                // Actualizar o insertar porcentaje
                $existeRegistro = $db->table('auditoria_clientes')
                    ->where('id_auditoria', $idAuditoria)
                    ->where('id_cliente', $idCliente)
                    ->countAllResults() > 0;

                if ($existeRegistro) {
                    $db->table('auditoria_clientes')
                        ->where('id_auditoria', $idAuditoria)
                        ->where('id_cliente', $idCliente)
                        ->update(['porcentaje_cumplimiento' => $porcentajeCliente]);
                } else {
                    $db->table('auditoria_clientes')->insert([
                        'id_auditoria' => $idAuditoria,
                        'id_cliente' => $idCliente,
                        'porcentaje_cumplimiento' => $porcentajeCliente,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            // PASO 3: Cambiar estado a cerrada
            $this->auditoriaModel->update($idAuditoria, [
                'estado' => 'cerrada',
                'porcentaje_cumplimiento' => $porcentajeGlobal,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Registrar en bitácora
            $this->auditoriaLogModel->registrarAuditoriaCerrada(
                $idAuditoria,
                $porcentajeGlobal,
                count($clientes)
            );

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al procesar la transacción');
            }

            return redirect()->to('/consultor/auditoria/' . $idAuditoria)
                ->with('success', "✅ Auditoría cerrada exitosamente. Porcentaje global: {$porcentajeGlobal}%. Ahora puedes descargar los PDFs por cliente.");

        } catch (\Exception $e) {
            log_message('error', 'Error al cerrar auditoría: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cerrar auditoría: ' . $e->getMessage());
        }
    }

    /**
     * Muestra la bitácora (log) de una auditoría con paginación
     *
     * @param int $idAuditoria
     * @return string Vista de bitácora
     */
    public function bitacora(int $idAuditoria)
    {
        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            return redirect()->to('/consultor/auditorias')->with('error', 'Auditoría no encontrada');
        }

        // Obtener bitácora paginada (20 registros por página)
        $resultado = $this->auditoriaLogModel->getBitacoraPaginada($idAuditoria, 20);

        // Obtener estadísticas de acciones
        $estadisticas = $this->auditoriaLogModel->getEstadisticas($idAuditoria);
        $totalAcciones = $this->auditoriaLogModel->contarAcciones($idAuditoria);

        return view('consultor/auditorias/bitacora', [
            'title' => 'Bitácora de Auditoría',
            'auditoria' => $auditoria,
            'logs' => $resultado['logs'],
            'pager' => $resultado['pager'],
            'estadisticas' => $estadisticas,
            'total_acciones' => $totalAcciones,
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva auditoría
     */
    public function crear()
    {
        if (!$this->idConsultor) {
            return view('consultor/sin_consultor');
        }

        // Obtener proveedores activos
        $proveedorModel = model('App\Models\ProveedorModel');
        $proveedores = $proveedorModel->where('id_users IS NOT NULL')->findAll();

        // Obtener información del consultor
        $consultor = $this->consultorModel->find($this->idConsultor);

        return view('consultor/auditorias/crear', [
            'title' => 'Nueva Auditoría',
            'proveedores' => $proveedores,
            'consultor' => $consultor,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    /**
     * Guarda una nueva auditoría (Paso 1)
     */
    public function guardar()
    {
        if (!$this->idConsultor) {
            return redirect()->to('/consultor/auditorias')->with('error', 'No tienes acceso a esta función');
        }

        // Validación manual
        $rules = [
            'id_proveedor' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_proveedor' => $this->request->getPost('id_proveedor'),
            'id_consultor' => $this->idConsultor,
            'codigo_formato' => $this->request->getPost('codigo_formato'),
            'version_formato' => $this->request->getPost('version_formato'),
            'fecha_programada' => $this->request->getPost('fecha_programada') ?: null,
            'estado' => 'borrador',
        ];

        if (!$this->auditoriaModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Error al crear la auditoría');
        }

        $idAuditoria = $this->auditoriaModel->getInsertID();

        return redirect()->to("/consultor/auditorias/asignar-clientes/{$idAuditoria}")
            ->with('success', 'Auditoría creada. Ahora asigne los clientes.');
    }

    /**
     * Muestra auditorías pendientes de revisión (en_revision_consultor)
     */
    public function pendientes()
    {
        if (!$this->idConsultor) {
            return view('consultor/sin_consultor');
        }

        $anio = $this->request->getGet('anio') ?? date('Y');

        $builder = $this->auditoriaModel
            ->where('id_consultor', $this->idConsultor)
            ->where('estado', 'en_revision_consultor');

        if ($anio !== 'todos') {
            $builder->where('YEAR(created_at)', (int)$anio);
        }

        $auditorias = $builder->orderBy('updated_at', 'DESC')->findAll();

        // Enriquecer con información de proveedor
        foreach ($auditorias as &$auditoria) {
            $proveedor = model('App\Models\ProveedorModel')->find($auditoria['id_proveedor']);
            $auditoria['proveedor_nombre'] = $proveedor['razon_social'] ?? 'N/A';
            $auditoria['proveedor_nit'] = $proveedor['nit'] ?? 'N/A';
        }

        return view('consultor/auditorias/pendientes', [
            'title' => 'Pendientes de Revisión',
            'auditorias' => $auditorias,
            'anio' => $anio,
        ]);
    }

    /**
     * Muestra reportes de auditorías cerradas
     */
    public function reportes()
    {
        if (!$this->idConsultor) {
            return view('consultor/sin_consultor');
        }

        $anio = $this->request->getGet('anio') ?? date('Y');

        $builder = $this->auditoriaModel
            ->where('id_consultor', $this->idConsultor)
            ->where('estado', 'cerrada');

        if ($anio !== 'todos') {
            $builder->where('YEAR(created_at)', (int)$anio);
        }

        $auditorias = $builder->orderBy('updated_at', 'DESC')->findAll();

        // Enriquecer con información de proveedor y clientes
        foreach ($auditorias as &$auditoria) {
            $proveedor = model('App\Models\ProveedorModel')->find($auditoria['id_proveedor']);
            $auditoria['proveedor_nombre'] = $proveedor['razon_social'] ?? 'N/A';
            $auditoria['proveedor_nit'] = $proveedor['nit'] ?? 'N/A';

            // Obtener clientes con sus PDFs
            $clientes = $this->auditoriaClienteModel->getClientesByAuditoria($auditoria['id_auditoria']);
            $auditoria['total_clientes'] = count($clientes);
            $auditoria['clientes'] = $clientes;
        }

        return view('consultor/auditorias/reportes', [
            'title' => 'Reportes',
            'auditorias' => $auditorias,
            'anio' => $anio,
        ]);
    }

    /**
     * Muestra y permite editar el perfil del consultor
     */
    public function perfil()
    {
        if (!$this->idConsultor) {
            return view('consultor/sin_consultor');
        }

        $consultor = $this->consultorModel->find($this->idConsultor);
        $usuario = model('App\Models\UserModel')->find($consultor['id_users']);

        // Agregar email del usuario al array consultor para la vista
        if ($usuario) {
            $consultor['email'] = $usuario['email'];
        }

        return view('consultor/perfil', [
            'title' => 'Mi Perfil',
            'consultor' => $consultor,
            'usuario' => $usuario,
        ]);
    }

    /**
     * Actualiza el perfil del consultor
     */
    public function actualizarPerfil()
    {
        if (!$this->idConsultor) {
            return redirect()->to('/consultor/dashboard')->with('error', 'No tienes acceso a esta función');
        }

        $consultor = $this->consultorModel->find($this->idConsultor);

        $dataConsultor = [
            'nombre_completo' => $this->request->getPost('nombre_completo'),
            'email' => $this->request->getPost('email'),
            'telefono' => $this->request->getPost('telefono'),
            'licencia_sst' => $this->request->getPost('licencia_sst'),
        ];

        // Manejar subida de firma si hay archivo nuevo
        $firma = $this->request->getFile('firma');
        if ($firma && $firma->isValid() && !$firma->hasMoved()) {
            helper('upload');
            $uploadService = (new \App\Services\UploadService())->setDatabase(\Config\Database::connect());

            try {
                $resultFirma = $uploadService->uploadFirmaConsultor($firma, $this->idConsultor);

                if ($resultFirma['ok']) {
                    // Eliminar firma anterior si existe
                    if (!empty($consultor['firma_path'])) {
                        @unlink(WRITEPATH . 'uploads/' . $consultor['firma_path']);
                    }
                    $dataConsultor['firma_path'] = $resultFirma['path'];
                }
            } catch (\Exception $e) {
                log_message('error', 'Error al subir firma: ' . $e->getMessage());
            }
        }

        $this->consultorModel->update($this->idConsultor, $dataConsultor);

        return redirect()->to('consultor/perfil')->with('success', '✅ Perfil actualizado exitosamente');
    }

    /**
     * Ver/Descargar evidencia global
     */
    public function verEvidencia(int $idEvidencia)
    {
        $evidencia = $this->evidenciaModel->find($idEvidencia);

        if (!$evidencia) {
            return redirect()->back()->with('error', 'Evidencia no encontrada');
        }

        // Verificar que la evidencia pertenece a una auditoría del consultor
        $auditoriaItem = $this->auditoriaItemModel->find($evidencia['id_auditoria_item']);
        if (!$auditoriaItem) {
            return redirect()->back()->with('error', 'Ítem no encontrado');
        }

        $auditoria = $this->auditoriaModel->find($auditoriaItem['id_auditoria']);
        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            return redirect()->back()->with('error', 'No tiene permisos para ver esta evidencia');
        }

        // Servir el archivo
        $filePath = WRITEPATH . $evidencia['ruta_archivo'];

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Archivo no encontrado en el servidor');
        }

        // Determinar si mostrar inline (en navegador) o descargar
        $mimeType = $evidencia['tipo_mime'];
        $inline = in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', ($inline ? 'inline' : 'attachment') . '; filename="' . $evidencia['nombre_archivo_original'] . '"')
            ->setHeader('Content-Length', $evidencia['tamanio_bytes'])
            ->setBody(file_get_contents($filePath));
    }

    /**
     * Ver/Descargar evidencia de cliente
     */
    public function verEvidenciaCliente(int $idEvidenciaCliente)
    {
        $evidencia = $this->evidenciaClienteModel->find($idEvidenciaCliente);

        if (!$evidencia) {
            return redirect()->back()->with('error', 'Evidencia no encontrada');
        }

        // Verificar que la evidencia pertenece a una auditoría del consultor
        $auditoriaItemCliente = $this->auditoriaItemClienteModel->find($evidencia['id_auditoria_item_cliente']);
        if (!$auditoriaItemCliente) {
            return redirect()->back()->with('error', 'Ítem de cliente no encontrado');
        }

        $auditoriaItem = $this->auditoriaItemModel->find($auditoriaItemCliente['id_auditoria_item']);
        if (!$auditoriaItem) {
            return redirect()->back()->with('error', 'Ítem no encontrado');
        }

        $auditoria = $this->auditoriaModel->find($auditoriaItem['id_auditoria']);
        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            return redirect()->back()->with('error', 'No tiene permisos para ver esta evidencia');
        }

        // Servir el archivo
        $filePath = WRITEPATH . $evidencia['ruta_archivo'];

        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'Archivo no encontrado en el servidor');
        }

        // Determinar si mostrar inline (en navegador) o descargar
        $mimeType = $evidencia['tipo_mime'];
        $inline = in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', ($inline ? 'inline' : 'attachment') . '; filename="' . $evidencia['nombre_archivo_original'] . '"')
            ->setHeader('Content-Length', $evidencia['tamanio_bytes'])
            ->setBody(file_get_contents($filePath));
    }

    /**
     * Generar y descargar PDF de un cliente (bajo demanda)
     */
    public function descargarPdfCliente(int $idAuditoria, int $idCliente)
    {
        // Verificar permisos
        $auditoria = $this->auditoriaModel->find($idAuditoria);
        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            return redirect()->back()->with('error', 'No tiene permisos para descargar este PDF');
        }

        // Verificar que el cliente esté asignado a esta auditoría
        $db = \Config\Database::connect();
        $clienteAsignado = $db->table('auditoria_clientes')
            ->where('id_auditoria', $idAuditoria)
            ->where('id_cliente', $idCliente)
            ->countAllResults() > 0;

        if (!$clienteAsignado) {
            return redirect()->back()->with('error', 'Cliente no asignado a esta auditoría');
        }

        try {
            // Aumentar límite de memoria temporalmente
            ini_set('memory_limit', '1024M');
            set_time_limit(300); // 5 minutos

            // Generar PDF
            log_message('info', "Generando PDF para auditoría {$idAuditoria}, cliente {$idCliente}");
            $rutaPdf = $this->pdfService->generarPdfCliente($idAuditoria, $idCliente);

            // Descargar PDF
            $fullPath = WRITEPATH . $rutaPdf;

            if (!file_exists($fullPath)) {
                return redirect()->back()->with('error', 'Error al generar el PDF');
            }

            return $this->response->download($fullPath, null);

        } catch (\Exception $e) {
            log_message('error', 'Error al generar PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }

    /**
     * Enviar PDF por email al usuario responsable del proveedor (bajo demanda)
     */
    public function enviarPdfCliente(int $idAuditoria, int $idCliente)
    {
        // Verificar permisos
        $auditoria = $this->auditoriaModel->find($idAuditoria);
        if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
            return redirect()->back()->with('error', 'No tiene permisos');
        }

        // Obtener datos del cliente
        $db = \Config\Database::connect();
        $cliente = $db->table('clientes')->where('id_cliente', $idCliente)->get()->getRowArray();

        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado');
        }

        // Obtener el contrato y el usuario responsable del proveedor (con LEFT JOIN para manejar usuarios eliminados)
        $contrato = $db->table('contratos_proveedor_cliente cpc')
            ->select('cpc.id_contrato, cpc.id_usuario_responsable,
                      users.email as usuario_responsable_email,
                      users.nombre as usuario_responsable_nombre')
            ->join('users', 'users.id_users = cpc.id_usuario_responsable', 'left')
            ->where('cpc.id_proveedor', $auditoria['id_proveedor'])
            ->where('cpc.id_cliente', $idCliente)
            ->where('cpc.estado', 'activo')
            ->get()
            ->getRowArray();

        // Fallback: si no hay usuario responsable válido, usar datos del proveedor
        $emailDestino = null;
        $nombreDestino = null;

        if ($contrato && !empty($contrato['usuario_responsable_email'])) {
            // Caso normal: usuario responsable existe
            $emailDestino = $contrato['usuario_responsable_email'];
            $nombreDestino = $contrato['usuario_responsable_nombre'];
        } else {
            // Fallback: buscar email del proveedor
            $proveedor = $db->table('proveedores')
                ->select('responsable_email, responsable_nombre, email_contacto, razon_social')
                ->where('id_proveedor', $auditoria['id_proveedor'])
                ->get()
                ->getRowArray();

            if ($proveedor) {
                // Prioridad: responsable_email > email_contacto
                $emailDestino = $proveedor['responsable_email'] ?: $proveedor['email_contacto'];
                $nombreDestino = $proveedor['responsable_nombre'] ?: $proveedor['razon_social'];
            }
        }

        if (empty($emailDestino)) {
            return redirect()->back()->with('error', 'No se encontró el usuario responsable del proveedor o no tiene email configurado');
        }

        try {
            // Aumentar límite de memoria
            ini_set('memory_limit', '1024M');
            set_time_limit(300);

            // Generar PDF
            log_message('info', "Generando PDF para enviar por email - Auditoría {$idAuditoria}, Cliente {$idCliente}");
            $rutaPdf = $this->pdfService->generarPdfCliente($idAuditoria, $idCliente);
            $fullPath = WRITEPATH . $rutaPdf;

            if (!file_exists($fullPath)) {
                return redirect()->back()->with('error', 'Error al generar el PDF');
            }

            // Enviar email con PDF adjunto al usuario responsable del proveedor
            $resultado = $this->emailService->enviarPdfCliente(
                $idAuditoria,
                $idCliente,
                $emailDestino,
                $cliente['razon_social'],
                $fullPath
            );

            if ($resultado['ok']) {
                return redirect()->back()->with('success', "✅ PDF enviado exitosamente a {$nombreDestino} ({$emailDestino})");
            } else {
                return redirect()->back()->with('error', 'Error al enviar email: ' . ($resultado['error'] ?? 'Desconocido'));
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al enviar PDF por email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una auditoría (solo consultor que la creó, solo en estados borrador o en_proveedor)
     */
    public function eliminar($idAuditoria = null)
    {
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

        // Verificar que sea el consultor propietario
        if ($auditoria['id_consultor'] != $this->idConsultor) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes permiso para eliminar esta auditoría'
            ]);
        }

        // Solo permitir eliminar en estados: borrador, asignada, en_progreso, en_proveedor
        $estadosPermitidos = ['borrador', 'asignada', 'en_progreso', 'en_proveedor'];
        if (!in_array($auditoria['estado'], $estadosPermitidos)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se puede eliminar una auditoría en estado: ' . $auditoria['estado']
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
}
