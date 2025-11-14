<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonalAsignadoModel extends Model
{
    protected $table            = 'personal_asignado';
    protected $primaryKey       = 'id_personal';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_proveedor',
        'id_cliente',
        'tipo_documento',
        'numero_documento',
        'nombres',
        'apellidos',
        'cargo',
        'fecha_ingreso',
        'estado',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'id_proveedor'      => 'required|integer',
        'id_cliente'        => 'required|integer',
        'tipo_documento'    => 'required|in_list[CC,CE,PA,TI]',
        'numero_documento'  => 'required|max_length[20]',
        'nombres'           => 'required|max_length[100]',
        'apellidos'         => 'required|max_length[100]',
        'cargo'             => 'required|max_length[100]',
        'fecha_ingreso'     => 'required|valid_date',
        'estado'            => 'in_list[activo,inactivo]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
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
     * Obtiene el personal asignado de un proveedor a un cliente
     */
    public function getPersonalByProveedorCliente(int $idProveedor, int $idCliente): array
    {
        return $this->where('id_proveedor', $idProveedor)
                    ->where('id_cliente', $idCliente)
                    ->orderBy('apellidos', 'ASC')
                    ->orderBy('nombres', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene todo el personal activo de un proveedor
     */
    public function getPersonalActivoByProveedor(int $idProveedor): array
    {
        return $this->where('id_proveedor', $idProveedor)
                    ->where('estado', 'activo')
                    ->orderBy('apellidos', 'ASC')
                    ->orderBy('nombres', 'ASC')
                    ->findAll();
    }

    /**
     * Cuenta el personal asignado a un cliente específico
     */
    public function countPersonalByCliente(int $idProveedor, int $idCliente): int
    {
        return $this->where('id_proveedor', $idProveedor)
                    ->where('id_cliente', $idCliente)
                    ->where('estado', 'activo')
                    ->countAllResults();
    }

    /**
     * Verifica si un proveedor tiene personal registrado para un cliente
     */
    public function tienePersonalRegistrado(int $idProveedor, int $idCliente): bool
    {
        return $this->countPersonalByCliente($idProveedor, $idCliente) > 0;
    }

    /**
     * Obtiene el personal con información del cliente
     */
    public function getPersonalConCliente(int $idProveedor): array
    {
        return $this->select('personal_asignado.*, clientes.razon_social, clientes.nit')
                    ->join('clientes', 'clientes.id_cliente = personal_asignado.id_cliente')
                    ->where('personal_asignado.id_proveedor', $idProveedor)
                    ->orderBy('clientes.razon_social', 'ASC')
                    ->orderBy('personal_asignado.apellidos', 'ASC')
                    ->findAll();
    }
}
