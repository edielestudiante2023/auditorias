<?php

namespace App\Controllers\Proveedor;

use App\Controllers\BaseController;
use App\Models\AuditoriaModel;
use App\Models\ProveedorModel;
use App\Models\AuditoriaItemModel;
use App\Models\ItemsBancoModel;
use App\Models\EvidenciaModel;
use App\Models\AuditoriaClienteModel;
use App\Models\AuditoriaItemClienteModel;
use App\Models\EvidenciaClienteModel;
use App\Models\AuditoriaLogModel;
use App\Services\UploadService;
use App\Services\EmailService;

class AuditoriasProveedorController extends BaseController
{
    protected $auditoriaModel;
    protected $proveedorModel;
    protected $auditoriaItemModel;
    protected $itemsBancoModel;
    protected $evidenciaModel;
    protected $auditoriaClienteModel;
    protected $auditoriaItemClienteModel;
    protected $evidenciaClienteModel;
    protected $auditoriaLogModel;
    protected $uploadService;
    protected $emailService;
    protected $idProveedor;

    public function __construct()
    {
        $this->auditoriaModel = new AuditoriaModel();
        $this->proveedorModel = model('App\Models\ProveedorModel');
        $this->auditoriaItemModel = model('App\Models\AuditoriaItemModel');
        $this->itemsBancoModel = new ItemsBancoModel();
        $this->evidenciaModel = model('App\Models\EvidenciaModel');
        $this->auditoriaClienteModel = model('App\Models\AuditoriaClienteModel');
        $this->auditoriaItemClienteModel = model('App\Models\AuditoriaItemClienteModel');
        $this->evidenciaClienteModel = model('App\Models\EvidenciaClienteModel');
        $this->auditoriaLogModel = model('App\Models\AuditoriaLogModel');
        $this->uploadService = (new UploadService())->setDatabase(\Config\Database::connect());
        $this->emailService = new EmailService();
        helper(['auth', 'upload']);

        // NOTA: No buscamos por id_proveedor vinculado al usuario
        // El usuario responsable puede trabajar para múltiples proveedores
        // Las auditorías se asignan por usuario_responsable en contratos_proveedor_cliente
        $this->idProveedor = null; // Ya no se usa
    }

    /**
     * Lista auditorías asignadas al usuario responsable actual
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // Verificar si el usuario tiene personal asignado a todos sus clientes
        $contrato = $db->table('contratos_proveedor_cliente')
            ->select('id_proveedor')
            ->where('id_usuario_responsable', userId())
            ->where('estado', 'activo')
            ->get()
            ->getRowArray();

        if ($contrato) {
            $idProveedor = $contrato['id_proveedor'];

            // Obtener clientes del proveedor
            $clientes = $db->table('contratos_proveedor_cliente')
                ->select('id_cliente')
                ->where('id_proveedor', $idProveedor)
                ->where('id_usuario_responsable', userId())
                ->where('estado', 'activo')
                ->get()
                ->getResultArray();

            // Verificar que cada cliente tenga al menos un personal asignado
            $personalModel = new \App\Models\PersonalAsignadoModel();
            $clientesSinPersonal = [];

            foreach ($clientes as $cliente) {
                if (!$personalModel->tienePersonalRegistrado($idProveedor, $cliente['id_cliente'])) {
                    $clienteModel = new \App\Models\ClienteModel();
                    $clienteInfo = $clienteModel->find($cliente['id_cliente']);
                    $clientesSinPersonal[] = $clienteInfo['razon_social'];
                }
            }

            // Si hay clientes sin personal, redirigir con alerta
            if (!empty($clientesSinPersonal)) {
                return redirect()->to('/proveedor/personal')->with('warning_personal', json_encode($clientesSinPersonal));
            }
        }

        // Primero obtener los años que tienen auditorías para este usuario
        $aniosConAuditorias = $db->query("
            SELECT DISTINCT YEAR(a.created_at) as anio, COUNT(*) as cantidad
            FROM auditorias a
            JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            WHERE cpc.id_usuario_responsable = ?
              AND cpc.estado = 'activo'
              AND a.estado IN ('en_proveedor', 'en_revision_consultor')
            GROUP BY YEAR(a.created_at)
            ORDER BY anio DESC
        ", [userId()])->getResultArray();

        // Convertir a array asociativo [año => cantidad]
        $auditoriasPorAnio = [];
        foreach ($aniosConAuditorias as $row) {
            $auditoriasPorAnio[$row['anio']] = $row['cantidad'];
        }

        // Si el usuario no especificó año y el año actual no tiene auditorías,
        // auto-seleccionar el año más reciente con auditorías
        $anioParam = $this->request->getGet('anio');
        if ($anioParam === null && !empty($auditoriasPorAnio)) {
            $anioActual = date('Y');
            if (!isset($auditoriasPorAnio[$anioActual])) {
                // Redirigir al año más reciente con auditorías
                $anioMasReciente = array_key_first($auditoriasPorAnio);
                return redirect()->to('/proveedor/auditorias?anio=' . $anioMasReciente);
            }
        }

        $anio = $anioParam ?? date('Y');
        $filtroAnio = ($anio !== 'todos') ? "AND YEAR(a.created_at) = " . (int)$anio : "";

        // Buscar auditorías asignadas a este usuario responsable
        // El usuario responsable se determina por el contrato activo entre cliente y proveedor
        $auditorias = $db->query("
            SELECT DISTINCT a.*,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            WHERE cpc.id_usuario_responsable = ?
              AND cpc.estado = 'activo'
              AND a.estado IN ('en_proveedor', 'en_revision_consultor')
              {$filtroAnio}
            ORDER BY a.created_at DESC
        ", [userId()])->getResultArray();

        // Calcular % avance por auditoría
        foreach ($auditorias as &$auditoria) {
            $progreso = $this->calcularProgreso($auditoria['id_auditoria']);
            $auditoria['progreso'] = $progreso['porcentaje_total'];
        }

        return view('proveedor/auditorias/index', [
            'title' => 'Mis Auditorías',
            'auditorias' => $auditorias,
            'anio' => $anio,
            'auditoriasPorAnio' => $auditoriasPorAnio,
        ]);
    }

    /**
     * Wizard para diligenciar auditoría
     */
    public function wizard(int $idAuditoria)
    {
        $db = \Config\Database::connect();

        // Verificar que la auditoría está asignada a este usuario responsable
        $auditoria = $db->query("
            SELECT a.*,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            WHERE a.id_auditoria = ?
              AND cpc.id_usuario_responsable = ?
              AND cpc.estado = 'activo'
        ", [$idAuditoria, userId()])->getRowArray();

        if (!$auditoria) {
            return redirect()->to('/proveedor/auditorias')->with('error', 'Auditoría no encontrada o no tiene acceso');
        }

        // Obtener clientes asignados
        $clientes = $this->auditoriaClienteModel->getClientesByAuditoria($idAuditoria);

        // VALIDACIÓN: Verificar que hay personal registrado para todos los clientes
        $personalModel = new \App\Models\PersonalAsignadoModel();
        $clientesSinPersonal = [];

        foreach ($clientes as $cliente) {
            if (!$personalModel->tienePersonalRegistrado($auditoria['id_proveedor'], $cliente['id_cliente'])) {
                $clientesSinPersonal[] = $cliente['razon_social'];
            }
        }

        if (!empty($clientesSinPersonal)) {
            $mensaje = 'Debes registrar el personal asignado antes de diligenciar la auditoría. Clientes sin personal: ' . implode(', ', $clientesSinPersonal);
            return redirect()->to('/proveedor/personal')->with('error', $mensaje);
        }

        // Obtener items con alcance y evidencias
        $items = $this->getItemsConAlcance($idAuditoria);

        return view('proveedor/auditorias/wizard', [
            'title' => 'Diligenciar Auditoría',
            'auditoria' => $auditoria,
            'items' => $items,
            'clientes' => $clientes,
            'progreso' => $this->calcularProgreso($idAuditoria),
        ]);
    }

    /**
     * Autosave - Guarda comentario vía AJAX sin recargar página
     */
    public function autosave(int $idAuditoria)
    {
        // Verificar que es una petición AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Petición inválida']);
        }

        $db = \Config\Database::connect();

        // Verificar que la auditoría está asignada a este usuario responsable
        $auditoria = $db->query("
            SELECT a.*
            FROM auditorias a
            JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            WHERE a.id_auditoria = ?
              AND cpc.id_usuario_responsable = ?
              AND cpc.estado = 'activo'
        ", [$idAuditoria, userId()])->getRowArray();

        if (!$auditoria) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Auditoría no encontrada']);
        }

        // Solo permitir autosave en auditorías en estado en_proveedor
        if ($auditoria['estado'] !== 'en_proveedor') {
            return $this->response->setJSON(['ok' => false, 'message' => 'La auditoría no está en estado editable']);
        }

        $tipo = $this->request->getPost('tipo'); // 'global' o 'cliente'
        $idAuditoriaItem = $this->request->getPost('id_auditoria_item');
        $comentario = $this->request->getPost('comentario');

        // Verificar que el ítem pertenece a esta auditoría
        $item = $this->auditoriaItemModel->find($idAuditoriaItem);
        if (!$item || $item['id_auditoria'] != $idAuditoria) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Ítem no válido']);
        }

