<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Services\EmailService;

class UsuariosController extends BaseController
{
    protected UserModel $userModel;
    protected EmailService $emailService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->emailService = new EmailService();
        helper(['auth']);
    }

    public function index()
    {
        $rol = trim((string) $this->request->getGet('rol'));
        $estado = trim((string) $this->request->getGet('estado'));

        $builder = $this->userModel;
        if (in_array($rol, ['1','2','3'], true)) {
            $builder = $builder->where('id_roles', (int) $rol);
        }
        if ($estado !== '' && in_array($estado, ['activo','inactivo'], true)) {
            $builder = $builder->where('estado', $estado);
        }

        $usuarios = $builder->orderBy('created_at', 'DESC')->findAll();

        return view('admin/usuarios/index', [
            'title'    => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
            'f_rol'    => $rol !== '' ? $rol : null,
            'f_estado' => $estado !== '' ? $estado : null,
        ]);
    }

    public function create()
    {
        // Obtener lista de proveedores para el select
        $proveedoresModel = model('App\Models\ProveedoresModel');
        $proveedores = $proveedoresModel->orderBy('razon_social', 'ASC')->findAll();

        return view('admin/usuarios/create', [
            'title'       => 'Crear Usuario',
            'form_action' => site_url('admin/usuarios'),
            'errors'      => session()->getFlashdata('errors') ?? [],
            'proveedores' => $proveedores,
        ]);
    }

    public function store()
    {
        $rules = [
            'nombre' => 'required|min_length[3]',
            'email'  => 'required|valid_email|is_unique[users.email] ',
            'id_roles' => 'required|in_list[1,2,3]',
            'estado' => 'required|in_list[activo,inactivo]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Generar contraseña segura aleatoria
        $passwordTemporal = generateSecurePassword(12);
        $hash = password_hash($passwordTemporal, PASSWORD_DEFAULT);

        $nombre = trim((string) $this->request->getPost('nombre'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $idRol = (int) $this->request->getPost('id_roles');
        $estado = $this->request->getPost('estado');

        $data = [
            'nombre'        => $nombre,
            'email'         => $email,
            'password_hash' => $hash,
            'id_roles'      => $idRol,
            'estado'        => $estado,
        ];

        try {
            if (!$this->userModel->save($data)) {
                $errors = $this->userModel->errors();
                log_message('error', 'Error al crear usuario: ' . json_encode($errors));
                return redirect()->back()->withInput()->with('errors', $errors);
            }
        } catch (\Exception $e) {
            log_message('error', 'Excepción al crear usuario: ' . $e->getMessage());

            // Verificar si es error de email duplicado
            if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'email') !== false) {
                return redirect()->back()->withInput()->with('error', 'El email ya está registrado en el sistema. Por favor usa otro email.');
            }

            return redirect()->back()->withInput()->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }

        $userId = $this->userModel->getInsertID();

        // Si se seleccionaron proveedores, vincular el usuario a través de la tabla intermedia
        $proveedores = $this->request->getPost('proveedores');
        if ($idRol == 3 && !empty($proveedores) && is_array($proveedores)) {
            $usuariosProveedoresModel = model('App\Models\UsuariosProveedoresModel');
            $usuariosProveedoresModel->vincularProveedores($userId, $proveedores);
        }

        // Solo enviar email a Admin y Consultor, NO a Proveedor
        // El proveedor recibirá sus credenciales cuando se le envíe la primera invitación de auditoría
        $mensaje = "Usuario <strong>{$nombre}</strong> creado exitosamente.";

        if ($idRol == 3) {
            // Rol Proveedor: NO enviar email ahora
            $mensaje .= " <span class='text-info'>ℹ️ El proveedor recibirá sus credenciales cuando se le asigne su primera auditoría.</span>";
        } else {
            // Roles Admin y Consultor: Enviar email con credenciales
            $rolesNombres = [
                1 => 'Administrador',
                2 => 'Consultor',
            ];
            $nombreRol = $rolesNombres[$idRol] ?? 'Usuario';

            $urlLogin = site_url('login');
            $resultadoEmail = $this->emailService->enviarCredencialesNuevoUsuario(
                $email,
                $nombre,
                $nombreRol,
                $passwordTemporal,
                $urlLogin
            );

            if ($resultadoEmail['ok']) {
                $mensaje .= " Se envió un email con las credenciales a <strong>{$email}</strong>.";
            } else {
                $mensaje .= " <span class='text-warning'>⚠️ No se pudo enviar el email. Contraseña temporal: <code>{$passwordTemporal}</code></span>";
                log_message('warning', "No se pudo enviar email de credenciales a {$email}: " . $resultadoEmail['error']);
            }
        }

        return redirect()->to('/admin/usuarios')
            ->with('success', $mensaje);
    }

    public function edit(int $id)
    {
        $usuario = $this->userModel->find($id);
        if (!$usuario) {
            return redirect()->to('/admin/usuarios')->with('error', 'Usuario no encontrado.');
        }

        $esActual = (userId() === (int) $id);

        // Obtener lista de proveedores
        $proveedoresModel = model('App\Models\ProveedoresModel');
        $proveedores = $proveedoresModel->orderBy('razon_social', 'ASC')->findAll();

        // Buscar proveedores vinculados al usuario (tabla intermedia)
        $proveedoresVinculados = [];
        if ($usuario['id_roles'] == 3) {
            $usuariosProveedoresModel = model('App\Models\UsuariosProveedoresModel');
            $proveedoresVinculados = $usuariosProveedoresModel->getProveedoresByUsuario($id);
        }

        return view('admin/usuarios/edit', [
            'title'                 => 'Editar Usuario',
            'usuario'               => $usuario,
            'form_action'           => site_url('admin/usuarios/' . $id),
            'errors'                => session()->getFlashdata('errors') ?? [],
            'esActual'              => $esActual,
            'proveedores'           => $proveedores,
            'proveedoresVinculados' => $proveedoresVinculados,
        ]);
    }

    public function update(int $id)
    {
        $usuario = $this->userModel->find($id);
        if (!$usuario) {
            return redirect()->to('/admin/usuarios')->with('error', 'Usuario no encontrado.');
        }

        $rules = [
            'nombre' => 'required|min_length[3]',
            'email'  => 'required|valid_email|is_unique[users.email,id_users,' . $id . ']',
            'estado' => 'required|in_list[activo,inactivo]',
        ];

        $esActual = (userId() === (int) $id);
        if (!$esActual) {
            $rules['id_roles'] = 'required|in_list[1,2,3]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id_users' => $id,
            'nombre'   => trim((string) $this->request->getPost('nombre')),
            'email'    => strtolower(trim((string) $this->request->getPost('email'))),
            'estado'   => $this->request->getPost('estado'),
        ];

        if (!$esActual) {
            $data['id_roles'] = (int) $this->request->getPost('id_roles');
        }

        try {
            if (!$this->userModel->save($data)) {
                $errors = $this->userModel->errors();
                log_message('error', 'Error al actualizar usuario: ' . json_encode($errors));
                return redirect()->back()->withInput()->with('errors', $errors);
            }
        } catch (\Exception $e) {
            log_message('error', 'Excepción al actualizar usuario: ' . $e->getMessage());

            // Verificar si es error de email duplicado
            if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'email') !== false) {
                return redirect()->back()->withInput()->with('error', 'El email ya está registrado en el sistema. Por favor usa otro email.');
            }

            return redirect()->back()->withInput()->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }

        // Si el rol es Proveedor, actualizar las vinculaciones en tabla intermedia
        $idRol = !$esActual ? (int) $this->request->getPost('id_roles') : $usuario['id_roles'];
        if ($idRol == 3) {
            $proveedores = $this->request->getPost('proveedores');
            $usuariosProveedoresModel = model('App\Models\UsuariosProveedoresModel');

            if (!empty($proveedores) && is_array($proveedores)) {
                // Actualizar vinculaciones (elimina anteriores y crea nuevas)
                $usuariosProveedoresModel->vincularProveedores($id, $proveedores);
            } else {
                // Si no hay proveedores seleccionados, desvincular todos
                $usuariosProveedoresModel->desvincularTodos($id);
            }
        }

        return redirect()->to('/admin/usuarios')->with('success', 'Usuario actualizado.');
    }

    /**
     * Resetea la contraseña de un usuario
     */
    public function resetPassword(int $id)
    {
        $usuario = $this->userModel->find($id);
        if (!$usuario) {
            return redirect()->to('/admin/usuarios')->with('error', 'Usuario no encontrado.');
        }

        // Generar nueva contraseña segura
        $passwordTemporal = generateSecurePassword(12);
        $hash = password_hash($passwordTemporal, PASSWORD_DEFAULT);

        $data = [
            'id_users' => $id,
            'password_hash' => $hash,
        ];

        if (!$this->userModel->save($data)) {
            return redirect()->back()->with('error', 'Error al resetear la contraseña.');
        }

        // Obtener nombre del rol
        $rolesNombres = [
            1 => 'Administrador',
            2 => 'Consultor',
            3 => 'Proveedor (Usuario Responsable)',
        ];
        $nombreRol = $rolesNombres[$usuario['id_roles']] ?? 'Usuario';

        // Enviar email con nueva contraseña
        $urlLogin = site_url('login');
        $resultadoEmail = $this->emailService->enviarCredencialesNuevoUsuario(
            $usuario['email'],
            $usuario['nombre'],
            $nombreRol,
            $passwordTemporal,
            $urlLogin
        );

        // Mensaje de éxito
        $mensaje = "Contraseña reseteada para <strong>{$usuario['nombre']}</strong>.";

        if ($resultadoEmail['ok']) {
            $mensaje .= " Se envió un email con la nueva contraseña a <strong>{$usuario['email']}</strong>.";
        } else {
            $mensaje .= " <span class='text-warning'>⚠️ No se pudo enviar el email. Nueva contraseña temporal: <code>{$passwordTemporal}</code></span>";
            log_message('warning', "No se pudo enviar email al resetear contraseña a {$usuario['email']}: " . $resultadoEmail['error']);
        }

        return redirect()->to('/admin/usuarios')
            ->with('success', $mensaje);
    }

    /**
     * Elimina un usuario
     */
    public function delete(int $id)
    {
        $usuario = $this->userModel->find($id);
        if (!$usuario) {
            return redirect()->to('/admin/usuarios')->with('error', 'Usuario no encontrado.');
        }

        // No permitir eliminar el propio usuario
        if ($id == userId()) {
            return redirect()->to('/admin/usuarios')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // Verificar si está vinculado a un consultor
        $consultorModel = model('App\Models\ConsultorModel');
        $consultor = $consultorModel->where('id_users', $id)->first();
        if ($consultor) {
            return redirect()->to('/admin/usuarios')
                ->with('error', 'No se puede eliminar. Este usuario está vinculado al Consultor #' . $consultor['id_consultor'] . '. Elimina primero el consultor.');
        }

        // Verificar si está vinculado a un proveedor
        $proveedorModel = model('App\Models\ProveedorModel');
        $proveedor = $proveedorModel->where('id_users', $id)->first();
        if ($proveedor) {
            return redirect()->to('/admin/usuarios')
                ->with('error', 'No se puede eliminar. Este usuario está vinculado al Proveedor #' . $proveedor['id_proveedor'] . '. Elimina primero el proveedor.');
        }

        // Eliminar usuario
        if (!$this->userModel->delete($id)) {
            return redirect()->to('/admin/usuarios')->with('error', 'Error al eliminar el usuario.');
        }

        return redirect()->to('/admin/usuarios')->with('success', "Usuario {$usuario['nombre']} eliminado exitosamente.");
    }

    /**
     * API: Verifica si un email ya existe en la base de datos
     * Método: GET/POST
     * Retorna: JSON con {exists: true/false, message: string}
     */
    public function checkEmail()
    {
        $email = $this->request->getGet('email') ?? $this->request->getPost('email');
        $excludeId = $this->request->getGet('exclude_id') ?? $this->request->getPost('exclude_id');

        if (empty($email)) {
            return $this->response->setJSON([
                'exists' => false,
                'message' => 'Email no proporcionado'
            ]);
        }

        $emailLimpio = strtolower(trim($email));

        $builder = $this->userModel->where('email', $emailLimpio);

        // Excluir un ID específico (útil para edición)
        if ($excludeId) {
            $builder->where('id_users !=', (int)$excludeId);
        }

        $usuario = $builder->first();

        if ($usuario) {
            return $this->response->setJSON([
                'exists' => true,
                'message' => 'El email ya está registrado en el sistema',
                'user' => [
                    'nombre' => $usuario['nombre'],
                    'rol' => $usuario['id_roles']
                ]
            ]);
        }

        return $this->response->setJSON([
            'exists' => false,
            'message' => 'Email disponible'
        ]);
    }
}

