<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ConsultorModel;
use App\Models\ConsultoresModel;
use App\Models\UserModel;
use App\Services\UploadService;

class ConsultoresController extends BaseController
{
    protected ConsultorModel $consultorModel;
    protected UserModel $userModel;
    protected UploadService $uploadService;

    public function __construct()
    {
        $this->consultorModel = new ConsultorModel();
        $this->userModel = new UserModel();
        $this->uploadService = (new UploadService())->setDatabase(\Config\Database::connect());
        helper(['auth', 'upload']);
    }

    // Nuevo flujo básico independiente de Users: mostrar formulario simple
    public function create()
    {
        return view('admin/consultores/create', [
            'title'       => 'Crear Consultor',
            'form_action' => site_url('admin/consultores'),
            'errors'      => session()->getFlashdata('errors') ?? [],
        ]);
    }

    // Guarda consultor básico (nombre, email, teléfono, estado)
    public function storeSimple()
    {
        $model = model(ConsultoresModel::class);

        $data = [
            'nombre'   => trim((string) $this->request->getPost('nombre')),
            'email'    => trim((string) $this->request->getPost('email')),
            'telefono' => trim((string) $this->request->getPost('telefono')),
            'estado'   => $this->request->getPost('estado') ? 'activo' : 'inactivo',
        ];

        if (!$model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('/admin/consultores')->with('success', 'Consultor creado exitosamente.');
    }

    // Edit básico
    public function edit(int $id)
    {
        $model = model(ConsultoresModel::class);
        $consultor = $model->find($id);
        if (!$consultor) {
            return redirect()->to('/admin/consultores')->with('error', 'Consultor no encontrado.');
        }
        return view('admin/consultores/edit', [
            'title'       => 'Editar Consultor',
            'consultor'   => $consultor,
            'form_action' => site_url('admin/consultores/' . $id),
            'errors'      => session()->getFlashdata('errors') ?? [],
        ]);
    }

    // Update básico
    public function updateSimple(int $id)
    {
        $model = model(ConsultoresModel::class);
        $consultor = $model->find($id);
        if (!$consultor) {
            return redirect()->to('/admin/consultores')->with('error', 'Consultor no encontrado.');
        }

        $data = [
            'id_consultor' => $id,
            'nombre'       => trim((string) $this->request->getPost('nombre')),
            'email'        => trim((string) $this->request->getPost('email')),
            'telefono'     => trim((string) $this->request->getPost('telefono')),
            'estado'       => $this->request->getPost('estado') ? 'activo' : 'inactivo',
        ];

        if (!$model->save($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        return redirect()->to('/admin/consultores')->with('success', 'Consultor actualizado exitosamente.');
    }

    // Delete básico
    public function delete(int $id)
    {
        $model = model(ConsultoresModel::class);
        if (!$model->find($id)) {
            return redirect()->to('/admin/consultores')->with('error', 'Consultor no encontrado.');
        }
        $model->delete($id);
        return redirect()->to('/admin/consultores')->with('success', 'Consultor eliminado exitosamente.');
    }

    /**
     * Lista todos los consultores
     */
    public function index()
    {
        $data = [
            'title'       => 'Gestión de Consultores',
            'consultores' => $this->consultorModel->getConsultoresWithUsers(),
            'breadcrumbs' => [
                ['title' => 'Inicio', 'url' => site_url('admin/dashboard')],
                ['title' => 'Consultores', 'url' => ''],
            ],
        ];

        return view('admin/consultores/index', $data);
    }

    /**
     * Formulario para crear nuevo consultor
     */
    public function crear()
    {
        // Obtener solo usuarios con rol consultor (id_roles = 2) que no tengan registro de consultor
        $usuariosConsultores = $this->userModel
            ->where('id_roles', 2)
            ->where('estado', 'activo')
            ->findAll();

        // Filtrar usuarios que ya tienen registro de consultor
        $usuariosDisponibles = array_filter($usuariosConsultores, function ($user) {
            return !$this->consultorModel->userHasConsultor($user['id_users']);
        });

        $data = [
            'title'      => 'Crear Consultor',
            'consultor'  => null,
            'usuarios'   => $usuariosDisponibles,
            'validation' => \Config\Services::validation(),
            'breadcrumbs' => [
                ['title' => 'Inicio', 'url' => site_url('admin/dashboard')],
                ['title' => 'Consultores', 'url' => site_url('admin/consultores')],
                ['title' => 'Crear', 'url' => ''],
            ],
        ];

        return view('admin/consultores/form', $data);
    }

    /**
     * Procesa la creación de un nuevo consultor
     */
    public function store()
    {
        // Determinar si se está creando un nuevo usuario o usando uno existente
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $idUsers = $this->request->getPost('id_users');
        $nombreCompleto = $this->request->getPost('nombre_completo');

        // MODO 1: Crear usuario nuevo (si se proporciona email y password)
        if (!empty($email) && !empty($password)) {
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
                'nombre'        => $nombreCompleto,
                'email'         => $emailLimpio,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'id_roles'      => 2, // Rol Consultor
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
                log_message('error', 'Error creating user: ' . $e->getMessage());
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Error al crear el usuario. El email podría estar duplicado.');
            }
        }
        // MODO 2: Usar usuario existente
        elseif (!empty($idUsers)) {
            $idUser = $idUsers;

            // Validar que el usuario no tenga ya un registro de consultor
            if ($this->consultorModel->userHasConsultor($idUser)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Este usuario ya tiene un registro de consultor.');
            }
        }
        // ERROR: No se proporcionaron datos válidos
        else {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Debes crear un nuevo usuario o seleccionar uno existente.');
        }

        // PASO 2: Crear el consultor
        $data = [
            'id_users'         => $idUser,
            'nombre_completo'  => $nombreCompleto,
            'tipo_documento'   => $this->request->getPost('tipo_documento'),
            'numero_documento' => $this->request->getPost('numero_documento'),
            'licencia_sst'     => $this->request->getPost('licencia_sst'),
            'email'            => $this->request->getPost('email'),
            'telefono'         => $this->request->getPost('telefono'),
        ];

        // Procesar firma si se subió
        $firmaFile = $this->request->getFile('firma');
        if ($firmaFile && $firmaFile->isValid()) {
            $firmaArray = [
                'name'     => $firmaFile->getName(),
                'type'     => $firmaFile->getClientMimeType(),
                'tmp_name' => $firmaFile->getTempName(),
                'error'    => $firmaFile->getError(),
                'size'     => $firmaFile->getSize(),
            ];

            // Validar que sea imagen
            if (!$this->uploadService->isImage($firmaArray)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'La firma debe ser una imagen PNG o JPG.');
            }

            // Guardar temporalmente para obtener ID después
            $firmaTemp = $firmaArray;
        }

        // Guardar consultor
        if (!$this->consultorModel->save($data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->consultorModel->errors());
        }

        // Obtener ID del consultor recién creado
        $idConsultor = $this->consultorModel->getInsertID();

        // Guardar firma si existe
        if (isset($firmaTemp)) {
            $resultFirma = $this->uploadService->saveFirmaConsultor($firmaTemp, $idConsultor);

            if ($resultFirma['ok']) {
                $this->consultorModel->update($idConsultor, [
                    'firma_path' => $resultFirma['path']
                ]);
            }
        }

        return redirect()
            ->to('/admin/consultores')
            ->with('success', 'Consultor creado exitosamente.');
    }

    /**
     * Formulario para editar consultor existente
     *
     * @param int $id
     */
    public function editar(int $id)
    {
        $consultor = $this->consultorModel->getConsultorWithUser($id);

        if (!$consultor) {
            return redirect()
                ->to('/admin/consultores')
                ->with('error', 'Consultor no encontrado.');
        }

        // Obtener usuarios consultores disponibles (incluir el actual)
        $usuariosConsultores = $this->userModel
            ->where('id_roles', 2)
            ->where('estado', 'activo')
            ->findAll();

        $data = [
            'title'      => 'Editar Consultor',
            'consultor'  => $consultor,
            'usuarios'   => $usuariosConsultores,
            'validation' => \Config\Services::validation(),
            'breadcrumbs' => [
                ['title' => 'Inicio', 'url' => site_url('admin/dashboard')],
                ['title' => 'Consultores', 'url' => site_url('admin/consultores')],
                ['title' => 'Editar', 'url' => ''],
            ],
        ];

        return view('admin/consultores/form', $data);
    }

    /**
     * Procesa la actualización de un consultor
     *
     * @param int $id
     */
    public function update(int $id)
    {
        $consultor = $this->consultorModel->find($id);

        if (!$consultor) {
            return redirect()
                ->to('/admin/consultores')
                ->with('error', 'Consultor no encontrado.');
        }

        $data = [
            'id_consultor'     => $id, // Para validación is_unique
            'id_users'         => $this->request->getPost('id_users'),
            'nombre_completo'  => $this->request->getPost('nombre_completo'),
            'tipo_documento'   => $this->request->getPost('tipo_documento'),
            'numero_documento' => $this->request->getPost('numero_documento'),
            'licencia_sst'     => $this->request->getPost('licencia_sst'),
            'email'            => $this->request->getPost('email'),
            'telefono'         => $this->request->getPost('telefono'),
        ];

        // Validar que el usuario no esté asignado a otro consultor
        if ($data['id_users'] != $consultor['id_users']) {
            if ($this->consultorModel->userHasConsultor($data['id_users'], $id)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Este usuario ya está asignado a otro consultor.');
            }
        }

        // Procesar nueva firma si se subió
        $firmaFile = $this->request->getFile('firma');
        if ($firmaFile && $firmaFile->isValid()) {
            $firmaArray = [
                'name'     => $firmaFile->getName(),
                'type'     => $firmaFile->getClientMimeType(),
                'tmp_name' => $firmaFile->getTempName(),
                'error'    => $firmaFile->getError(),
                'size'     => $firmaFile->getSize(),
            ];

            // Validar que sea imagen
            if (!$this->uploadService->isImage($firmaArray)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'La firma debe ser una imagen PNG o JPG.');
            }

            // Guardar nueva firma
            $resultFirma = $this->uploadService->saveFirmaConsultor($firmaArray, $id);

            if ($resultFirma['ok']) {
                // Eliminar firma anterior si existe
                if (!empty($consultor['firma_path'])) {
                    $this->uploadService->deleteFile($consultor['firma_path']);
                }

                $data['firma_path'] = $resultFirma['path'];
            } else {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Error al guardar la firma: ' . $resultFirma['error']);
            }
        }

        // Actualizar consultor
        if (!$this->consultorModel->save($data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->consultorModel->errors());
        }

        return redirect()
            ->to('/admin/consultores')
            ->with('success', 'Consultor actualizado exitosamente.');
    }

