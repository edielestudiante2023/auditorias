<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ItemsBancoModel;

class ItemsBancoController extends BaseController
{
    protected ItemsBancoModel $itemsModel;

    public function __construct()
    {
        $this->itemsModel = new ItemsBancoModel();
        helper('auth');
    }

    /**
     * Lista todos los items del banco
     */
    public function index()
    {
        $data = [
            'title' => 'Banco de Ítems',
            'items' => $this->itemsModel->getAllOrdered(),
        ];

        return view('admin/items_banco/index', $data);
    }

    /**
     * Formulario para crear nuevo item
     */
    public function crear()
    {
        $data = [
            'title'      => 'Crear Ítem',
            'item'       => null,
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/items_banco/form', $data);
    }

    /**
     * Procesa la creación de un nuevo item
     */
    public function store()
    {
        $data = [
            'codigo_item'        => $this->request->getPost('codigo_item'),
            'titulo'             => $this->request->getPost('titulo'),
            'descripcion'        => $this->request->getPost('descripcion'),
            'alcance'            => $this->request->getPost('alcance') ?: 'global',
            'aplica_alto_riesgo' => $this->request->getPost('aplica_alto_riesgo') ? 1 : 0,
            'orden'              => $this->request->getPost('orden') ?: $this->itemsModel->getNextOrder(),
            'activo'             => $this->request->getPost('activo') ? 1 : 0,
        ];

        if (!$this->itemsModel->save($data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->itemsModel->errors());
        }

        return redirect()
            ->to('/admin/items')
            ->with('success', 'Ítem creado exitosamente.');
    }

    /**
     * Formulario para editar item existente
     *
     * @param int $id
     */
    public function editar(int $id)
    {
        $item = $this->itemsModel->find($id);

        if (!$item) {
            return redirect()
                ->to('/admin/items')
                ->with('error', 'Ítem no encontrado.');
        }

        $data = [
            'title'      => 'Editar Ítem',
            'item'       => $item,
            'validation' => \Config\Services::validation(),
        ];

        return view('admin/items_banco/form', $data);
    }

    /**
     * Procesa la actualización de un item
     *
     * @param int $id
     */
    public function update(int $id)
    {
        $item = $this->itemsModel->find($id);

        if (!$item) {
            return redirect()
                ->to('/admin/items')
                ->with('error', 'Ítem no encontrado.');
        }

        $data = [
            'codigo_item'        => $this->request->getPost('codigo_item'),
            'titulo'             => $this->request->getPost('titulo'),
            'descripcion'        => $this->request->getPost('descripcion'),
            'alcance'            => $this->request->getPost('alcance') ?: 'global',
            'aplica_alto_riesgo' => $this->request->getPost('aplica_alto_riesgo') ? 1 : 0,
            'orden'              => $this->request->getPost('orden') ?: 0,
            'activo'             => $this->request->getPost('activo') ? 1 : 0,
        ];

        // Agregar id_item para validación de is_unique
        $data['id_item'] = $id;

        if (!$this->itemsModel->save($data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->itemsModel->errors());
        }

        return redirect()
            ->to('/admin/items')
            ->with('success', 'Ítem actualizado exitosamente.');
    }

    /**
     * Alterna el estado activo/inactivo de un item
     *
     * @param int $id
     */
    public function toggle(int $id)
    {
        if (!$this->itemsModel->toggleActive($id)) {
            return redirect()
                ->to('/admin/items')
                ->with('error', 'No se pudo cambiar el estado del ítem.');
        }

        return redirect()
            ->to('/admin/items')
            ->with('success', 'Estado del ítem actualizado.');
    }

    /**
     * Interfaz para reordenar items (drag & drop)
     */
    public function reordenar()
    {
        $data = [
            'title' => 'Reordenar Ítems',
            'items' => $this->itemsModel->getAllOrdered(),
        ];

        return view('admin/items_banco/reordenar', $data);
    }

    /**
     * Procesa la actualización del orden vía AJAX
     */
    public function updateOrden()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solicitud no válida.',
            ]);
        }

        $ordenData = $this->request->getJSON(true)['orden'] ?? [];

        if (empty($ordenData)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibieron datos de orden.',
            ]);
        }

        if ($this->itemsModel->updateOrden($ordenData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Orden actualizado exitosamente.',
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error al actualizar el orden.',
        ]);
    }

    /**
     * Elimina un item (soft delete deshabilitado, elimina físicamente)
     *
     * @param int $id
     */
    public function eliminar(int $id)
    {
        // Verificar si el item está siendo usado en auditorías
        $db = \Config\Database::connect();
        $usado = $db->table('auditoria_items')
                    ->where('id_item', $id)
                    ->countAllResults();

        if ($usado > 0) {
            return redirect()
                ->to('/admin/items')
                ->with('error', 'No se puede eliminar el ítem porque está siendo usado en auditorías.');
        }

        if (!$this->itemsModel->delete($id)) {
            return redirect()
                ->to('/admin/items')
                ->with('error', 'No se pudo eliminar el ítem.');
        }

        return redirect()
            ->to('/admin/items')
            ->with('success', 'Ítem eliminado exitosamente.');
    }
}
