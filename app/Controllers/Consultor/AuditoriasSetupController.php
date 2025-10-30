<?php

namespace App\Controllers\Consultor;

use App\Controllers\BaseController;
use App\Models\AuditoriaModel;
use App\Models\AuditoriaItemModel;
use App\Models\AuditoriaItemClienteModel;
use App\Models\AuditoriaClienteModel;
use App\Models\ProveedorModel;
use App\Models\ItemsBancoModel;
use App\Models\ContratoModel;
use App\Models\UserModel;
use App\Services\EmailService;

class AuditoriasSetupController extends BaseController
{
    protected AuditoriaModel $auditoriaModel;
    protected AuditoriaItemModel $auditoriaItemModel;
    protected AuditoriaItemClienteModel $auditoriaItemClienteModel;
    protected AuditoriaClienteModel $auditoriaClienteModel;
    protected ProveedorModel $proveedorModel;
    protected ItemsBancoModel $itemsBancoModel;
    protected ContratoModel $contratoModel;
    protected UserModel $userModel;
    protected EmailService $emailService;

    public function __construct()
    {
        $this->auditoriaModel = new AuditoriaModel();
        $this->auditoriaItemModel = new AuditoriaItemModel();
        $this->auditoriaItemClienteModel = model('App\Models\AuditoriaItemClienteModel');
        $this->auditoriaClienteModel = model('App\Models\AuditoriaClienteModel');
        $this->proveedorModel = new ProveedorModel();
        $this->itemsBancoModel = new ItemsBancoModel();
        $this->contratoModel = new ContratoModel();
        $this->userModel = new UserModel();
        $this->emailService = new EmailService();
        helper(['auth']);
    }

    /**
     * PASO 1: Formulario para crear nueva auditoría (información básica)
     */
    public function crear()
    {
        // Obtener ID del consultor actual
        $userId = session()->get('id_users');
        $db = \Config\Database::connect();
        $consultor = $db->table('consultores')
            ->where('id_users', $userId)
            ->get()
            ->getRowArray();

        if (!$consultor) {
            return redirect()
                ->to('/consultor/dashboard')
                ->with('error', 'No se encontró el perfil de consultor.');
        }

        $data = [
            'title' => 'Crear Nueva Auditoría - Paso 1',
            'proveedores' => $this->proveedorModel->getProveedoresActivos(),
            'consultor' => $consultor,
        ];

        return view('consultor/auditorias/crear', $data);
    }

    /**
     * PASO 1: Guarda la auditoría en estado 'borrador'
     */
    public function guardar()
    {
        // Obtener ID del consultor actual
        $userId = session()->get('id_users');
        $db = \Config\Database::connect();
        $consultor = $db->table('consultores')
            ->where('id_users', $userId)
            ->get()
            ->getRowArray();

        if (!$consultor) {
            return redirect()
                ->back()
                ->with('error', 'No se encontró el perfil de consultor.');
        }

        // Validar datos
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_proveedor' => 'required|integer',
            'codigo_formato' => 'permit_empty|max_length[50]',
            'version_formato' => 'permit_empty|max_length[20]',
            'fecha_programada' => 'permit_empty|valid_date',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        // Crear auditoría en estado BORRADOR
        $dataAuditoria = [
            'id_proveedor' => $this->request->getPost('id_proveedor'),
            'id_consultor' => $consultor['id_consultor'],
            'codigo_formato' => $this->request->getPost('codigo_formato'),
            'version_formato' => $this->request->getPost('version_formato'),
            'fecha_programada' => $this->request->getPost('fecha_programada') ?: null,
            'estado' => 'borrador',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if (!$this->auditoriaModel->save($dataAuditoria)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->auditoriaModel->errors());
        }

        // Obtener ID de la auditoría creada
        $idAuditoria = $this->auditoriaModel->getInsertID();

        // Redirigir al PASO 2: Seleccionar ítems
        return redirect()
            ->to('/consultor/auditorias/' . $idAuditoria . '/seleccionar-items')
            ->with('success', 'Auditoría creada. Ahora seleccione los ítems que aplicarán a esta auditoría.');
    }

