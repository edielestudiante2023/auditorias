<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id_cliente';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'razon_social',
        'nit',
        'email_contacto',
        'telefono_contacto',
        'direccion',
        'estado',
        'logo_cliente_path',
        'created_at',
        'updated_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
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
            'rules'  => 'required|max_length[50]',
            'errors' => [
                'required'   => 'El NIT es obligatorio.',
                'max_length' => 'El NIT no puede exceder 50 caracteres.',
            ],
        ],
        'email_contacto' => [
            'label'  => 'Email de Contacto',
            'rules'  => 'permit_empty|valid_email|max_length[150]',
            'errors' => [
                'valid_email' => 'El email de contacto debe ser válido.',
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
        'direccion' => [
            'label'  => 'Dirección',
            'rules'  => 'permit_empty|max_length[200]',
            'errors' => [
                'max_length' => 'La dirección no puede exceder 200 caracteres.',
            ],
        ],
        'estado' => [
            'label'  => 'Estado',
            'rules'  => 'permit_empty|in_list[activo,inactivo]',
            'errors' => [
                'in_list' => 'El estado debe ser activo o inactivo.',
            ],
        ],
        'logo_cliente_path' => [
            'label'  => 'Logo del Cliente',
            'rules'  => 'permit_empty|max_length[500]',
            'errors' => [
                'max_length' => 'La ruta del logo no puede exceder 500 caracteres.',
            ],
        ],
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Obtiene clientes activos ordenados por razón social
     *
     * @return array
     */
    public function getClientesActivos(): array
    {
        return $this->where('estado', 'activo')
                    ->orderBy('razon_social', 'ASC')
                    ->findAll();
    }

    /**
     * Alterna el estado activo/inactivo de un cliente
     *
     * @param int $id
     * @return bool
     */
    public function toggleEstado(int $id): bool
    {
        $cliente = $this->find($id);

        if (!$cliente) {
            return false;
        }

        $nuevoEstado = $cliente['estado'] === 'activo' ? 'inactivo' : 'activo';

        return $this->update($id, ['estado' => $nuevoEstado]);
    }

    /**
     * Verifica si un cliente tiene contratos
     *
     * @param int $id
     * @return bool
     */
    public function hasContratos(int $id): bool
    {
        $db = \Config\Database::connect();
        $count = $db->table('contratos_proveedor_cliente')
                    ->where('id_cliente', $id)
                    ->countAllResults();

        return $count > 0;
    }

    /**
     * Verifica si un cliente está en auditorías
     *
     * @param int $id
     * @return bool
     */
    public function hasAuditorias(int $id): bool
    {
        $db = \Config\Database::connect();
        $count = $db->table('auditoria_clientes')
                    ->where('id_cliente', $id)
                    ->countAllResults();

        return $count > 0;
    }
}
