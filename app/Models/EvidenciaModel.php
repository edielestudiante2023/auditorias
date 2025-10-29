<?php

namespace App\Models;

use CodeIgniter\Model;

class EvidenciaModel extends Model
{
    protected $table            = 'evidencias';
    protected $primaryKey       = 'id_evidencia';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_auditoria_item',
        'nombre_archivo_original',
        'ruta_archivo',
        'tipo_mime',
        'tamanio_bytes',
        'hash_archivo',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    public function getEvidenciasByItem(int $idAuditoriaItem): array
    {
        return $this->where('id_auditoria_item', $idAuditoriaItem)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