    /**
     * PASO 2: Pantalla para asignar clientes a la auditoría
     */
    public function asignarClientesSetup(int $idAuditoria)
    {
        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria) {
            return redirect()
                ->to('/consultor/auditorias')
                ->with('error', 'Auditoría no encontrada.');
        }

        // Verificar que está en estado borrador
        if ($auditoria['estado'] !== 'borrador') {
            return redirect()
                ->to('/consultor/auditorias')
                ->with('error', 'Esta auditoría ya no está en estado borrador.');
        }

        // Obtener proveedor
        $proveedor = $this->proveedorModel->find($auditoria['id_proveedor']);

        // Obtener clientes disponibles del proveedor (contratos activos)
        $clientes = $this->contratoModel->getClientesByProveedor($auditoria['id_proveedor']);

        // Obtener clientes ya asignados
        $asignados = $this->auditoriaClienteModel->getClientesByAuditoria($idAuditoria);
        $idsAsignados = array_column($asignados, 'id_cliente');

        $data = [
            'title' => 'Asignar Clientes - Paso 2',
            'auditoria' => $auditoria,
            'proveedor' => $proveedor,
            'clientes' => $clientes,
            'asignados' => $idsAsignados,
        ];

        return view('consultor/auditorias/asignar_clientes_setup', $data);
    }

    /**
     * PASO 2: Guarda los clientes asignados a la auditoría
     * Ahora incluye snapshot de id_contrato y id_servicio
     */
    public function guardarClientesSetup(int $idAuditoria)
    {
        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria || $auditoria['estado'] !== 'borrador') {
            return redirect()
                ->to('/consultor/auditorias')
                ->with('error', 'Auditoría no válida.');
        }

        $clientesSeleccionados = $this->request->getPost('clientes') ?? [];
        $contratos = $this->request->getPost('contrato') ?? [];

        // Eliminar asignaciones anteriores
        $this->auditoriaClienteModel->where('id_auditoria', $idAuditoria)->delete();

        // Crear nuevas asignaciones con snapshot de contrato
        foreach ($clientesSeleccionados as $idCliente) {
            $this->auditoriaClienteModel->insert([
                'id_auditoria' => $idAuditoria,
                'id_cliente' => $idCliente,
                'id_contrato' => $contratos[$idCliente] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Redirigir al PASO 3: Invitar proveedor
        return redirect()
            ->to('/consultor/auditorias/' . $idAuditoria . '/enviar-invitacion')
            ->with('success', count($clientesSeleccionados) . ' cliente(s) asignado(s). Ahora puede invitar al proveedor.');
    }

    /**
     * PASO 3: Formulario para enviar invitación al proveedor
     */
    public function formEnviarInvitacion(int $idAuditoria)
    {
        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria) {
            return redirect()
                ->to('/consultor/auditorias')
                ->with('error', 'Auditoría no encontrada.');
        }

        // Obtener proveedor
        $proveedor = $this->proveedorModel->find($auditoria['id_proveedor']);

        // Obtener clientes asignados
        $clientes = $this->auditoriaClienteModel->getClientesByAuditoria($idAuditoria);

        // CRÍTICO: Obtener el usuario responsable del contrato (NO el usuario genérico del proveedor)
        $usuario = null;
        if (!empty($clientes)) {
            // Obtener el contrato para acceder al usuario responsable
            $contrato = $this->contratoModel
                ->select('contratos_proveedor_cliente.*,
                          users.email as usuario_responsable_email,
                          users.nombre as usuario_responsable_nombre,
                          users.id_users as usuario_responsable_id')
                ->join('users', 'users.id_users = contratos_proveedor_cliente.id_usuario_responsable')
                ->where('contratos_proveedor_cliente.id_proveedor', $auditoria['id_proveedor'])
                ->where('contratos_proveedor_cliente.id_cliente', $clientes[0]['id_cliente'])
                ->where('contratos_proveedor_cliente.estado', 'activo')
                ->first();

            if ($contrato && !empty($contrato['usuario_responsable_id'])) {
                $usuario = $this->userModel->find($contrato['usuario_responsable_id']);
            }
        }

        $data = [
            'title' => 'Enviar Invitación - Paso 3',
            'auditoria' => $auditoria,
            'proveedor' => $proveedor,
            'clientes' => $clientes,
            'usuario' => $usuario,  // Usuario responsable del proveedor, no usuario genérico
        ];

        return view('consultor/auditorias/enviar_invitacion', $data);
    }

    /**
     * PASO 3: Envía invitación al proveedor y cambia estado a 'en_proveedor'
     * Pre-crea ítems globales y por_cliente según corresponda
     */
    public function enviarInvitacion(int $idAuditoria)
    {
        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria) {
            return redirect()
                ->to('/consultor/auditorias')
                ->with('error', 'Auditoría no encontrada.');
        }

        // Validar que haya clientes asignados
        $clientes = $this->auditoriaClienteModel->getClientesByAuditoria($idAuditoria);

        if (empty($clientes)) {
            return redirect()
                ->back()
                ->with('error', 'Debe asignar al menos un cliente antes de enviar la invitación.');
        }

        // Obtener proveedor
        $proveedor = $this->proveedorModel->find($auditoria['id_proveedor']);

        if (!$proveedor) {
            return redirect()
                ->back()
                ->with('error', 'Proveedor no encontrado.');
        }

        // CRÍTICO: Obtener el contrato para acceder al usuario responsable
        // El email debe ir al usuario responsable (Analista SST o Coordinador SG-SST del proveedor)
        // NO al email genérico del proveedor (info@proveedor.com)

        // Buscar el contrato activo del proveedor con cualquiera de los clientes asignados
        $idClientesPrimero = !empty($clientes) ? $clientes[0]['id_cliente'] : null;

        if (!$idClientesPrimero) {
            return redirect()
                ->back()
                ->with('error', 'No se encontraron clientes asignados.');
        }

        // Obtener el contrato para acceder al usuario responsable
        $contrato = $this->contratoModel
            ->select('contratos_proveedor_cliente.*,
                      users.email as usuario_responsable_email,
                      users.nombre as usuario_responsable_nombre,
                      users.id_users as usuario_responsable_id')
            ->join('users', 'users.id_users = contratos_proveedor_cliente.id_usuario_responsable')
            ->where('contratos_proveedor_cliente.id_proveedor', $auditoria['id_proveedor'])
            ->where('contratos_proveedor_cliente.id_cliente', $idClientesPrimero)
            ->where('contratos_proveedor_cliente.estado', 'activo')
            ->first();

        if (!$contrato) {
            return redirect()
                ->back()
                ->with('error', 'No se encontró un contrato activo con usuario responsable asignado.');
        }

        // Verificar que el usuario responsable tenga email
        if (empty($contrato['usuario_responsable_email'])) {
            return redirect()
                ->back()
                ->with('error', 'El usuario responsable del proveedor no tiene email configurado.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // CORREGIDO: Usar solo los ítems que el consultor YA SELECCIONÓ en el paso 2
            // NO crear ítems de TODO el banco, solo de los que están en auditoria_items

            // Obtener los ítems que el consultor seleccionó (ya están en auditoria_items)
            // Incluir información completa del items_banco para el email
            $itemsSeleccionados = $this->auditoriaItemModel
                ->select('auditoria_items.*,
                         items_banco.alcance,
                         items_banco.titulo,
                         items_banco.descripcion,
                         items_banco.orden')
                ->join('items_banco', 'items_banco.id_item = auditoria_items.id_item')
                ->where('auditoria_items.id_auditoria', $idAuditoria)
                ->orderBy('items_banco.orden', 'ASC')
                ->findAll();

            log_message('info', 'enviarInvitacion - Items seleccionados: ' . count($itemsSeleccionados));

            // Crear registros en auditoria_item_cliente SOLO para items seleccionados con alcance='por_cliente'
            $itemsPorCliente = array_filter($itemsSeleccionados, function($item) {
                return $item['alcance'] === 'por_cliente';
            });

            $itemsCreados = 0;
            foreach ($clientes as $cliente) {
                foreach ($itemsPorCliente as $auditoriaItem) {
                    // Verificar si ya existe en auditoria_item_cliente
                    $existe = $db->table('auditoria_item_cliente')
                        ->where('id_auditoria_item', $auditoriaItem['id_auditoria_item'])
                        ->where('id_cliente', $cliente['id_cliente'])
                        ->countAllResults();

                    if ($existe == 0) {
                        $this->auditoriaItemClienteModel->insert([
                            'id_auditoria_item' => $auditoriaItem['id_auditoria_item'],
                            'id_cliente' => $cliente['id_cliente'],
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                        $itemsCreados++;
                    }
                }
            }

            log_message('info', 'enviarInvitacion - Registros auditoria_item_cliente creados: ' . $itemsCreados);

            // CRÍTICO: El usuario responsable ya debe existir (fue asignado en el contrato)
            $usuario = $this->userModel->find($contrato['usuario_responsable_id']);

            if (!$usuario) {
                throw new \Exception('El usuario responsable no se encontró en la base de datos.');
            }

            // Verificar que el usuario tenga rol de proveedor
            if ($usuario['id_roles'] != 3) {
                throw new \Exception('El usuario responsable debe tener rol de Proveedor (rol 3).');
            }

            // Generar nueva contraseña segura para enviar en el email
            // Esto permite que el proveedor tenga acceso incluso si olvidó su contraseña anterior
            helper('auth');
            $claveTemporal = generateSecurePassword(12);
            $hash = password_hash($claveTemporal, PASSWORD_DEFAULT);

            // Actualizar la contraseña del usuario
            $this->userModel->update($usuario['id_users'], [
                'password_hash' => $hash
            ]);

            $nombreUsuario = $usuario['email'];

            // Cambiar estado a 'en_proveedor'
            $this->auditoriaModel->update($idAuditoria, [
                'estado' => 'en_proveedor',
                'fecha_envio_proveedor' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de base de datos');
            }

            // CRÍTICO: Enviar email al usuario responsable (NO al email genérico del proveedor)
            $urlLogin = site_url('login');
            $urlAuditoria = site_url('proveedor/auditoria/' . $idAuditoria);

            $resultado = $this->emailService->sendInviteProveedor(
                $contrato['usuario_responsable_email'],  // Email del Analista SST o Coordinador SG-SST
                $nombreUsuario,
                $claveTemporal,  // Contraseña temporal generada
                $urlLogin,
                $urlAuditoria,
                $contrato['usuario_responsable_nombre'],  // Nombre del usuario responsable para el saludo
                $itemsSeleccionados,  // Ítems seleccionados con información completa
                $clientes  // Clientes asignados a la auditoría
            );

            if ($resultado['ok']) {
                $itemsGlobalesCount = count(array_filter($itemsSeleccionados, function($item) {
                    return in_array($item['alcance'], ['global', 'mixto']);
                }));
                return redirect()
                    ->to('/consultor/auditorias')
                    ->with('success', 'Invitación enviada exitosamente a ' . $contrato['usuario_responsable_nombre'] . ' (' . $contrato['usuario_responsable_email'] . '). Se configuraron ' . $itemsGlobalesCount . ' ítem(s) globales y ' . $itemsCreados . ' ítem(s) por cliente.');
            } else {
                return redirect()
                    ->back()
                    ->with('warning', 'La auditoría fue creada pero hubo un error al enviar el email: ' . $resultado['error']);
            }

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error al enviar invitación: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Error al procesar la invitación: ' . $e->getMessage());
        }
    }

    /**
     * PASO 2: Formulario para seleccionar ítems del banco
     */
    public function seleccionarItems(int $idAuditoria)
    {
        // Verificar que la auditoría existe y pertenece al consultor
        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria) {
            return redirect()
                ->to('/consultor/auditorias')
                ->with('error', 'Auditoría no encontrada.');
        }

        // Obtener todos los ítems activos del banco ordenados
        $items = $this->itemsBancoModel
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->findAll();

        // Obtener ítems ya asignados a esta auditoría
        $itemsAsignados = $this->auditoriaItemModel
            ->where('id_auditoria', $idAuditoria)
            ->findColumn('id_item');

        $data = [
            'title' => 'Seleccionar Ítems',
            'auditoria' => $auditoria,
            'items' => $items,
            'itemsAsignados' => $itemsAsignados ?: [],
        ];

        return view('consultor/auditorias/seleccionar_items', $data);
    }

    /**
     * PASO 2: Guarda los ítems seleccionados
     */
    public function guardarItems(int $idAuditoria)
    {
        $auditoria = $this->auditoriaModel->find($idAuditoria);

        if (!$auditoria) {
            return redirect()
                ->to('/consultor/auditorias')
                ->with('error', 'Auditoría no encontrada.');
        }

        $itemsSeleccionados = $this->request->getPost('items');

        // DEBUG: Log de ítems recibidos
        log_message('info', 'guardarItems called for auditoria: ' . $idAuditoria);
        log_message('info', 'Items recibidos: ' . json_encode($itemsSeleccionados));
        log_message('info', 'Total items: ' . (is_array($itemsSeleccionados) ? count($itemsSeleccionados) : 0));

        // Validar que al menos un ítem esté seleccionado
        if (empty($itemsSeleccionados) || !is_array($itemsSeleccionados)) {
            log_message('error', 'No items selected or invalid format');
            return redirect()
                ->back()
                ->with('error', 'Debe seleccionar al menos un ítem para continuar.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Primero eliminar registros de auditoria_item_cliente relacionados
            // (deben eliminarse antes por la foreign key constraint)
            $auditoriaItemsIds = $this->auditoriaItemModel
                ->where('id_auditoria', $idAuditoria)
                ->findColumn('id_auditoria_item');

            if (!empty($auditoriaItemsIds)) {
                $auditoriaItemClienteModel = model('App\Models\AuditoriaItemClienteModel');
                $auditoriaItemClienteModel->whereIn('id_auditoria_item', $auditoriaItemsIds)->delete();
            }

            // 2. Ahora eliminar los ítems de la auditoría
            $this->auditoriaItemModel->where('id_auditoria', $idAuditoria)->delete();

            // 3. Insertar los nuevos ítems seleccionados
            foreach ($itemsSeleccionados as $idItem) {
                $item = $this->itemsBancoModel->find($idItem);

                if (!$item) {
                    continue; // Saltar ítems inválidos
                }

                // Insertar en auditoria_items
                $dataItem = [
                    'id_auditoria' => $idAuditoria,
                    'id_item' => $idItem,
                    'titulo' => $item['titulo'],
                    'descripcion' => $item['descripcion'],
                    'alcance' => $item['alcance'],
                    'aplica_alto_riesgo' => $item['aplica_alto_riesgo'],
                    'orden' => $item['orden'],
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                $this->auditoriaItemModel->insert($dataItem);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al guardar los ítems');
            }

            // Redirigir al PASO 3: Asignar clientes
            return redirect()
                ->to('/consultor/auditorias/' . $idAuditoria . '/asignar-clientes-setup')
                ->with('success', count($itemsSeleccionados) . ' ítems asignados correctamente. Ahora asigne los clientes.');

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error al guardar ítems: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('error', 'Error al guardar los ítems: ' . $e->getMessage());
        }
    }
}
