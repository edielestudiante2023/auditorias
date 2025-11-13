<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProveedorModel;
use App\Models\ProveedoresModel;
use App\Models\ContratoModel;
use App\Models\UserModel;

class ProveedoresController extends BaseController
{
    protected ProveedorModel $proveedorModel;
    protected UserModel $userModel;

    public function __construct()
    {
        $this->proveedorModel = new ProveedorModel();
        $this->userModel = new UserModel();
        helper(['auth']);
    }

    /**
     * Lista todos los proveedores
     */
    public function index()
    {
        // Obtener TODOS los proveedores sin límite de paginación
        $proveedores = $this->proveedorModel
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title'       => 'Gestión de Proveedores',
            'proveedores' => $proveedores,
            'breadcrumbs' => [
                ['title' => 'Inicio', 'url' => site_url('admin/dashboard')],
                ['title' => 'Proveedores', 'url' => ''],
            ],
        ];

        return view('admin/proveedores/index', $data);
    }

    // Índice con búsqueda + paginación (patrón Clientes)
    public function index2()
    {
        $q = trim((string) $this->request->getGet('q'));
        $model = model(ProveedoresModel::class);

        if ($q !== '') {
            $model = $model->groupStart()
                           ->like('razon_social', $q)
                           ->orLike('nit', $q)
                           ->groupEnd();
        }

        $proveedores = $model->orderBy('razon_social', 'ASC')->paginate(10, 'proveedores');

        $data = [
            'title'       => 'Gestión de Proveedores',
            'proveedores' => $proveedores,
            'q'           => $q,
            'pager'       => $model->pager,
            'breadcrumbs' => [
                ['title' => 'Inicio', 'url' => site_url('admin/dashboard')],
                ['title' => 'Proveedores', 'url' => ''],
            ],
        ];

        return view('admin/proveedores/index', $data);
    }

    /**
     * Formulario para crear nuevo proveedor
     */
    public function crear()
    {
        // Obtener usuarios con rol proveedor (id_roles = 3) que no tengan proveedor asignado
        $usuariosProveedores = $this->userModel
            ->where('id_roles', 3)
            ->where('estado', 'activo')
            ->findAll();

        // Filtrar usuarios que ya tienen proveedor asignado
        $usuariosDisponibles = array_filter($usuariosProveedores, function ($user) {
            return !$this->proveedorModel->userHasProveedor($user['id_users']);
        });

        $data = [
            'title'      => 'Crear Proveedor',
            'proveedor'  => null,
            'usuarios'   => $usuariosDisponibles,
            'validation' => \Config\Services::validation(),
            'breadcrumbs' => [
                ['title' => 'Inicio', 'url' => site_url('admin/dashboard')],
                ['title' => 'Proveedores', 'url' => site_url('admin/proveedores')],
                ['title' => 'Crear', 'url' => ''],
            ],
        ];

        return view('admin/proveedores/form', $data);
    }

    /**
     * Procesa la creación de un nuevo proveedor
     */
    public function store()
    {
        // Determinar si se está creando un nuevo usuario o usando uno existente
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $nombreUsuario = $this->request->getPost('nombre_usuario');
        $idUsers = $this->request->getPost('id_users');

        // MODO 1: Crear usuario nuevo (si se proporciona email y password)
        if (!empty($email) && !empty($password) && !empty($nombreUsuario)) {
            // Validar que el email no exista
            $emailLimpio = strtolower(trim($email));
            $existeEmail = $this->userModel->where('email', $emailLimpio)->first();

            if ($existeEmail) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'El email <strong>' . htmlspecialchars($emailLimpio) . '</strong> ya está registrado en el sistema. Por favor usa otro email o selecciona "Usar Usuario Existente".');
            }

            // Crear usuario nuevo
            $userData = [
                'nombre'        => $nombreUsuario,
                'email'         => $emailLimpio,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'id_roles'      => 3, // Rol Proveedor
                'estado'        => 'activo',
            ];

            try {
                if (!$this->userModel->save($userData)) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('errors', $this->userModel->errors());
                }

                $idUser = $this->userModel->getInsertID();
            } catch (\Exception $e) {
                log_message('error', 'Error creating user for proveedor: ' . $e->getMessage());
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Error al crear el usuario. El email podría estar duplicado.');
            }
        }
        // MODO 2: Usar usuario existente
        elseif (!empty($idUsers)) {
            $idUser = $idUsers;

            // Validar que el usuario no tenga ya un registro de proveedor
            if ($this->proveedorModel->userHasProveedor($idUser)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Este usuario ya tiene un registro de proveedor.');
            }
        }
        // ERROR: No se proporcionaron datos válidos
        else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Debes crear un nuevo usuario o seleccionar uno existente.');
        }

        // Crear el proveedor
        $data = [
            'id_users'             => $idUser,
            'razon_social'         => $this->request->getPost('razon_social'),
            'nit'                  => $this->request->getPost('nit'),
            'email_contacto'       => $this->request->getPost('email_contacto'),
            'telefono_contacto'    => $this->request->getPost('telefono_contacto'),
            'observaciones'        => $this->request->getPost('observaciones'),
            'responsable_nombre'   => $this->request->getPost('responsable_nombre'),
            'responsable_email'    => $this->request->getPost('responsable_email'),
            'responsable_telefono' => $this->request->getPost('responsable_telefono'),
            'responsable_cargo'    => $this->request->getPost('responsable_cargo'),
        ];

        // Guardar proveedor
        if (!$this->proveedorModel->save($data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->proveedorModel->errors());
        }

        return redirect()
            ->to('/admin/proveedores')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    // Nuevo: create simple (patrón Clientes)
    public function create()
    {
        return view('admin/proveedores/create', [
            'title'       => 'Crear Proveedor',
            'form_action' => site_url('admin/proveedores'),
            'errors'      => session()->getFlashdata('errors') ?? [],
        ]);
    }

    // Nuevo: store para rutas REST-like con logo opcional
    public function storeNew()
    {
        $model = model(ProveedoresModel::class);

        $data = [
            'razon_social'         => trim((string) $this->request->getPost('razon_social')),
            'nit'                  => trim((string) $this->request->getPost('nit')),
            'email_contacto'       => $this->request->getPost('email_contacto'),
            'telefono_contacto'    => $this->request->getPost('telefono_contacto'),
            'observaciones'        => $this->request->getPost('observaciones'),
            'responsable_nombre'   => $this->request->getPost('responsable_nombre'),
            'responsable_email'    => $this->request->getPost('responsable_email'),
            'responsable_telefono' => $this->request->getPost('responsable_telefono'),
            'responsable_cargo'    => $this->request->getPost('responsable_cargo'),
        ];

        if (!$model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('/admin/proveedores')->with('success', 'Proveedor creado exitosamente. Ahora puedes vincular usuarios desde Admin → Usuarios.');
    }

    /**
     * Formulario para editar proveedor existente
     */
    public function editar(int $id)
    {
        $proveedor = $this->proveedorModel->getProveedorWithUser($id);

        if (!$proveedor) {
            return redirect()
                ->to('/admin/proveedores')
                ->with('error', 'Proveedor no encontrado.');
        }

        // Obtener usuarios proveedores disponibles
        $usuariosProveedores = $this->userModel
            ->where('id_roles', 3)
            ->where('estado', 'activo')
            ->findAll();

        $data = [
            'title'      => 'Editar Proveedor',
            'proveedor'  => $proveedor,
            'usuarios'   => $usuariosProveedores,
            'validation' => \Config\Services::validation(),
            'breadcrumbs' => [
                ['title' => 'Inicio', 'url' => site_url('admin/dashboard')],
                ['title' => 'Proveedores', 'url' => site_url('admin/proveedores')],
                ['title' => 'Editar', 'url' => ''],
            ],
        ];

        return view('admin/proveedores/form', $data);
    }

    /**
     * Procesa la actualización de un proveedor
     */
    public function update(int $id)
    {
        $proveedor = $this->proveedorModel->find($id);

        if (!$proveedor) {
            return redirect()
                ->to('/admin/proveedores')
                ->with('error', 'Proveedor no encontrado.');
        }

        $data = [
            'id_proveedor'         => $id,
            'razon_social'         => $this->request->getPost('razon_social'),
            'nit'                  => $this->request->getPost('nit'),
            'email_contacto'       => $this->request->getPost('email_contacto'),
            'telefono_contacto'    => $this->request->getPost('telefono_contacto'),
            'observaciones'        => $this->request->getPost('observaciones'),
            'responsable_nombre'   => $this->request->getPost('responsable_nombre'),
            'responsable_email'    => $this->request->getPost('responsable_email'),
            'responsable_telefono' => $this->request->getPost('responsable_telefono'),
            'responsable_cargo'    => $this->request->getPost('responsable_cargo'),
        ];

        // Actualizar proveedor
        if (!$this->proveedorModel->save($data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->proveedorModel->errors());
        }

        return redirect()
            ->to('/admin/proveedores')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    /**
     * Elimina un proveedor
     */
    public function eliminar(int $id)
    {
        $proveedor = $this->proveedorModel->find($id);

        if (!$proveedor) {
            return redirect()
                ->to('/admin/proveedores')
                ->with('error', 'Proveedor no encontrado.');
        }

        // Verificar si tiene contratos activos asociados
        $contratos = model(ContratoModel::class);
        $tieneActivos = $contratos->where('id_proveedor', $id)->where('estado', 'activo')->countAllResults() > 0;
        if ($tieneActivos) {
            return redirect()
                ->to('/admin/proveedores')
                ->with('error', 'No se puede eliminar el proveedor porque tiene contratos activos.');
        }

        // Eliminar proveedor
        if (!$this->proveedorModel->delete($id)) {
            return redirect()
                ->to('/admin/proveedores')
                ->with('error', 'No se pudo eliminar el proveedor.');
        }

        return redirect()
            ->to('/admin/proveedores')
            ->with('success', 'Proveedor eliminado exitosamente.');
    }

    // Nuevo: edit simple (patrón Clientes)
    public function edit(int $id)
    {
        $model = model(ProveedoresModel::class);
        $proveedor = $model->find($id);
        if (!$proveedor) {
            return redirect()->to('/admin/proveedores')->with('error', 'Proveedor no encontrado.');
        }

        return view('admin/proveedores/edit', [
            'title'       => 'Editar Proveedor',
            'proveedor'   => $proveedor,
            'form_action' => site_url('admin/proveedores/' . $id),
            'errors'      => session()->getFlashdata('errors') ?? [],
        ]);
    }

    // Nuevo: update con soporte de logo opcional
    public function updateNew(int $id)
    {
        $model = model(ProveedoresModel::class);
        $proveedor = $model->find($id);
        if (!$proveedor) {
            return redirect()->to('/admin/proveedores')->with('error', 'Proveedor no encontrado.');
        }

        $data = [
            'id_proveedor'         => $id,
            'razon_social'         => trim((string) $this->request->getPost('razon_social')),
            'nit'                  => trim((string) $this->request->getPost('nit')),
            'email_contacto'       => $this->request->getPost('email_contacto'),
            'telefono_contacto'    => $this->request->getPost('telefono_contacto'),
            'observaciones'        => $this->request->getPost('observaciones'),
            'responsable_nombre'   => $this->request->getPost('responsable_nombre'),
            'responsable_email'    => $this->request->getPost('responsable_email'),
            'responsable_telefono' => $this->request->getPost('responsable_telefono'),
            'responsable_cargo'    => $this->request->getPost('responsable_cargo'),
        ];

        if (!$model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('/admin/proveedores')->with('success', 'Proveedor actualizado exitosamente.');
    }

    // Nuevo: delete para rutas REST-like
    public function delete(int $id)
    {
        $contratos = model(ContratoModel::class);
        $tieneActivos = $contratos->where('id_proveedor', $id)->where('estado', 'activo')->countAllResults() > 0;
        if ($tieneActivos) {
            return redirect()->to('/admin/proveedores')->with('error', 'No se puede eliminar el proveedor porque tiene contratos activos.');
        }
        $this->proveedorModel->delete($id);
        return redirect()->to('/admin/proveedores')->with('success', 'Proveedor eliminado exitosamente.');
    }
}