    /**
     * Elimina un consultor
     *
     * @param int $id
     */
    public function eliminar(int $id)
    {
        $consultor = $this->consultorModel->find($id);

        if (!$consultor) {
            return redirect()
                ->to('/admin/consultores')
                ->with('error', 'Consultor no encontrado.');
        }

        // Verificar si tiene auditorías asignadas
        if ($this->consultorModel->hasAuditorias($id)) {
            return redirect()
                ->to('/admin/consultores')
                ->with('error', 'No se puede eliminar el consultor porque tiene auditorías asignadas.');
        }

        // Eliminar firma si existe
        if (!empty($consultor['firma_path'])) {
            $this->uploadService->deleteFile($consultor['firma_path']);
        }

        // Eliminar consultor
        if (!$this->consultorModel->delete($id)) {
            return redirect()
                ->to('/admin/consultores')
                ->with('error', 'No se pudo eliminar el consultor.');
        }

        return redirect()
            ->to('/admin/consultores')
            ->with('success', 'Consultor eliminado exitosamente.');
    }

    /**
     * Elimina la firma de un consultor
     *
     * @param int $id
     */
    public function eliminarFirma(int $id)
    {
        $consultor = $this->consultorModel->find($id);

        if (!$consultor) {
            return redirect()
                ->back()
                ->with('error', 'Consultor no encontrado.');
        }

        if (empty($consultor['firma_path'])) {
            return redirect()
                ->back()
                ->with('error', 'El consultor no tiene firma registrada.');
        }

        // Eliminar archivo
        $this->uploadService->deleteFile($consultor['firma_path']);

        // Actualizar registro
        $this->consultorModel->update($id, ['firma_path' => null]);

        return redirect()
            ->back()
            ->with('success', 'Firma eliminada exitosamente.');
    }
}
