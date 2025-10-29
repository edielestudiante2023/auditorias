<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemsBancoModel extends Model
{
    protected $table            = 'items_banco';
    protected $primaryKey       = 'id_item';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'codigo_item',
        'titulo',
        'descripcion',
        'alcance',
        'aplica_alto_riesgo',
        'orden',
        'activo',
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id_item' => [
            'label'  => 'ID',
            'rules'  => 'permit_empty|integer',
        ],
        'codigo_item' => [
            'label'  => 'Código',
            'rules'  => 'required|max_length[10]|is_unique[items_banco.codigo_item,id_item,{id_item}]',
            'errors' => [
                'required'   => 'El código es obligatorio.',
                'max_length' => 'El código no puede exceder 10 caracteres.',
                'is_unique'  => 'Este código ya está en uso.',
            ],
        ],
        'titulo' => [
            'label'  => 'Título',
            'rules'  => 'required|max_length[255]',
            'errors' => [
                'required'   => 'El título es obligatorio.',
                'max_length' => 'El título no puede exceder 255 caracteres.',
            ],
        ],
        'descripcion' => [
            'label'  => 'Descripción',
            'rules'  => 'permit_empty|max_length[5000]',
            'errors' => [
                'max_length' => 'La descripción no puede exceder 5000 caracteres.',
            ],
        ],
        'alcance' => [
            'label'  => 'Alcance',
            'rules'  => 'required|in_list[global,por_cliente]',
            'errors' => [
                'required' => 'El alcance es obligatorio.',
                'in_list'  => 'El alcance debe ser "global" o "por_cliente".',
            ],
        ],
        'aplica_alto_riesgo' => [
            'label'  => 'Aplica Alto Riesgo',
            'rules'  => 'permit_empty|in_list[0,1]',
            'errors' => [
                'in_list' => 'El valor de alto riesgo debe ser 0 o 1.',
            ],
        ],
        'orden' => [
            'label'  => 'Orden',
            'rules'  => 'permit_empty|integer|greater_than_equal_to[0]',
            'errors' => [
                'integer'                => 'El orden debe ser un número entero.',
                'greater_than_equal_to'  => 'El orden debe ser mayor o igual a 0.',
            ],
        ],
        'activo' => [
            'label'  => 'Activo',
            'rules'  => 'permit_empty|in_list[0,1]',
            'errors' => [
                'in_list' => 'El valor de activo debe ser 0 o 1.',
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
     * Obtiene todos los items ordenados por el campo 'orden'
     *
     * @return array
     */
    public function getAllOrdered(): array
    {
        return $this->orderBy('orden', 'ASC')
                    ->orderBy('id_item', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene solo items activos ordenados
     *
     * @return array
     */
    public function getActiveOrdered(): array
    {
        return $this->where('activo', 1)
                    ->orderBy('orden', 'ASC')
                    ->orderBy('id_item', 'ASC')
                    ->findAll();
    }

    /**
     * Obtiene el siguiente número de orden disponible
     *
     * @return int
     */
    public function getNextOrder(): int
    {
        $result = $this->selectMax('orden')->first();
        return ($result['orden'] ?? 0) + 1;
    }

    /**
     * Alterna el estado activo/inactivo de un item
     *
     * @param int $id
     * @return bool
     */
    public function toggleActive(int $id): bool
    {
        $item = $this->find($id);

        if (!$item) {
            return false;
        }

        $newStatus = $item['activo'] == 1 ? 0 : 1;

        return $this->update($id, ['activo' => $newStatus]);
    }

    /**
     * Actualiza el orden de múltiples items
     *
     * @param array $ordenData Array con formato ['id_item' => nuevo_orden, ...]
     * @return bool
     */
    public function updateOrden(array $ordenData): bool
    {
        $this->db->transStart();

        foreach ($ordenData as $id => $orden) {
            $this->update($id, ['orden' => $orden]);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
