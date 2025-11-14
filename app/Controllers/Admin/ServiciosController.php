<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ServicioModel;

class ServiciosController extends BaseController
{
    protected $servicioModel;

    public function __construct()
    {
        $this->servicioModel = new ServicioModel();
        helper(['auth', 'form']);
    }

    /**
     * Lista todos los servicios
     */
    public function index()
    {
        if (!isSuperAdmin()) {
            return redirect()->to('/login')->with('error', 'Acceso denegado.');
        }

        $servicios = $this->servicioModel->orderBy('nombre', 'ASC')->findAll();

        return view('admin/servicios/index', [
            'title' => 'Gestión de Servicios',
            'servicios' => $servicios
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo servicio
     */
    public function create()
    {
        if (!isSuperAdmin()) {
            return redirect()->to('/login')->with('error', 'Acceso denegado.');
        }

        return view('admin/servicios/create', [
            'title' => 'Crear Servicio'
        ]);
    }

    /**
     * Guarda un nuevo servicio
     */
    public function store()
    {
        if (!isSuperAdmin()) {
            return redirect()->to('/login')->with('error', 'Acceso denegado.');
        }

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        if (!$this->servicioModel->save($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->servicioModel->errors());
        }

        return redirect()->to('admin/servicios')
            ->with('success', 'Servicio creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un servicio
     */
    public function edit($id = null)
    {
        if (!isSuperAdmin()) {
            return redirect()->to('/login')->with('error', 'Acceso denegado.');
        }

        $servicio = $this->servicioModel->find($id);

        if (!$servicio) {
            return redirect()->to('admin/servicios')
                ->with('error', 'Servicio no encontrado.');
        }

        return view('admin/servicios/edit', [
            'title' => 'Editar Servicio',
            'servicio' => $servicio
        ]);
    }

    /**
     * Actualiza un servicio
     */
    public function update($id = null)
    {
        if (!isSuperAdmin()) {
            return redirect()->to('/login')->with('error', 'Acceso denegado.');
        }

        $servicio = $this->servicioModel->find($id);

        if (!$servicio) {
            return redirect()->to('admin/servicios')
                ->with('error', 'Servicio no encontrado.');
        }

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'activo' => $this->request->getPost('activo') ?? 0
        ];

        if (!$this->servicioModel->update($id, $data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->servicioModel->errors());
        }

        return redirect()->to('admin/servicios')
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    /**
     * Elimina un servicio
     */
    public function delete($id = null)
    {
        if (!isSuperAdmin()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso denegado.'
            ]);
        }

        $servicio = $this->servicioModel->find($id);

        if (!$servicio) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Servicio no encontrado.'
            ]);
        }

        try {
            $this->servicioModel->delete($id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Servicio eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error al eliminar servicio: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar el servicio: ' . $e->getMessage()
            ]);
        }
    }
}
