<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsultorModel extends Model
{
    protected $table            = 'consultores';
    protected $primaryKey       = 'id_consultor';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_users',
        'nombre_completo',
        'tipo_documento',
        'numero_documento',
        'licencia_sst',
        'firma_path',
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
        'id_users' => [
            'label'  => 'Usuario',
            'rules'  => 'required|integer|is_not_unique[users.id_users]',
            'errors' => [
                'required'      => 'El usuario es obligatorio.',
                'integer'       => 'El usuario debe ser un ID válido.',
                'is_not_unique' => 'El usuario seleccionado no existe.',
            ],
        ],
        'nombre_completo' => [
            'label'  => 'Nombre Completo',
            'rules'  => 'required|max_length[150]',
            'errors' => [
                'required'   => 'El nombre completo es obligatorio.',
                'max_length' => 'El nombre no puede exceder 150 caracteres.',
            ],
        ],
        'tipo_documento' => [
            'label'  => 'Tipo de Documento',
            'rules'  => 'required|in_list[CC,CE,PAS,OTRO]',
            'errors' => [
                'required' => 'El tipo de documento es obligatorio.',
                'in_list'  => 'El tipo de documento debe ser CC, CE, PAS u OTRO.',
            ],
        ],
        'numero_documento' => [
            'label'  => 'Número de Documento',
            'rules'  => 'required|max_length[30]|is_unique[consultores.numero_documento,id_consultor,{id_consultor}]',
            'errors' => [
                'required'   => 'El número de documento es obligatorio.',
                'max_length' => 'El número de documento no puede exceder 30 caracteres.',
                'is_unique'  => 'Este número de documento ya está registrado.',
            ],
        ],
        'licencia_sst' => [
            'label'  => 'Licencia SST',
            'rules'  => 'permit_empty|max_length[100]',
            'errors' => [
                'max_length' => 'La licencia SST no puede exceder 100 caracteres.',
            ],
        ],
        'firma_path' => [
            'label'  => 'Firma Digital',
            'rules'  => 'permit_empty|max_length[500]',
            'errors' => [
                'max_length' => 'La ruta de la firma no puede exceder 500 caracteres.',
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
     * Obtiene todos los consultores con información del usuario relacionado
     *
     * @return array
     */
    public function getConsultoresWithUsers(): array
    {
        return $this->select('consultores.*, users.email, users.nombre as nombre_usuario, users.estado as estado_usuario')
                    ->join('users', 'users.id_users = consultores.id_users')
                    ->orderBy('consultores.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Obtiene un consultor con información del usuario
     *
     * @param int $id
     * @return array|null
     */
    public function getConsultorWithUser(int $id): ?array
    {
        return $this->select('consultores.*, users.email, users.nombre as nombre_usuario, users.estado as estado_usuario')
                    ->join('users', 'users.id_users = consultores.id_users')
                    ->find($id);
    }

    /**
     * Verifica si un usuario ya tiene un registro de consultor
     *
     * @param int $idUsers
     * @param int|null $excludeId ID del consultor a excluir (para edición)
     * @return bool
     */
    public function userHasConsultor(int $idUsers, ?int $excludeId = null): bool
    {
        $builder = $this->where('id_users', $idUsers);

        if ($excludeId) {
            $builder->where('id_consultor !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Obtiene consultores activos (usuarios activos)
     *
     * @return array
     */
    public function getConsultoresActivos(): array
    {
        return $this->select('consultores.*, users.email, users.nombre as nombre_usuario')
                    ->join('users', 'users.id_users = consultores.id_users')
                    ->where('users.estado', 'activo')
                    ->orderBy('consultores.nombre_completo', 'ASC')
                    ->findAll();
    }

    /**
     * Verifica si un consultor tiene auditorías asignadas
     *
     * @param int $id
     * @return bool
     */
    public function hasAuditorias(int $id): bool
    {
        $db = \Config\Database::connect();
        $count = $db->table('auditorias')
                    ->where('id_consultor', $id)
                    ->countAllResults();

        return $count > 0;
    }
}