        try {
            if ($tipo === 'global') {
                // Guardar comentario global
                $this->auditoriaItemModel->update($idAuditoriaItem, [
                    'comentario_proveedor' => $comentario,
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
                        'comentario_proveedor_cliente' => $comentario,
                        'created_at' => date('Y-m-d H:i:s'),
                    ], false); // skip validation
                } else {
                    $this->auditoriaItemClienteModel->update($itemCliente['id_auditoria_item_cliente'], [
                        'comentario_proveedor_cliente' => $comentario,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } else {
                return $this->response->setJSON(['ok' => false, 'message' => 'Tipo inválido']);
            }

            // Calcular progreso actualizado
            $progreso = $this->calcularProgreso($idAuditoria);

            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Guardado',
                'timestamp' => date('H:i:s'),
                'progreso' => $progreso,
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Autosave proveedor error: ' . $e->getMessage());
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Error al guardar',
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Guardar ítem completo vía AJAX (comentario + archivos)
     * Retorna JSON en lugar de redirect
     */
    public function guardarItemAjax(int $idAuditoria, int $idAuditoriaItem)
    {
        // Verificar que es una petición AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Petición inválida']);
        }

        $db = \Config\Database::connect();

        // Verificar acceso
        $auditoria = $db->query("
            SELECT a.*, p.nit as proveedor_nit
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            WHERE a.id_auditoria = ?
              AND cpc.id_usuario_responsable = ?
              AND cpc.estado = 'activo'
        ", [$idAuditoria, userId()])->getRowArray();

        if (!$auditoria) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Auditoría no encontrada',
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        }

        if ($auditoria['estado'] !== 'en_proveedor') {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'La auditoría no está en estado editable',
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        }

        $item = $this->auditoriaItemModel->find($idAuditoriaItem);
        if (!$item || $item['id_auditoria'] != $idAuditoria) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Ítem no válido',
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        }

        // Obtener alcance del ítem
        $itemBanco = $this->itemsBancoModel->find($item['id_item']);
        $idCliente = $this->request->getPost('id_cliente');
        $tipo = $itemBanco['alcance'] === 'global' ? 'global' : 'cliente';

        try {
            $archivosSubidos = [];

            if ($tipo === 'global') {
                // Guardar comentario global
                $comentario = $this->request->getPost('comentario_proveedor');

                // Si no hay comentario pero hay archivos, poner "Sin observaciones"
                $files = $this->request->getFileMultiple('evidencias');
                $hayArchivos = $files && count(array_filter($files, fn($f) => $f->isValid())) > 0;
                if (empty(trim($comentario ?? '')) && $hayArchivos) {
                    $comentario = 'Sin observaciones';
                }

                $this->auditoriaItemModel->update($idAuditoriaItem, [
                    'comentario_proveedor' => $comentario,
                ]);

                // Registrar en bitácora
                if (!empty($comentario)) {
                    $this->auditoriaLogModel->registrarComentarioGlobal(
                        $idAuditoria,
                        $idAuditoriaItem,
                        $itemBanco['titulo'] ?? 'Ítem sin título'
                    );
                }

                // Procesar evidencias globales
                $files = $this->request->getFileMultiple('evidencias');
                if ($files) {
                    foreach ($files as $file) {
                        if ($file->isValid() && !$file->hasMoved()) {
                            $validation = $this->validateFile($file);
                            if (!$validation['ok']) {
                                return $this->response->setJSON([
                                    'ok' => false,
                                    'message' => $validation['error'],
                                    'csrf_token' => csrf_token(),
                                    'csrf_hash' => csrf_hash()
                                ]);
                            }

                            $result = $this->uploadService->saveEvidencia([
                                'name' => $file->getName(),
                                'type' => $file->getClientMimeType(),
                                'tmp_name' => $file->getTempName(),
                                'error' => $file->getError(),
                                'size' => $file->getSize(),
                            ], $auditoria['proveedor_nit'], $idAuditoria, $idAuditoriaItem, [
                                'user_id' => userId(),
                                'id_auditoria' => $idAuditoria,
                                'id_item' => $item['id_item'],
                                'id_auditoria_item' => $idAuditoriaItem,
                            ]);

                            if ($result['ok']) {
                                $this->evidenciaModel->insert([
                                    'id_auditoria_item' => $idAuditoriaItem,
                                    'nombre_archivo_original' => $file->getName(),
                                    'ruta_archivo' => $result['path'],
                                    'tipo_mime' => $result['mime'],
                                    'tamanio_bytes' => $result['size'],
                                    'hash_archivo' => hash_file('sha256', WRITEPATH . $result['path']),
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);

                                $archivosSubidos[] = $file->getName();

                                $this->auditoriaLogModel->registrarEvidenciaGlobalSubida(
                                    $idAuditoria,
                                    $idAuditoriaItem,
                                    $file->getName(),
                                    $result['size']
                                );
                            }
                        }
                    }
                }
            } else {
                // Guardar ítem por cliente
                if (!$idCliente) {
                    return $this->response->setJSON([
                        'ok' => false,
                        'message' => 'Debe especificar el cliente',
                        'csrf_token' => csrf_token(),
                        'csrf_hash' => csrf_hash()
                    ]);
                }

                $comentario = $this->request->getPost('comentario_proveedor_cliente');

                // Si no hay comentario pero hay archivos, poner "Sin observaciones"
                $filesCliente = $this->request->getFileMultiple('evidencias_cliente');
                $hayArchivosCliente = $filesCliente && count(array_filter($filesCliente, fn($f) => $f->isValid())) > 0;
                if (empty(trim($comentario ?? '')) && $hayArchivosCliente) {
                    $comentario = 'Sin observaciones';
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
                        'comentario_proveedor_cliente' => $comentario,
                        'created_at' => date('Y-m-d H:i:s'),
                    ], false);
                    $idAuditoriaItemCliente = $this->auditoriaItemClienteModel->getInsertID();
                } else {
                    $this->auditoriaItemClienteModel->update($itemCliente['id_auditoria_item_cliente'], [
                        'comentario_proveedor_cliente' => $comentario,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $idAuditoriaItemCliente = $itemCliente['id_auditoria_item_cliente'];
                }

                // Registrar en bitácora
                if (!empty($comentario)) {
                    $cliente = $this->auditoriaClienteModel
                        ->select('clientes.razon_social')
                        ->join('clientes', 'clientes.id_cliente = auditoria_clientes.id_cliente')
                        ->where('auditoria_clientes.id_cliente', $idCliente)
                        ->first();

                    $this->auditoriaLogModel->registrarComentarioCliente(
                        $idAuditoria,
                        $idAuditoriaItem,
                        $idCliente,
                        $itemBanco['titulo'] ?? 'Ítem sin título',
                        $cliente['razon_social'] ?? 'Cliente'
                    );
                }

                // Procesar evidencias por cliente
                $files = $this->request->getFileMultiple('evidencias_cliente');
                if ($files && $idAuditoriaItemCliente) {
                    $proveedor = $this->proveedorModel->find($auditoria['id_proveedor']);

                    foreach ($files as $file) {
                        if ($file->isValid() && !$file->hasMoved()) {
                            $validation = $this->validateFile($file);
                            if (!$validation['ok']) {
                                return $this->response->setJSON([
                                    'ok' => false,
                                    'message' => $validation['error'],
                                    'csrf_token' => csrf_token(),
                                    'csrf_hash' => csrf_hash()
                                ]);
                            }

                            $result = $this->uploadService->saveEvidenciaCliente([
                                'name' => $file->getName(),
                                'type' => $file->getClientMimeType(),
                                'tmp_name' => $file->getTempName(),
                                'error' => $file->getError(),
                                'size' => $file->getSize(),
                            ], $proveedor['nit'], $idAuditoria, $idAuditoriaItem, $idCliente, [
                                'user_id' => userId(),
                                'id_auditoria' => $idAuditoria,
                                'id_item' => $item['id_item'],
                                'id_auditoria_item' => $idAuditoriaItem,
                                'id_cliente' => $idCliente,
                            ]);

                            if ($result['ok']) {
                                $this->evidenciaClienteModel->insert([
                                    'id_auditoria_item_cliente' => $idAuditoriaItemCliente,
                                    'nombre_archivo_original' => $file->getName(),
                                    'ruta_archivo' => $result['path'],
                                    'tipo_mime' => $result['mime'],
                                    'tamanio_bytes' => $result['size'],
                                    'hash_archivo' => hash_file('sha256', WRITEPATH . $result['path']),
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);

                                $archivosSubidos[] = $file->getName();

                                $clienteInfo = $this->auditoriaClienteModel
                                    ->select('clientes.razon_social')
                                    ->join('clientes', 'clientes.id_cliente = auditoria_clientes.id_cliente')
                                    ->where('auditoria_clientes.id_cliente', $idCliente)
                                    ->first();

                                $this->auditoriaLogModel->registrarEvidenciaClienteSubida(
                                    $idAuditoria,
                                    $idAuditoriaItem,
                                    $idCliente,
                                    $file->getName(),
                                    $result['size'],
                                    $clienteInfo['razon_social'] ?? 'Cliente'
                                );
                            }
                        }
                    }
                }
            }

            // Calcular progreso actualizado
            $progreso = $this->calcularProgreso($idAuditoria);

            $mensaje = '✅ Ítem guardado exitosamente';
            if (!empty($archivosSubidos)) {
                $mensaje .= '. Archivos subidos: ' . count($archivosSubidos);
            }

            // Obtener lista actualizada de evidencias para devolver al frontend
            $evidenciasActualizadas = [];
            if ($tipo === 'global') {
                $evidenciasActualizadas = $this->evidenciaModel
                    ->where('id_auditoria_item', $idAuditoriaItem)
                    ->findAll();
            } else if ($idCliente) {
                // Buscar el id_auditoria_item_cliente
                $itemClienteActual = $this->auditoriaItemClienteModel
                    ->where('id_auditoria_item', $idAuditoriaItem)
                    ->where('id_cliente', $idCliente)
                    ->first();
                if ($itemClienteActual) {
                    $evidenciasActualizadas = $this->evidenciaClienteModel
                        ->where('id_auditoria_item_cliente', $itemClienteActual['id_auditoria_item_cliente'])
                        ->findAll();
                }
            }

            return $this->response->setJSON([
                'ok' => true,
                'message' => $mensaje,
                'timestamp' => date('H:i:s'),
                'progreso' => $progreso,
                'archivos_subidos' => $archivosSubidos,
                'evidencias' => $evidenciasActualizadas,
                'id_auditoria' => $idAuditoria,
                'id_item' => $idAuditoriaItem,
                'id_cliente' => $idCliente,
                'tipo' => $tipo,
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'guardarItemAjax error: ' . $e->getMessage());
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Error al guardar: ' . $e->getMessage(),
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        }
    }

    /**
     * Guarda respuesta de un ítem (detecta automáticamente si es global o por cliente)
     */
    public function guardarItem(int $idAuditoria, int $idAuditoriaItem)
    {
        $item = $this->auditoriaItemModel->find($idAuditoriaItem);

        if (!$item || $item['id_auditoria'] != $idAuditoria) {
            return redirect()->back()->with('error', 'Ítem no válido');
        }

        // Obtener alcance del ítem
        $itemBanco = $this->itemsBancoModel->find($item['id_item']);
        $idCliente = $this->request->getPost('id_cliente');

        // Validar según alcance
        if ($itemBanco['alcance'] === 'por_cliente' && !$idCliente) {
            return redirect()->back()->with('error', 'Debe especificar el cliente para este ítem');
        }

        if ($itemBanco['alcance'] === 'global') {
            return $this->guardarItemGlobal($idAuditoriaItem);
        } else {
            return $this->guardarItemPorCliente($idAuditoriaItem, $idCliente);
        }
    }

    /**
     * Guarda ítem global (alcance='global')
     */
    private function guardarItemGlobal(int $idAuditoriaItem)
    {
        $item = $this->auditoriaItemModel->find($idAuditoriaItem);

        // Actualizar comentario en auditoria_items
        $comentario = $this->request->getPost('comentario_proveedor');
        $this->auditoriaItemModel->update($idAuditoriaItem, [
            'comentario_proveedor' => $comentario,
        ]);

        // Registrar en bitácora
        if (!empty($comentario)) {
            $itemBanco = $this->itemsBancoModel->find($item['id_item']);
            $this->auditoriaLogModel->registrarComentarioGlobal(
                $item['id_auditoria'],
                $idAuditoriaItem,
                $itemBanco['titulo'] ?? 'Ítem sin título'
            );
        }

        // Procesar evidencias globales
        $files = $this->request->getFileMultiple('evidencias');

        // Obtener proveedor desde la auditoría
        $auditoria = $this->auditoriaModel->find($item['id_auditoria']);
        $proveedor = $this->proveedorModel->find($auditoria['id_proveedor']);

        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    // Validar tipo y tamaño
                    $validation = $this->validateFile($file);
                    if (!$validation['ok']) {
                        return redirect()->back()->with('error', $validation['error']);
                    }

                    $result = $this->uploadService->saveEvidencia([
                        'name' => $file->getName(),
                        'type' => $file->getClientMimeType(),
                        'tmp_name' => $file->getTempName(),
                        'error' => $file->getError(),
                        'size' => $file->getSize(),
                    ], $proveedor['nit'], $item['id_auditoria'], $idAuditoriaItem, [
                        'user_id' => userId(),
                        'id_auditoria' => $item['id_auditoria'],
                        'id_item' => $item['id_item'],
                        'id_auditoria_item' => $idAuditoriaItem,
                    ]);

                    if ($result['ok']) {
                        $this->evidenciaModel->insert([
                            'id_auditoria_item' => $idAuditoriaItem,
                            'nombre_archivo_original' => $file->getName(),
                            'ruta_archivo' => $result['path'],
                            'tipo_mime' => $result['mime'],
                            'tamanio_bytes' => $result['size'],
                            'hash_archivo' => hash_file('sha256', WRITEPATH . $result['path']),
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);

                        // Registrar en bitácora
                        $this->auditoriaLogModel->registrarEvidenciaGlobalSubida(
                            $item['id_auditoria'],
                            $idAuditoriaItem,
                            $file->getName(),
                            $result['size']
                        );
                    } else {
                        return redirect()->back()->with('error', 'Error al subir archivo: ' . ($result['error'] ?? 'Desconocido'));
                    }
                }
            }
        }

        log_message('info', 'Item global guardado exitosamente - ID: ' . $idAuditoriaItem);
        return redirect()->to('proveedor/auditoria/' . $item['id_auditoria'])->with('success', '✅ Ítem global guardado exitosamente');
    }

    /**
     * Guarda ítem por cliente (alcance='por_cliente' o 'mixto')
     */
    private function guardarItemPorCliente(int $idAuditoriaItem, int $idCliente)
    {
        log_message('info', 'Guardando item por cliente - ID Item: ' . $idAuditoriaItem . ', ID Cliente: ' . $idCliente);

        $item = $this->auditoriaItemModel->find($idAuditoriaItem);
        $comentario = $this->request->getPost('comentario_proveedor_cliente');

        log_message('info', 'Comentario recibido: ' . ($comentario ? 'SI (' . strlen($comentario) . ' chars)' : 'NO'));

        // Buscar o crear registro en auditoria_item_cliente
        $itemCliente = $this->auditoriaItemClienteModel
            ->where('id_auditoria_item', $idAuditoriaItem)
            ->where('id_cliente', $idCliente)
            ->first();

        if (!$itemCliente) {
            // Crear registro (sin validación porque el proveedor no envía calificacion_ajustada)
            $insertResult = $this->auditoriaItemClienteModel->insert([
                'id_auditoria_item' => $idAuditoriaItem,
                'id_cliente' => $idCliente,
                'comentario_proveedor_cliente' => $comentario,
                'created_at' => date('Y-m-d H:i:s'),
            ], false); // false = skip validation

            if (!$insertResult) {
                $errors = $this->auditoriaItemClienteModel->errors();
                log_message('error', 'Error al insertar auditoria_item_cliente: ' . json_encode($errors));
                return redirect()->back()->with('error', 'Error al guardar el comentario del cliente: ' . json_encode($errors));
            }

            $idAuditoriaItemCliente = $this->auditoriaItemClienteModel->getInsertID();
            log_message('info', 'Registro auditoria_item_cliente creado con ID: ' . $idAuditoriaItemCliente);

            if (!$idAuditoriaItemCliente || $idAuditoriaItemCliente == 0) {
                log_message('error', 'getInsertID() retornó 0 o NULL');
                return redirect()->back()->with('error', 'Error al obtener el ID del registro creado');
            }
        } else {
            // Actualizar comentario
            $this->auditoriaItemClienteModel->update($itemCliente['id_auditoria_item_cliente'], [
                'comentario_proveedor_cliente' => $comentario,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $idAuditoriaItemCliente = $itemCliente['id_auditoria_item_cliente'];
            log_message('info', 'Registro auditoria_item_cliente actualizado, ID: ' . $idAuditoriaItemCliente);
        }

        // Registrar en bitácora
        if (!empty($comentario)) {
            $itemBanco = $this->itemsBancoModel->find($item['id_item']);
            $cliente = $this->auditoriaClienteModel
                ->select('clientes.razon_social')
                ->join('clientes', 'clientes.id_cliente = auditoria_clientes.id_cliente')
                ->where('auditoria_clientes.id_cliente', $idCliente)
                ->first();

            $this->auditoriaLogModel->registrarComentarioCliente(
                $item['id_auditoria'],
                $idAuditoriaItem,
                $idCliente,
                $itemBanco['titulo'] ?? 'Ítem sin título',
                $cliente['razon_social'] ?? 'Cliente'
            );
        }

        // Procesar evidencias por cliente
        $files = $this->request->getFileMultiple('evidencias_cliente');

        // Obtener proveedor desde la auditoría
        $auditoria = $this->auditoriaModel->find($item['id_auditoria']);
        $proveedor = $this->proveedorModel->find($auditoria['id_proveedor']);

        if ($files) {
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    // Validar tipo y tamaño
                    $validation = $this->validateFile($file);
                    if (!$validation['ok']) {
                        return redirect()->back()->with('error', $validation['error']);
                    }

                    $result = $this->uploadService->saveEvidenciaCliente([
                        'name' => $file->getName(),
                        'type' => $file->getClientMimeType(),
                        'tmp_name' => $file->getTempName(),
                        'error' => $file->getError(),
                        'size' => $file->getSize(),
                    ], $proveedor['nit'], $item['id_auditoria'], $idAuditoriaItem, $idCliente, [
                        'user_id' => userId(),
                        'id_auditoria' => $item['id_auditoria'],
                        'id_item' => $item['id_item'],
                        'id_auditoria_item' => $idAuditoriaItem,
                        'id_cliente' => $idCliente,
                    ]);

                    if ($result['ok']) {
                        $this->evidenciaClienteModel->insert([
                            'id_auditoria_item_cliente' => $idAuditoriaItemCliente,
                            'nombre_archivo_original' => $file->getName(),
                            'ruta_archivo' => $result['path'],
                            'tipo_mime' => $result['mime'],
                            'tamanio_bytes' => $result['size'],
                            'hash_archivo' => hash_file('sha256', WRITEPATH . $result['path']),
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);

                        // Registrar en bitácora
                        $cliente = $this->auditoriaClienteModel
                            ->select('clientes.razon_social')
                            ->join('clientes', 'clientes.id_cliente = auditoria_clientes.id_cliente')
                            ->where('auditoria_clientes.id_cliente', $idCliente)
                            ->first();

                        $this->auditoriaLogModel->registrarEvidenciaClienteSubida(
                            $item['id_auditoria'],
                            $idAuditoriaItem,
                            $idCliente,
                            $file->getName(),
                            $result['size'],
                            $cliente['razon_social'] ?? 'Cliente'
                        );
                    } else {
                        return redirect()->back()->with('error', 'Error al subir archivo: ' . ($result['error'] ?? 'Desconocido'));
                    }
                }
            }
        }

        log_message('info', 'Item por cliente guardado - ID Item: ' . $idAuditoriaItem . ', ID Cliente: ' . $idCliente);
        return redirect()->to('proveedor/auditoria/' . $item['id_auditoria'])->with('success', '✅ Ítem del cliente guardado exitosamente');
    }

    /**
     * Valida archivo (tipo y tamaño)
     */
    private function validateFile($file): array
    {
        // Tipos permitidos
        $allowedMimes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $mime = $file->getClientMimeType();
        if (!in_array($mime, $allowedMimes)) {
            return [
                'ok' => false,
                'error' => 'Tipo de archivo no permitido. Solo se permiten imágenes, PDF, Word y Excel.'
            ];
        }

        // Tamaño máximo: 10MB
        $maxSize = 10 * 1024 * 1024; // 10MB en bytes
        if ($file->getSize() > $maxSize) {
            return [
                'ok' => false,
                'error' => 'El archivo es demasiado grande. Tamaño máximo: 10MB'
            ];
        }

        return ['ok' => true];
    }

    /**
     * Finaliza auditoría y notifica al consultor
     */
    public function finalizar(int $idAuditoria)
    {
        $db = \Config\Database::connect();

        // Verificar que la auditoría pertenece a este usuario responsable
        $auditoria = $db->query("
            SELECT a.*
            FROM auditorias a
            JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            WHERE a.id_auditoria = ?
              AND cpc.id_usuario_responsable = ?
              AND cpc.estado = 'activo'
        ", [$idAuditoria, userId()])->getRowArray();

        if (!$auditoria) {
            return redirect()->back()->with('error', 'Auditoría no válida o no tiene acceso');
        }

        // Cambiar estado
        $this->auditoriaModel->update($idAuditoria, [
            'estado' => 'en_revision_consultor',
            'fecha_envio_consultor' => date('Y-m-d H:i:s'),
        ]);

        // Enviar email al consultor
        $this->notifyConsultorProveedorFinalizo($idAuditoria);

        return redirect()->to('/proveedor/auditorias')->with('success', 'Auditoría enviada a revisión');
    }

    /**
     * Elimina una evidencia global antes de finalizar
     */
    public function deleteEvidencia(int $idAuditoria, int $idEvidencia)
    {
        $isAjax = $this->request->isAJAX();

        $evidencia = $this->evidenciaModel->find($idEvidencia);

        if (!$evidencia) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Evidencia no encontrada',
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            }
            return redirect()->back()->with('error', 'Evidencia no encontrada');
        }

        $auditoriaItem = $this->auditoriaItemModel->find($evidencia['id_auditoria_item']);

        if (!$auditoriaItem || $auditoriaItem['id_auditoria'] != $idAuditoria) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Evidencia no válida',
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            }
            return redirect()->back()->with('error', 'Evidencia no válida');
        }

        // Eliminar archivo físico
        $filePath = WRITEPATH . $evidencia['ruta_archivo'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // Eliminar registro
        $this->evidenciaModel->delete($idEvidencia);

        if ($isAjax) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Evidencia eliminada',
                'id_item' => $auditoriaItem['id_auditoria_item'],
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        }

        return redirect()->back()->with('success', 'Evidencia eliminada');
    }

