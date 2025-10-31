<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ContratoModel;
use App\Models\ProveedorModel;
use App\Models\ClienteModel;
use App\Models\ServicioModel;
use App\Services\UploadService;

class ContratosController extends BaseController
{
    protected ContratoModel $contratoModel;
    protected ProveedorModel $proveedorModel;
    protected ClienteModel $clienteModel;
    protected ServicioModel $servicioModel;
    protected UploadService $uploadService;

    public function __construct()
    {
        $this->contratoModel = new ContratoModel();
        $this->proveedorModel = new ProveedorModel();
        $this->clienteModel = new ClienteModel();
        $this->servicioModel = new ServicioModel();
        $this->uploadService = (new UploadService())->setDatabase(\Config\Database::connect());
        helper(['auth', 'upload']);
    }

    private function getConsultoresActivos(): array
    {
        $consultorModel = new \App\Models\ConsultorModel();
        return $consultorModel->orderBy('nombre_completo', 'ASC')->findAll();
    }

    private function getUsuariosProveedores(): array
    {
        $userModel = new \App\Models\UserModel();
        return $userModel->where('id_roles', 3) // Rol Proveedor
                         ->where('estado', 'activo')
                         ->orderBy('nombre', 'ASC')
                         ->findAll();
    }

    /**
     * Lista todas las relaciones cliente-proveedor
     */
    public function index()
    {
        $data = [
            'title'     => 'Relaciones Cliente-Proveedor',
            'contratos' => $this->contratoModel->getContratosWithRelations(),
            'breadcrumbs' => [
                ['title' => 'Inicio', 'url' => site_url('admin/dashboard')],
                ['title' => 'Relaciones Cliente-Proveedor', 'url' => ''],
            ],
        ];

        return view('admin/contratos/index', $data);
    }

    /**
     * Formulario para crear nueva relación cliente-proveedor
     */
    public function crear()
    {
        $data = [
            'title'              => 'Nueva Relación Cliente-Proveedor',
            'contrato'           => null,
            'clientes'           => $this->clienteModel->getClientesActivos(),
            'proveedores'        => $this->proveedorModel->getProveedoresActivos(),
            'consultores'        => $this->getConsultoresActivos(),
            'usuarios_proveedores' => $this->getUsuariosProveedores(),
            'servicios'          => $this->servicioModel->where('activo', 1)->findAll(),
            'validation'         => \Config\Services::validation(),
            'breadcrumbs'        => [
                ['title' => 'Inicio', 'url' => site_url('admin/dashboard')],
                ['title' => 'Relaciones Cliente-Proveedor', 'url' => site_url('admin/contratos')],
                ['title' => 'Crear', 'url' => ''],
            ],
        ];

        return view('admin/contratos/form', $data);
    }

    /**
     * Procesa la creación de una nueva relación cliente-proveedor
     */
    public function store()
    {
        $data = [
            'id_cliente'             => $this->request->getPost('id_cliente'),
            'id_proveedor'           => $this->request->getPost('id_proveedor'),
            'id_servicio'            => $this->request->getPost('id_servicio'),
            'id_consultor'           => $this->request->getPost('id_consultor'),
            'id_usuario_responsable' => $this->request->getPost('id_usuario_responsable'),
            'tipo_auditoria'         => $this->request->getPost('tipo_auditoria'),
            'estado'                 => $this->request->getPost('estado') ?: 'activo',
            'observaciones'          => $this->request->getPost('observaciones'),
        ];

        // Guardar relación
        if (!$this->contratoModel->save($data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->contratoModel->errors());
        }

        return redirect()
            ->to('/admin/contratos')
            ->with('success', 'Relación cliente-proveedor creada exitosamente.');
    }

    /**
     * Formulario para editar relación cliente-proveedor existente
     */
    public function editar(int $id)
    {
        $contrato = $this->contratoModel->getContratoWithRelations($id);

        if (!$contrato) {
            return redirect()
                ->to('/admin/contratos')
                ->with('error', 'Relación no encontrada.');
        }

        $data = [
            'title'              => 'Editar Relación Cliente-Proveedor',
            'contrato'           => $contrato,
            'clientes'           => $this->clienteModel->getClientesActivos(),
            'proveedores'        => $this->proveedorModel->getProveedoresActivos(),
            'consultores'        => $this->getConsultoresActivos(),
            'usuarios_proveedores' => $this->getUsuariosProveedores(),
            'servicios'          => $this->servicioModel->where('activo', 1)->findAll(),
            'validation'         => \Config\Services::validation(),
            'breadcrumbs'        => [
                ['title' => 'Inicio', 'url' => site_url('admin/dashboard')],
                ['title' => 'Relaciones Cliente-Proveedor', 'url' => site_url('admin/contratos')],
                ['title' => 'Editar', 'url' => ''],
            ],
        ];

        return view('admin/contratos/form', $data);
    }

    /**
     * Procesa la actualización de una relación cliente-proveedor
     */
    public function update(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (!$contrato) {
            return redirect()
                ->to('/admin/contratos')
                ->with('error', 'Relación no encontrada.');
        }

        $data = [
            'id_contrato'            => $id,
            'id_cliente'             => $this->request->getPost('id_cliente'),
            'id_proveedor'           => $this->request->getPost('id_proveedor'),
            'id_servicio'            => $this->request->getPost('id_servicio'),
            'id_consultor'           => $this->request->getPost('id_consultor'),
            'id_usuario_responsable' => $this->request->getPost('id_usuario_responsable'),
            'tipo_auditoria'         => $this->request->getPost('tipo_auditoria'),
            'estado'                 => $this->request->getPost('estado'),
            'observaciones'          => $this->request->getPost('observaciones'),
        ];

        // Actualizar relación
        if (!$this->contratoModel->save($data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->contratoModel->errors());
        }

        return redirect()
            ->to('/admin/contratos')
            ->with('success', 'Relación cliente-proveedor actualizada exitosamente.');
    }

    /**
     * Elimina una relación cliente-proveedor
     */
    public function eliminar(int $id)
    {
        $contrato = $this->contratoModel->find($id);

        if (!$contrato) {
            return redirect()
                ->to('/admin/contratos')
                ->with('error', 'Relación no encontrada.');
        }

        // Verificar si tiene auditorías asociadas
        if ($this->contratoModel->hasAuditorias($id)) {
            return redirect()
                ->to('/admin/contratos')
                ->with('error', 'No se puede eliminar la relación porque tiene auditorías asociadas.');
        }

        // Eliminar relación
        if (!$this->contratoModel->delete($id)) {
            return redirect()
                ->to('/admin/contratos')
                ->with('error', 'No se pudo eliminar la relación.');
        }

        return redirect()
            ->to('/admin/contratos')
            ->with('success', 'Relación cliente-proveedor eliminada exitosamente.');
    }
}
