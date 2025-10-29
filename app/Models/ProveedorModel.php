<?php

namespace App\Models;

use CodeIgniter\Model;

class ProveedorModel extends Model
{
    protected $table            = 'proveedores';
    protected $primaryKey       = 'id_proveedor';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'razon_social',
        'nit',
        'email_contacto',
        'telefono_contacto',
        'id_users',
        'observaciones',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'razon_social' => [
            'label'  => 'Razón Social',
            'rules'  => 'required|max_length[200]',
            'errors' => [
                'required'   => 'La razón social es obligatoria.',
                'max_length' => 'La razón social no puede exceder 200 caracteres.',
            ],
        ],
        'nit' => [
            'label'  => 'NIT',
            'rules'  => 'required|max_length[50]|is_unique[proveedores.nit,id_proveedor,{id_proveedor}]',
            'errors' => [
                'required'   => 'El NIT es obligatorio.',
                'max_length' => 'El NIT no puede exceder 50 caracteres.',
                'is_unique'  => 'Este NIT ya está registrado.',
            ],
        ],
        'email_contacto' => [
            'label'  => 'Email de Contacto',
            'rules'  => 'permit_empty|valid_email|max_length[150]',
            'errors' => [
                'valid_email' => 'El email debe ser válido.',
                'max_length'  => 'El email no puede exceder 150 caracteres.',
            ],
        ],
        'telefono_contacto' => [
            'label'  => 'Teléfono de Contacto',
            'rules'  => 'permit_empty|max_length[50]',
            'errors' => [
                'max_length' => 'El teléfono no puede exceder 50 caracteres.',
            ],
        ],
        'id_users' => [
            'label'  => 'Usuario',
            'rules'  => 'permit_empty|integer',
            'errors' => [
                'integer' => 'El usuario debe ser un ID válido.',
            ],
        ],
        'observaciones' => [
            'label'  => 'Observaciones',
            'rules'  => 'permit_empty|max_length[500]',
            'errors' => [
                'max_length' => 'Las observaciones no pueden exceder 500 caracteres.',
            ],
        ],
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    /**
     * Obtiene todos los proveedores con información del usuario si existe
     */
    public function getProveedoresWithUsers(): array
    {
        return $this->select('proveedores.*, users.email, users.nombre as nombre_usuario, users.estado as estado_usuario')
                    ->join('users', 'users.id_users = proveedores.id_users', 'left')
                    ->orderBy('proveedores.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Obtiene un proveedor con información del usuario
     */
    public function getProveedorWithUser(int $id): ?array
    {
        return $this->select('proveedores.*, users.email, users.nombre as nombre_usuario, users.estado as estado_usuario')
                    ->join('users', 'users.id_users = proveedores.id_users', 'left')
                    ->find($id);
    }

    /**
     * Obtiene proveedores activos ordenados por razón social
     */
    public function getProveedoresActivos(): array
    {
        return $this->orderBy('razon_social', 'ASC')->findAll();
    }

    /**
     * Obtiene proveedor por usuario
     */
    public function getProveedorByUser(int $idUsers): ?array
    {
        return $this->where('id_users', $idUsers)->first();
    }

    /**
     * Verifica si un proveedor tiene contratos asociados
     */
    public function hasContratos(int $id): bool
    {
        $db = \Config\Database::connect();
        $count = $db->table('contratos')
                    ->where('id_proveedor', $id)
                    ->countAllResults();

        return $count > 0;
    }

    /**
     * Verifica si un usuario ya está asignado a un proveedor
     */
    public function userHasProveedor(int $idUsers, ?int $excludeId = null): bool
    {
        $builder = $this->where('id_users', $idUsers);

        if ($excludeId) {
            $builder->where('id_proveedor !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }
}
