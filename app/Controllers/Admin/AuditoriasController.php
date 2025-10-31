<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditoriaModel;

class AuditoriasController extends BaseController
{
    protected $auditoriaModel;

    public function __construct()
    {
        $this->auditoriaModel = new AuditoriaModel();
    }

    /**
     * Vista 1: Auditorías completadas por proveedores (esperando revisión del consultor)
     * Equivalente a /proveedor/auditorias/completadas pero sin filtro de usuario
     */
    public function completadasProveedores()
    {
        $db = \Config\Database::connect();

        // Todas las auditorías cerradas, sin filtro de usuario
        $auditorias = $db->query("
            SELECT DISTINCT a.*,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre,
                   u.nombre as usuario_responsable_nombre,
                   u.email as usuario_responsable_email
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            LEFT JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            LEFT JOIN users u ON u.id_users = cpc.id_usuario_responsable
            WHERE a.estado = 'cerrada'
            ORDER BY a.updated_at DESC
        ")->getResultArray();

        return view('admin/auditorias/completadas_proveedores', [
            'title' => 'Auditorías Completadas por Proveedores',
            'auditorias' => $auditorias,
        ]);
    }

    /**
     * Vista 2: Auditorías pendientes de diligenciamiento por proveedores
     * Equivalente a /proveedor/auditorias pero sin filtro de usuario
     */
    public function pendientesProveedores()
    {
        $db = \Config\Database::connect();

        // Todas las auditorías asignadas a proveedores, sin filtro de usuario
        $auditorias = $db->query("
            SELECT DISTINCT a.*,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre,
                   u.nombre as usuario_responsable_nombre,
                   u.email as usuario_responsable_email
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            LEFT JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            LEFT JOIN users u ON u.id_users = cpc.id_usuario_responsable
            WHERE a.estado IN ('borrador', 'asignada', 'en_progreso')
            ORDER BY a.created_at DESC
        ")->getResultArray();

        return view('admin/auditorias/pendientes_proveedores', [
            'title' => 'Auditorías Pendientes - Proveedores',
            'auditorias' => $auditorias,
        ]);
    }

    /**
     * Vista 3: Auditorías en revisión por consultores
     * Equivalente a /consultor/auditorias pero sin filtro de usuario
     */
    public function revisionConsultores()
    {
        $db = \Config\Database::connect();

        // Todas las auditorías de todos los consultores, sin filtro
        $auditorias = $db->query("
            SELECT a.*,
                   p.razon_social as proveedor_nombre,
                   p.nit as proveedor_nit,
                   c.nombre_completo as consultor_nombre,
                   u.nombre as usuario_responsable_nombre,
                   u.email as usuario_responsable_email,
                   COUNT(DISTINCT ai.id_auditoria_item) as total_items,
                   COUNT(DISTINCT CASE WHEN ai.calificacion IS NOT NULL THEN ai.id_auditoria_item END) as items_calificados
            FROM auditorias a
            JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            JOIN consultores c ON c.id_consultor = a.id_consultor
            LEFT JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
            LEFT JOIN users u ON u.id_users = cpc.id_usuario_responsable
            LEFT JOIN auditoria_items ai ON ai.id_auditoria = a.id_auditoria
            WHERE a.estado IN ('borrador', 'asignada', 'en_progreso', 'en_revision_consultor', 'cerrada')
            GROUP BY a.id_auditoria, p.razon_social, p.nit, c.nombre_completo, u.nombre, u.email
            ORDER BY a.created_at DESC
        ")->getResultArray();

        return view('admin/auditorias/revision_consultores', [
            'title' => 'Auditorías - Revisión Consultores',
            'auditorias' => $auditorias,
        ]);
    }
}