    /**
     * Elimina evidencia de cliente
     */
    public function deleteEvidenciaCliente(int $idAuditoria, int $idEvidenciaCliente)
    {
        $isAjax = $this->request->isAJAX();

        $evidencia = $this->evidenciaClienteModel->find($idEvidenciaCliente);

        if (!$evidencia) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Evidencia no encontrada',
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            }
            return redirect()->back()->with('error', 'Evidencia no encontrada');
        }

        // Verificar que pertenece a esta auditoría
        $itemCliente = $this->auditoriaItemClienteModel->find($evidencia['id_auditoria_item_cliente']);
        if (!$itemCliente) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Evidencia no válida',
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            }
            return redirect()->back()->with('error', 'Evidencia no válida');
        }

        $auditoriaItem = $this->auditoriaItemModel->find($itemCliente['id_auditoria_item']);
        if (!$auditoriaItem || $auditoriaItem['id_auditoria'] != $idAuditoria) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Evidencia no válida',
                    'csrf_token' => csrf_token(),
                    'csrf_hash' => csrf_hash()
                ]);
            }
            return redirect()->back()->with('error', 'Evidencia no válida');
        }

        // Eliminar archivo físico
        $filePath = WRITEPATH . $evidencia['ruta_archivo'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        // Eliminar registro
        $this->evidenciaClienteModel->delete($idEvidenciaCliente);

        if ($isAjax) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Evidencia del cliente eliminada',
                'id_item' => $auditoriaItem['id_auditoria_item'],
                'id_cliente' => $itemCliente['id_cliente'],
                'csrf_token' => csrf_token(),
                'csrf_hash' => csrf_hash()
            ]);
        }

        return redirect()->back()->with('success', 'Evidencia del cliente eliminada');
    }

    /**
     * Calcula progreso considerando ítems globales y por cliente
     * Retorna array con datos detallados de progreso
     */
    private function calcularProgreso(int $idAuditoria): array
    {
        $db = \Config\Database::connect();

        // === ÍTEMS GLOBALES ===
        // Total de ítems globales
        $queryGlobalesTotal = $db->query("
            SELECT COUNT(*) as total
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance IN ('global', 'mixto')
        ", [$idAuditoria]);

        $globalesTotal = $queryGlobalesTotal->getRow()->total ?? 0;

        // Ítems globales completados (con comentario O con al menos una evidencia)
        $queryGlobalesCompletos = $db->query("
            SELECT COUNT(DISTINCT ai.id_auditoria_item) as completados
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            LEFT JOIN evidencias e ON e.id_auditoria_item = ai.id_auditoria_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance IN ('global', 'mixto')
              AND (
                  (ai.comentario_proveedor IS NOT NULL AND ai.comentario_proveedor != '')
                  OR e.id_evidencia IS NOT NULL
              )
        ", [$idAuditoria]);

        $globalesCompletos = $queryGlobalesCompletos->getRow()->completados ?? 0;

        // === ÍTEMS POR CLIENTE ===
        // Contar clientes asignados
        $queryClientes = $db->query("
            SELECT COUNT(*) as total_clientes
            FROM auditoria_clientes
            WHERE id_auditoria = ?
        ", [$idAuditoria]);

        $totalClientes = $queryClientes->getRow()->total_clientes ?? 0;

        // Total de ítems por cliente (ítems × clientes)
        $queryPorClienteTotal = $db->query("
            SELECT COUNT(*) as total_items
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance = 'por_cliente'
        ", [$idAuditoria]);

        $itemsPorClienteCount = $queryPorClienteTotal->getRow()->total_items ?? 0;
        $porClienteTotal = $itemsPorClienteCount * $totalClientes;

        // Ítems por cliente completados (con comentario O con al menos una evidencia)
        $queryPorClienteCompletos = $db->query("
            SELECT COUNT(DISTINCT aic.id_auditoria_item_cliente) as completados
            FROM auditoria_item_cliente aic
            JOIN auditoria_items ai ON ai.id_auditoria_item = aic.id_auditoria_item
            JOIN items_banco ib ON ib.id_item = ai.id_item
            LEFT JOIN evidencias_cliente ec ON ec.id_auditoria_item_cliente = aic.id_auditoria_item_cliente
            WHERE ai.id_auditoria = ?
              AND ib.alcance = 'por_cliente'
              AND (
                  (aic.comentario_proveedor_cliente IS NOT NULL AND aic.comentario_proveedor_cliente != '')
                  OR ec.id_evidencia_cliente IS NOT NULL
              )
        ", [$idAuditoria]);

        $porClienteCompletos = $queryPorClienteCompletos->getRow()->completados ?? 0;

        // === TOTALES ===
        $total = $globalesTotal + $porClienteTotal;
        $completados = $globalesCompletos + $porClienteCompletos;

        $porcentaje = $total > 0 ? round(($completados / $total) * 100, 2) : 0;

        return [
            'porcentaje_total' => $porcentaje,
            'globales_completos' => $globalesCompletos,
            'globales_total' => $globalesTotal,
            'por_cliente_completos' => $porClienteCompletos,
            'por_cliente_total' => $porClienteTotal,
            'total_completados' => $completados,
            'total' => $total,
        ];
    }

    /**
     * Obtiene items con alcance, evidencias y estado de completitud
     */
    private function getItemsConAlcance(int $idAuditoria): array
    {
        $items = $this->auditoriaItemModel
            ->select('auditoria_items.*, items_banco.codigo_item, items_banco.titulo, items_banco.descripcion, items_banco.alcance')
            ->join('items_banco', 'items_banco.id_item = auditoria_items.id_item')
            ->where('id_auditoria', $idAuditoria)
            ->orderBy('items_banco.orden', 'ASC')
            ->findAll();

        // Cargar evidencias y datos por cliente
        foreach ($items as &$item) {
            if ($item['alcance'] === 'global' || $item['alcance'] === 'mixto') {
                // Evidencias globales
                $item['evidencias'] = $this->evidenciaModel
                    ->where('id_auditoria_item', $item['id_auditoria_item'])
                    ->findAll();

                // Marcar como completo si tiene comentario
                $item['completo'] = !empty($item['comentario_proveedor']);

            } else { // por_cliente
                // Items por cliente
                $item['items_cliente'] = $this->auditoriaItemClienteModel
                    ->select('auditoria_item_cliente.*, clientes.razon_social, clientes.nit')
                    ->join('clientes', 'clientes.id_cliente = auditoria_item_cliente.id_cliente')
                    ->where('id_auditoria_item', $item['id_auditoria_item'])
                    ->findAll();

                // Cargar evidencias y marcar completitud por cada item-cliente
                $todosClientesCompletos = true;
                foreach ($item['items_cliente'] as &$itemCliente) {
                    $itemCliente['evidencias'] = $this->evidenciaClienteModel
                        ->where('id_auditoria_item_cliente', $itemCliente['id_auditoria_item_cliente'])
                        ->findAll();

                    // Marcar cliente como completo si tiene comentario
                    $itemCliente['completo'] = !empty($itemCliente['comentario_proveedor_cliente']);

                    if (!$itemCliente['completo']) {
                        $todosClientesCompletos = false;
                    }
                }

                // El ítem está completo solo si TODOS sus clientes están completos
                $item['completo'] = $todosClientesCompletos && !empty($item['items_cliente']);
            }
        }

        return $items;
    }

    private function notifyConsultorProveedorFinalizo(int $idAuditoria)
    {
        try {
            $result = $this->emailService->notifyConsultorProveedorFinalizo($idAuditoria);

            if ($result['ok']) {
                log_message('info', "Notificación enviada a consultor para auditoría {$idAuditoria}");
            } else {
                log_message('error', "Error al enviar notificación: " . ($result['error'] ?? 'Desconocido'));
            }
        } catch (\Exception $e) {
            log_message('error', "Excepción al notificar consultor: " . $e->getMessage());
        }
    }

    /**
     * Muestra auditorías completadas o cerradas del proveedor
     */
    public function completadas()
    {
        $db = \Config\Database::connect();
        $anio = $this->request->getGet('anio') ?? date('Y');
        $filtroAnio = ($anio !== 'todos') ? "AND YEAR(a.created_at) = " . (int)$anio : "";

        // Buscar auditorías cerradas asignadas a este usuario responsable
        // Incluye auditorías donde el usuario responsable está en cualquier contrato del proveedor
        $auditorias = $db->query("
            SELECT DISTINCT a.*,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            WHERE a.estado = 'cerrada'
              {$filtroAnio}
              AND EXISTS (
                  SELECT 1
                  FROM contratos_proveedor_cliente cpc
                  WHERE cpc.id_proveedor = a.id_proveedor
                    AND cpc.id_usuario_responsable = ?
              )
            ORDER BY a.updated_at DESC
        ", [userId()])->getResultArray();

        return view('proveedor/auditorias/completadas', [
            'title' => 'Auditorías Completadas',
            'auditorias' => $auditorias,
            'anio' => $anio,
        ]);
    }

    /**
     * Muestra todas las evidencias subidas por el proveedor
     */
    public function evidencias()
    {
        $db = \Config\Database::connect();

        // Buscar evidencias globales
        $evidenciasGlobales = $db->query("
            SELECT e.*,
                   ai.id_auditoria,
                   a.codigo_formato,
                   ib.codigo_item,
                   ib.titulo as item_titulo
            FROM evidencias e
            JOIN auditoria_items ai ON ai.id_auditoria_item = e.id_auditoria_item
            JOIN auditorias a ON a.id_auditoria = ai.id_auditoria
            JOIN items_banco ib ON ib.id_item = ai.id_item
            WHERE EXISTS (
                SELECT 1
                FROM contratos_proveedor_cliente cpc
                WHERE cpc.id_proveedor = a.id_proveedor
                  AND cpc.id_usuario_responsable = ?
            )
            ORDER BY e.created_at DESC
        ", [userId()])->getResultArray();

        // Buscar evidencias por cliente
        $evidenciasCliente = $db->query("
            SELECT ec.*,
                   ai.id_auditoria,
                   a.codigo_formato,
                   ib.codigo_item,
                   ib.titulo as item_titulo,
                   cl.razon_social as cliente_nombre
            FROM evidencias_cliente ec
            JOIN auditoria_item_cliente aic ON aic.id_auditoria_item_cliente = ec.id_auditoria_item_cliente
            JOIN auditoria_items ai ON ai.id_auditoria_item = aic.id_auditoria_item
            JOIN auditorias a ON a.id_auditoria = ai.id_auditoria
            JOIN items_banco ib ON ib.id_item = ai.id_item
            JOIN clientes cl ON cl.id_cliente = aic.id_cliente
            WHERE EXISTS (
                SELECT 1
                FROM contratos_proveedor_cliente cpc
                WHERE cpc.id_proveedor = a.id_proveedor
                  AND cpc.id_usuario_responsable = ?
            )
            ORDER BY ec.created_at DESC
        ", [userId()])->getResultArray();

        return view('proveedor/auditorias/evidencias', [
            'title' => 'Mis Evidencias',
            'evidencias_globales' => $evidenciasGlobales,
            'evidencias_cliente' => $evidenciasCliente,
        ]);
    }

    /**
     * Muestra información de la empresa (proveedor)
     */
    public function empresa()
    {
        $db = \Config\Database::connect();

        // Buscar todos los proveedores asociados a este usuario responsable
        $proveedores = $db->query("
            SELECT DISTINCT p.*
            FROM proveedores p
            WHERE EXISTS (
                SELECT 1
                FROM contratos_proveedor_cliente cpc
                WHERE cpc.id_proveedor = p.id_proveedor
                  AND cpc.id_usuario_responsable = ?
            )
        ", [userId()])->getResultArray();

        // Si no hay proveedores, redirigir al dashboard
        if (empty($proveedores)) {
            return redirect()->to('proveedor/dashboard')->with('error', 'No hay información de empresa disponible');
        }

        // Buscar clientes asociados
        $clientes = $db->query("
            SELECT DISTINCT cl.*,
                   p.razon_social as proveedor_nombre
            FROM clientes cl
            JOIN contratos_proveedor_cliente cpc ON cpc.id_cliente = cl.id_cliente
            JOIN proveedores p ON p.id_proveedor = cpc.id_proveedor
            WHERE cpc.id_usuario_responsable = ?
            ORDER BY cl.razon_social ASC
        ", [userId()])->getResultArray();

        return view('proveedor/auditorias/empresa', [
            'title' => 'Mi Empresa',
            'proveedores' => $proveedores,
            'clientes' => $clientes,
        ]);
    }

    /**
     * Ver/Descargar evidencia global
     */
    public function verEvidencia(int $idEvidencia)
    {
        $evidenciaModel = new \App\Models\EvidenciaModel();
        $evidencia = $evidenciaModel->find($idEvidencia);

        if (!$evidencia) {
            return redirect()->back()->with('error', 'Evidencia no encontrada');
        }

        // Verificar que la evidencia pertenece a una auditoría del proveedor actual
        $auditoriaItemModel = new \App\Models\AuditoriaItemModel();
        $auditoriaItem = $auditoriaItemModel->find($evidencia['id_auditoria_item']);

        if (!$auditoriaItem) {
            return redirect()->back()->with('error', 'Ítem no encontrado');
        }

        $auditoria = $this->auditoriaModel->find($auditoriaItem['id_auditoria']);

        // Verificar que el usuario actual es responsable de este proveedor
        $db = \Config\Database::connect();
        $tieneAcceso = $db->table('contratos_proveedor_cliente cpc')
            ->join('auditorias a', 'a.id_proveedor = cpc.id_proveedor')
            ->where('a.id_auditoria', $auditoria['id_auditoria'])
            ->where('cpc.id_usuario_responsable', userId())
            ->countAllResults() > 0;

        if (!$tieneAcceso) {
            return redirect()->back()->with('error', 'No tiene permisos para ver esta evidencia');
        }

        // Construir ruta del archivo
        $filePath = WRITEPATH . $evidencia['ruta_archivo'];

        if (!file_exists($filePath)) {
            log_message('error', "Archivo de evidencia no encontrado: {$filePath}");
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
        $evidenciaClienteModel = new \App\Models\EvidenciaClienteModel();
        $evidencia = $evidenciaClienteModel->find($idEvidenciaCliente);

        if (!$evidencia) {
            return redirect()->back()->with('error', 'Evidencia no encontrada');
        }

        // Verificar que la evidencia pertenece a una auditoría del proveedor actual
        $auditoriaItemClienteModel = new \App\Models\AuditoriaItemClienteModel();
        $auditoriaItemCliente = $auditoriaItemClienteModel->find($evidencia['id_auditoria_item_cliente']);

        if (!$auditoriaItemCliente) {
            return redirect()->back()->with('error', 'Ítem de cliente no encontrado');
        }

        $auditoriaItemModel = new \App\Models\AuditoriaItemModel();
        $auditoriaItem = $auditoriaItemModel->find($auditoriaItemCliente['id_auditoria_item']);

        if (!$auditoriaItem) {
            return redirect()->back()->with('error', 'Ítem no encontrado');
        }

        $auditoria = $this->auditoriaModel->find($auditoriaItem['id_auditoria']);

        // Verificar que el usuario actual es responsable de este proveedor
        $db = \Config\Database::connect();
        $tieneAcceso = $db->table('contratos_proveedor_cliente cpc')
            ->join('auditorias a', 'a.id_proveedor = cpc.id_proveedor')
            ->where('a.id_auditoria', $auditoria['id_auditoria'])
            ->where('cpc.id_usuario_responsable', userId())
            ->countAllResults() > 0;

        if (!$tieneAcceso) {
            return redirect()->back()->with('error', 'No tiene permisos para ver esta evidencia');
        }

        // Construir ruta del archivo
        $filePath = WRITEPATH . $evidencia['ruta_archivo'];

        if (!file_exists($filePath)) {
            log_message('error', "Archivo de evidencia no encontrado: {$filePath}");
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
}
