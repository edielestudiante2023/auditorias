<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Models\ClientesModel;
use App\Models\ContratoModel;
use App\Services\UploadService;

class ClientesController extends BaseController
{
    protected ClienteModel $clienteModel;
    protected UploadService $uploadService;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->uploadService = (new UploadService())->setDatabase(\Config\Database::connect());
        helper(['auth', 'upload']);
    }

    // Nuevo índice con búsqueda y paginación
    public function index2()
    {
        $q = trim((string) $this->request->getGet('q'));

        $model = $this->clienteModel;
        if ($q !== '') {
            $model = $model->groupStart()
                           ->like('razon_social', $q)
                           ->orLike('nit', $q)
                           ->groupEnd();
        }

        $clientes = $model->orderBy('razon_social', 'ASC')->paginate(10, 'clientes');

        return view('admin/clientes/index', [
            'title'     => 'Gestión de Clientes',
            'clientes'  => $clientes,
            'q'         => $q,
            'pager'     => $this->clienteModel->pager,
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'admin/dashboard'],
                ['title' => 'Clientes', 'url' => null]
            ]
        ]);
    }

    public function index()
    {
        return view('admin/clientes/index', [
            'title' => 'Gestión de Clientes',
            'clientes' => $this->clienteModel->orderBy('razon_social', 'ASC')->findAll(),
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'admin/dashboard'],
                ['title' => 'Clientes', 'url' => null]
            ]
        ]);
    }

    public function crear()
    {
        return view('admin/clientes/form', [
            'title' => 'Crear Cliente',
            'cliente' => null,
            'form_action' => site_url('admin/clientes/guardar'),
            'validation' => \Config\Services::validation(),
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'admin/dashboard'],
                ['title' => 'Clientes', 'url' => 'admin/clientes'],
                ['title' => 'Crear', 'url' => null]
            ]
        ]);
    }

    // Nuevo: método create() (vista simple)
    public function create()
    {
        return view('admin/clientes/create', [
            'title'       => 'Crear Cliente',
            'form_action' => site_url('admin/clientes'),
            'errors'      => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function guardar()
    {
        $data = [
            'razon_social' => $this->request->getPost('razon_social'),
            'nit' => $this->request->getPost('nit'),
            'email_contacto' => $this->request->getPost('email_contacto'),
            'telefono_contacto' => $this->request->getPost('telefono_contacto'),
            'direccion' => $this->request->getPost('direccion'),
            'estado' => $this->request->getPost('estado') ?: 'activo',
        ];

        if (!$this->clienteModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->clienteModel->errors());
        }

        $idCliente = $this->clienteModel->getInsertID();

        // Procesar logo
        $logoFile = $this->request->getFile('logo_cliente');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            if (!$this->uploadService->isImage(['tmp_name' => $logoFile->getTempName()])) {
                return redirect()->back()->withInput()->with('error', 'El logo debe ser una imagen PNG o JPG.');
            }

            $result = $this->uploadService->saveLogoCliente([
                'name' => $logoFile->getName(),
                'type' => $logoFile->getClientMimeType(),
                'tmp_name' => $logoFile->getTempName(),
                'error' => $logoFile->getError(),
                'size' => $logoFile->getSize(),
            ], $idCliente);

            if ($result['ok']) {
                $this->clienteModel->update($idCliente, ['logo_cliente_path' => $result['path']]);
            } else {
                log_message('error', 'Error al guardar logo del cliente: ' . ($result['message'] ?? 'Sin mensaje'));
            }
        }

        return redirect()->to('/admin/clientes')->with('success', 'Cliente creado exitosamente.');
    }

    public function store()
    {
        $clientesModel = new ClientesModel();

        $data = [
            'razon_social' => trim((string) $this->request->getPost('razon_social')),
            'nit'          => trim((string) $this->request->getPost('nit')),
            'estado'       => $this->request->getPost('estado') ?: 'activo',
        ];

        if (!$clientesModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $clientesModel->errors());
        }

        return redirect()->to('/admin/clientes')->with('success', 'Cliente creado exitosamente.');
    }

    public function editar(int $id)
    {
        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            return redirect()->to('/admin/clientes')->with('error', 'Cliente no encontrado.');
        }

        return view('admin/clientes/form', [
            'title' => 'Editar Cliente',
            'cliente' => $cliente,
            'form_action' => site_url('admin/clientes/' . $id . '/update'),
            'validation' => \Config\Services::validation(),
            'breadcrumbs' => [
                ['title' => 'Dashboard', 'url' => 'admin/dashboard'],
                ['title' => 'Clientes', 'url' => 'admin/clientes'],
                ['title' => 'Editar', 'url' => null]
            ]
        ]);
    }

    // Nuevo: método edit() (vista simple)
    public function edit(int $id)
    {
        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            return redirect()->to('/admin/clientes')->with('error', 'Cliente no encontrado.');
        }

        return view('admin/clientes/edit', [
            'title'       => 'Editar Cliente',
            'cliente'     => $cliente,
            'form_action' => site_url('admin/clientes/' . $id),
            'errors'      => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function update(int $id)
    {
        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            return redirect()->to('/admin/clientes')->with('error', 'Cliente no encontrado.');
        }

        $data = [
            'id_cliente' => $id,
            'razon_social' => $this->request->getPost('razon_social'),
            'nit' => $this->request->getPost('nit'),
            'email_contacto' => $this->request->getPost('email_contacto'),
            'telefono_contacto' => $this->request->getPost('telefono_contacto'),
            'direccion' => $this->request->getPost('direccion'),
            'estado' => $this->request->getPost('estado') ?: 'activo',
        ];

        // Procesar nuevo logo
        $logoFile = $this->request->getFile('logo_cliente');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            if (!$this->uploadService->isImage(['tmp_name' => $logoFile->getTempName()])) {
                return redirect()->back()->withInput()->with('error', 'El logo debe ser una imagen PNG o JPG.');
            }

            $result = $this->uploadService->saveLogoCliente([
                'name' => $logoFile->getName(),
                'type' => $logoFile->getClientMimeType(),
                'tmp_name' => $logoFile->getTempName(),
                'error' => $logoFile->getError(),
                'size' => $logoFile->getSize(),
            ], $id);

            if ($result['ok']) {
                if (!empty($cliente['logo_cliente_path'])) {
                    $this->uploadService->deleteFile($cliente['logo_cliente_path']);
                }
                $data['logo_cliente_path'] = $result['path'];
            } else {
                log_message('error', 'Error al actualizar logo del cliente: ' . ($result['message'] ?? 'Sin mensaje'));
                return redirect()->back()->withInput()->with('error', 'Error al guardar el logo: ' . ($result['message'] ?? 'Error desconocido'));
            }
        }

        if (!$this->clienteModel->save($data)) {
            return redirect()->back()->withInput()->with('errors', $this->clienteModel->errors());
        }

        return redirect()->to('/admin/clientes')->with('success', 'Cliente actualizado exitosamente.');
    }

    public function eliminar(int $id)
    {
        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            return redirect()->to('/admin/clientes')->with('error', 'Cliente no encontrado.');
        }

        if ($this->clienteModel->hasContratos($id) || $this->clienteModel->hasAuditorias($id)) {
            return redirect()->to('/admin/clientes')->with('error', 'No se puede eliminar el cliente porque tiene contratos o auditorías asociadas.');
        }

        if (!empty($cliente['logo_cliente_path'])) {
            $this->uploadService->deleteFile($cliente['logo_cliente_path']);
        }

        $this->clienteModel->delete($id);
        return redirect()->to('/admin/clientes')->with('success', 'Cliente eliminado exitosamente.');
    }

    public function toggle(int $id)
    {
        if (!$this->clienteModel->toggleEstado($id)) {
            return redirect()->to('/admin/clientes')->with('error', 'No se pudo cambiar el estado.');
        }
        return redirect()->to('/admin/clientes')->with('success', 'Estado actualizado.');
    }

    public function eliminarLogo(int $id)
    {
        $cliente = $this->clienteModel->find($id);
        if ($cliente && !empty($cliente['logo_cliente_path'])) {
            $this->uploadService->deleteFile($cliente['logo_cliente_path']);
            $this->clienteModel->update($id, ['logo_cliente_path' => null]);
            return redirect()->back()->with('success', 'Logo eliminado exitosamente.');
        }
        return redirect()->back()->with('error', 'No se pudo eliminar el logo.');
    }

    // Nuevo: método delete() requerido por las rutas REST-like
    public function delete(int $id)
    {
        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            return redirect()->to('/admin/clientes')->with('error', 'Cliente no encontrado.');
        }

        $contratosModel = new ContratoModel();
        $tiene = $contratosModel->where('id_cliente', $id)->countAllResults() > 0;
        if ($tiene) {
            return redirect()->to('/admin/clientes')->with('error', 'No se puede eliminar el cliente: tiene contratos asociados.');
        }

        $this->clienteModel->delete($id);
        return redirect()->to('/admin/clientes')->with('success', 'Cliente eliminado exitosamente.');
    }
}
