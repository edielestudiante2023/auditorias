<?php

namespace App\Controllers\Proveedor;

use App\Controllers\BaseController;
use App\Models\PersonalAsignadoModel;
use App\Models\ClienteModel;

class PersonalController extends BaseController
{
    protected $personalModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->personalModel = new PersonalAsignadoModel();
        $this->clienteModel = new ClienteModel();
        helper('auth');
    }

    /**
     * Vista principal: lista de clientes y su personal asignado
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // Obtener el id_proveedor desde los contratos del usuario responsable
        $contrato = $db->table('contratos_proveedor_cliente')
            ->select('id_proveedor')
            ->where('id_usuario_responsable', userId())
            ->where('estado', 'activo')
            ->get()
            ->getRowArray();

        if (!$contrato) {
            return redirect()->to('/proveedor/dashboard')->with('error', 'No se encontró un contrato activo para este usuario');
        }

        $idProveedor = $contrato['id_proveedor'];

        // Obtener clientes del proveedor con conteo de personal
        $clientes = $db->query("
            SELECT DISTINCT c.*,
                   COUNT(pa.id_personal) as total_personal
            FROM clientes c
            JOIN contratos_proveedor_cliente cpc ON cpc.id_cliente = c.id_cliente
            LEFT JOIN personal_asignado pa ON pa.id_cliente = c.id_cliente
                                            AND pa.id_proveedor = ?
                                            AND pa.estado = 'activo'
            WHERE cpc.id_proveedor = ?
              AND cpc.id_usuario_responsable = ?
              AND cpc.estado = 'activo'
            GROUP BY c.id_cliente
            ORDER BY c.razon_social ASC
        ", [$idProveedor, $idProveedor, userId()])->getResultArray();

        // Obtener todo el personal del proveedor con información del cliente
        $todoPersonal = $this->personalModel->getPersonalConCliente($idProveedor);

        return view('proveedor/personal/index', [
            'title' => 'Gestión de Personal Asignado',
            'clientes' => $clientes,
            'todoPersonal' => $todoPersonal,
        ]);
    }

    /**
     * Vista para gestionar personal de un cliente específico
     */
    public function gestionarCliente($idCliente = null)
    {
        if (!$idCliente) {
            return redirect()->back()->with('error', 'Cliente no especificado');
        }

        $db = \Config\Database::connect();

        // Verificar que el cliente pertenece al usuario responsable
        $contrato = $db->table('contratos_proveedor_cliente')
            ->where('id_usuario_responsable', userId())
            ->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->get()
            ->getRowArray();

        if (!$contrato) {
            return redirect()->back()->with('error', 'No tienes acceso a este cliente');
        }

        $idProveedor = $contrato['id_proveedor'];
        $cliente = $this->clienteModel->find($idCliente);
        $personal = $this->personalModel->getPersonalByProveedorCliente($idProveedor, $idCliente);

        return view('proveedor/personal/gestionar', [
            'title' => 'Personal Asignado - ' . $cliente['razon_social'],
            'cliente' => $cliente,
            'personal' => $personal,
        ]);
    }

    /**
     * Guarda o actualiza personal
     */
    public function guardar($idCliente = null)
    {
        if (!$idCliente) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cliente no especificado'
            ]);
        }

        $db = \Config\Database::connect();

        // Obtener id_proveedor del usuario
        $contrato = $db->table('contratos_proveedor_cliente')
            ->select('id_proveedor')
            ->where('id_usuario_responsable', userId())
            ->where('id_cliente', $idCliente)
            ->where('estado', 'activo')
            ->get()
            ->getRowArray();

        if (!$contrato) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes acceso a este cliente'
            ]);
        }

        $idProveedor = $contrato['id_proveedor'];

        log_message('debug', 'Personal Controller - Guardar: idProveedor=' . $idProveedor . ', idCliente=' . $idCliente);

        // Validar datos
        $rules = [
            'tipo_documento'   => 'required|in_list[CC,CE,PA,TI]',
            'numero_documento' => 'required|max_length[20]',
            'nombres'          => 'required|max_length[100]',
            'apellidos'        => 'required|max_length[100]',
            'cargo'            => 'required|max_length[100]',
            'fecha_ingreso'    => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            log_message('error', 'Validación fallida: ' . json_encode($this->validator->getErrors()));
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'id_proveedor'     => $idProveedor,
            'id_cliente'       => $idCliente,
            'tipo_documento'   => $this->request->getPost('tipo_documento'),
            'numero_documento' => $this->request->getPost('numero_documento'),
            'nombres'          => $this->request->getPost('nombres'),
            'apellidos'        => $this->request->getPost('apellidos'),
            'cargo'            => $this->request->getPost('cargo'),
            'fecha_ingreso'    => $this->request->getPost('fecha_ingreso'),
            'estado'           => 'activo',
        ];

        log_message('debug', 'Datos a guardar: ' . json_encode($data));

        $idPersonal = $this->request->getPost('id_personal');

        try {
            if ($idPersonal) {
                // Actualizar
                log_message('debug', 'Actualizando personal ID: ' . $idPersonal);
                $result = $this->personalModel->update($idPersonal, $data);
                log_message('debug', 'Resultado update: ' . ($result ? 'true' : 'false'));
                $message = 'Personal actualizado exitosamente';
            } else {
                // Insertar
                log_message('debug', 'Insertando nuevo personal');
                $insertId = $this->personalModel->insert($data);
                log_message('debug', 'ID insertado: ' . $insertId);
                $message = 'Personal registrado exitosamente';
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error al guardar personal: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene datos de un personal para editar
     */
    public function obtener($idPersonal = null)
    {
        if (!$idPersonal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID no especificado'
            ]);
        }

        $personal = $this->personalModel->find($idPersonal);

        if (!$personal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Personal no encontrado'
            ]);
        }

        // Verificar que el usuario tiene acceso al cliente de este personal
        $db = \Config\Database::connect();
        $contrato = $db->table('contratos_proveedor_cliente')
            ->where('id_usuario_responsable', userId())
            ->where('id_proveedor', $personal['id_proveedor'])
            ->where('id_cliente', $personal['id_cliente'])
            ->where('estado', 'activo')
            ->get()
            ->getRowArray();

        if (!$contrato) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes acceso a este personal'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $personal
        ]);
    }

    /**
     * Cambia el estado del personal (activo/inactivo)
     */
    public function cambiarEstado($idPersonal = null)
    {
        if (!$idPersonal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID no especificado'
            ]);
        }

        $personal = $this->personalModel->find($idPersonal);

        if (!$personal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Personal no encontrado'
            ]);
        }

        // Verificar acceso
        $db = \Config\Database::connect();
        $contrato = $db->table('contratos_proveedor_cliente')
            ->where('id_usuario_responsable', userId())
            ->where('id_proveedor', $personal['id_proveedor'])
            ->where('id_cliente', $personal['id_cliente'])
            ->where('estado', 'activo')
            ->get()
            ->getRowArray();

        if (!$contrato) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes acceso a este personal'
            ]);
        }

        $nuevoEstado = $personal['estado'] === 'activo' ? 'inactivo' : 'activo';

        try {
            $this->personalModel->update($idPersonal, ['estado' => $nuevoEstado]);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'nuevo_estado' => $nuevoEstado
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al cambiar estado: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Elimina un personal
     */
    public function eliminar($idPersonal = null)
    {
        if (!$idPersonal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID no especificado'
            ]);
        }

        $personal = $this->personalModel->find($idPersonal);

        if (!$personal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Personal no encontrado'
            ]);
        }

        // Verificar acceso
        $db = \Config\Database::connect();
        $contrato = $db->table('contratos_proveedor_cliente')
            ->where('id_usuario_responsable', userId())
            ->where('id_proveedor', $personal['id_proveedor'])
            ->where('id_cliente', $personal['id_cliente'])
            ->where('estado', 'activo')
            ->get()
            ->getRowArray();

        if (!$contrato) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No tienes acceso a este personal'
            ]);
        }

        try {
            $this->personalModel->delete($idPersonal);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Personal eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }
}
