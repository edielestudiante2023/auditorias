<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuariosProveedoresModel extends Model
{
    protected $table            = 'usuarios_proveedores';
    protected $primaryKey       = 'id_usuario_proveedor';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_users',
        'id_proveedor',
        'activo',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene todos los proveedores vinculados a un usuario
     */
    public function getProveedoresByUsuario(int $idUsers): array
    {
        return $this->select('usuarios_proveedores.*, proveedores.razon_social, proveedores.nit')
            ->join('proveedores', 'proveedores.id_proveedor = usuarios_proveedores.id_proveedor')
            ->where('usuarios_proveedores.id_users', $idUsers)
            ->where('usuarios_proveedores.activo', 1)
            ->findAll();
    }

    /**
     * Obtiene todos los usuarios vinculados a un proveedor
     */
    public function getUsuariosByProveedor(int $idProveedor): array
    {
        return $this->select('usuarios_proveedores.*, users.nombre, users.email')
            ->join('users', 'users.id_users = usuarios_proveedores.id_users')
            ->where('usuarios_proveedores.id_proveedor', $idProveedor)
            ->where('usuarios_proveedores.activo', 1)
            ->findAll();
    }

    /**
     * Verifica si un usuario está vinculado a un proveedor específico
     */
    public function isVinculado(int $idUsers, int $idProveedor): bool
    {
        return $this->where('id_users', $idUsers)
            ->where('id_proveedor', $idProveedor)
            ->where('activo', 1)
            ->countAllResults() > 0;
    }

    /**
     * Desvincular todos los proveedores de un usuario
     */
    public function desvincularTodos(int $idUsers): void
    {
        $this->where('id_users', $idUsers)->delete();
    }

    /**
     * Vincular múltiples proveedores a un usuario
     */
    public function vincularProveedores(int $idUsers, array $idsProveedores): bool
    {
        // Primero eliminar vinculaciones existentes
        $this->desvincularTodos($idUsers);

        // Insertar nuevas vinculaciones
        $data = [];
        foreach ($idsProveedores as $idProveedor) {
            $data[] = [
                'id_users' => $idUsers,
                'id_proveedor' => $idProveedor,
                'activo' => 1,
            ];
        }

        if (!empty($data)) {
            return $this->insertBatch($data);
        }

        return true;
    }
}
