<?php

namespace App\Models;

use CodeIgniter\Model;

class ContratoModel extends Model
{
    protected $table            = 'contratos_proveedor_cliente';
    protected $primaryKey       = 'id_contrato';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_cliente',
        'id_proveedor',
        'id_consultor',
        'id_usuario_responsable',
        'tipo_auditoria',
        'estado',
        'observaciones',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'id_cliente' => [
            'label'  => 'Cliente',
            'rules'  => 'required|integer|is_not_unique[clientes.id_cliente]',
            'errors' => [
                'required'      => 'El cliente es obligatorio.',
                'integer'       => 'El cliente debe ser un ID válido.',
                'is_not_unique' => 'El cliente seleccionado no existe.',
            ],
        ],
        'id_proveedor' => [
            'label'  => 'Proveedor',
            'rules'  => 'required|integer|is_not_unique[proveedores.id_proveedor]',
            'errors' => [
                'required'      => 'El proveedor es obligatorio.',
                'integer'       => 'El proveedor debe ser un ID válido.',
                'is_not_unique' => 'El proveedor seleccionado no existe.',
            ],
        ],
        'id_consultor' => [
            'label'  => 'Consultor',
            'rules'  => 'required|integer|is_not_unique[consultores.id_consultor]',
            'errors' => [
                'required'      => 'El consultor es obligatorio.',
                'integer'       => 'El consultor debe ser un ID válido.',
                'is_not_unique' => 'El consultor seleccionado no existe.',
            ],
        ],
        'id_usuario_responsable' => [
            'label'  => 'Usuario Responsable',
            'rules'  => 'required|integer|is_not_unique[users.id_users]',
            'errors' => [
                'required'      => 'El usuario responsable es obligatorio.',
                'integer'       => 'El usuario responsable debe ser un ID válido.',
                'is_not_unique' => 'El usuario responsable seleccionado no existe.',
            ],
        ],
        'tipo_auditoria' => [
            'label'  => 'Tipo de Auditoría',
            'rules'  => 'required|in_list[basica,alto_riesgo]',
            'errors' => [
                'required' => 'El tipo de auditoría es obligatorio.',
                'in_list'  => 'El tipo de auditoría debe ser "basica" o "alto_riesgo".',
            ],
        ],
        'estado' => [
            'label'  => 'Estado',
            'rules'  => 'required|in_list[activo,inactivo]',
            'errors' => [
                'required' => 'El estado es obligatorio.',
                'in_list'  => 'El estado debe ser activo o inactivo.',
            ],
        ],
        'observaciones' => [
            'label'  => 'Observaciones',
            'rules'  => 'permit_empty',
        ],
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    public function existsAuditorias(int $id): bool
    {
        return $this->hasAuditorias($id);
    }

    /**
     * Obtiene todas las relaciones cliente-proveedor con información completa
     */
    public function getContratosWithRelations(): array
    {
        return $this->select('contratos_proveedor_cliente.*,
                              clientes.razon_social as cliente_nombre,
                              clientes.email_contacto as cliente_email,
                              proveedores.razon_social as proveedor_nombre,
                              consultores.nombre_completo as consultor_nombre,
                              users.nombre as usuario_responsable_nombre,
                              users.email as usuario_responsable_email')
                    ->join('clientes', 'clientes.id_cliente = contratos_proveedor_cliente.id_cliente')
                    ->join('proveedores', 'proveedores.id_proveedor = contratos_proveedor_cliente.id_proveedor')
                    ->join('consultores', 'consultores.id_consultor = contratos_proveedor_cliente.id_consultor')
                    ->join('users', 'users.id_users = contratos_proveedor_cliente.id_usuario_responsable')
                    ->orderBy('clientes.razon_social', 'ASC')
                    ->orderBy('proveedores.razon_social', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene una relación cliente-proveedor con información completa
     */
    public function getContratoWithRelations(int $id): ?array
    {
        return $this->select('contratos_proveedor_cliente.*,
                              clientes.razon_social as cliente_nombre,
                              clientes.email_contacto as cliente_email,
                              proveedores.razon_social as proveedor_nombre,
                              consultores.nombre_completo as consultor_nombre,
                              users.nombre as usuario_responsable_nombre,
                              users.email as usuario_responsable_email')
                    ->join('clientes', 'clientes.id_cliente = contratos_proveedor_cliente.id_cliente')
                    ->join('proveedores', 'proveedores.id_proveedor = contratos_proveedor_cliente.id_proveedor')
                    ->join('consultores', 'consultores.id_consultor = contratos_proveedor_cliente.id_consultor')
                    ->join('users', 'users.id_users = contratos_proveedor_cliente.id_usuario_responsable')
                    ->find($id);
    }

    /**
     * Obtiene clientes distintos por proveedor con información del contrato
     * Incluye id_contrato y tipo_auditoria para snapshot en auditorias
     */
    public function getClientesByProveedor(int $idProveedor): array
    {
        return $this->select('clientes.id_cliente,
                              clientes.razon_social,
                              clientes.nit,
                              clientes.email_contacto,
                              clientes.telefono_contacto,
                              clientes.direccion,
                              clientes.estado,
                              clientes.created_at,
                              clientes.updated_at,
                              MAX(contratos_proveedor_cliente.id_contrato) as id_contrato,
                              MAX(contratos_proveedor_cliente.tipo_auditoria) as tipo_auditoria')
                    ->join('clientes', 'clientes.id_cliente = contratos_proveedor_cliente.id_cliente')
                    ->where('contratos_proveedor_cliente.id_proveedor', $idProveedor)
                    ->where('contratos_proveedor_cliente.estado', 'activo')
                    ->where('clientes.estado', 'activo')
                    ->groupBy('clientes.id_cliente,
                              clientes.razon_social,
                              clientes.nit,
                              clientes.email_contacto,
                              clientes.telefono_contacto,
                              clientes.direccion,
                              clientes.estado,
                              clientes.created_at,
                              clientes.updated_at')
                    ->findAll();
    }

    /**
     * Verifica si un contrato tiene auditorías asociadas
     */
    public function hasAuditorias(int $id): bool
    {
        $db = \Config\Database::connect();
        $count = $db->table('auditorias')
                    ->where('id_contrato', $id)
                    ->countAllResults();

        return $count > 0;
    }

    /**
     * Obtiene contratos activos
     */
    public function getContratosActivos(): array
    {
        return $this->where('estado', 'activo')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
