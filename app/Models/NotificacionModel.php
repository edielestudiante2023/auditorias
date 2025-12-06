<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificacionModel extends Model
{
    protected $table            = 'notificaciones';
    protected $primaryKey       = 'id_notificacion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id_auditoria',
        'tipo',
        'payload_json',
        'fecha_envio',
        'estado_envio',
        'detalle_error',
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';

    /**
     * Obtiene reporte de auditorías cerradas con estado de envío de emails por cliente
     *
     * @param int|null $anio Año a filtrar (null = todos)
     * @return array
     */
    public function getReporteEmailsClientes(?int $anio = null): array
    {
        $db = \Config\Database::connect();

        // Filtro de año
        $filtroAnio = $anio ? "AND YEAR(a.created_at) = {$anio}" : "";

        // Obtener todas las auditorías cerradas con sus clientes
        $auditoriasCerradas = $db->query("
            SELECT
                a.id_auditoria,
                a.updated_at as fecha_cierre,
                p.razon_social as proveedor_nombre,
                p.nit as proveedor_nit,
                c.id_cliente,
                c.razon_social as cliente_nombre,
                c.email_contacto as cliente_email,
                ac.porcentaje_cumplimiento,
                cons.nombre_completo as consultor_nombre
            FROM auditorias a
            INNER JOIN proveedores p ON p.id_proveedor = a.id_proveedor
            INNER JOIN auditoria_clientes ac ON ac.id_auditoria = a.id_auditoria
            INNER JOIN clientes c ON c.id_cliente = ac.id_cliente
            INNER JOIN consultores cons ON cons.id_consultor = a.id_consultor
            WHERE a.estado = 'cerrada'
            {$filtroAnio}
            ORDER BY a.updated_at DESC, p.razon_social, c.razon_social
        ")->getResultArray();

        // Para cada combinación auditoría-cliente, buscar si se envió email
        foreach ($auditoriasCerradas as &$row) {
            $notificacion = $db->table('notificaciones')
                ->where('id_auditoria', $row['id_auditoria'])
                ->where('tipo', 'pdf_cliente')
                ->like('payload_json', '"id_cliente":' . $row['id_cliente'])
                ->orderBy('fecha_envio', 'DESC')
                ->get()
                ->getRowArray();

            if ($notificacion) {
                $row['email_enviado'] = true;
                $row['fecha_envio_email'] = $notificacion['fecha_envio'];
                $row['estado_envio'] = $notificacion['estado_envio'];
                $row['detalle_error'] = $notificacion['detalle_error'];

                // Extraer email destinatario del payload
                $payload = json_decode($notificacion['payload_json'], true);
                $row['email_destinatario'] = $payload['email_destinatario'] ?? null;
            } else {
                $row['email_enviado'] = false;
                $row['fecha_envio_email'] = null;
                $row['estado_envio'] = null;
                $row['detalle_error'] = null;
                $row['email_destinatario'] = null;
            }
        }

        return $auditoriasCerradas;
    }

    /**
     * Obtiene estadísticas de envío de emails
     *
     * @param int|null $anio Año a filtrar (null = todos)
     * @return array
     */
    public function getEstadisticasEnvio(?int $anio = null): array
    {
        $db = \Config\Database::connect();

        // Filtro de año
        $filtroAnio = $anio ? "AND YEAR(a.created_at) = {$anio}" : "";

        // Total de clientes en auditorías cerradas
        $totalClientes = $db->query("
            SELECT COUNT(*) as total
            FROM auditoria_clientes ac
            INNER JOIN auditorias a ON a.id_auditoria = ac.id_auditoria
            WHERE a.estado = 'cerrada'
            {$filtroAnio}
        ")->getRow()->total;

        // Emails enviados exitosamente (estado_envio ENUM: 'ok', 'error', 'pendiente')
        $queryEnviados = "
            SELECT COUNT(*) as total
            FROM notificaciones n
            INNER JOIN auditorias a ON a.id_auditoria = n.id_auditoria
            WHERE n.tipo = 'pdf_cliente'
            AND n.estado_envio = 'ok'
            {$filtroAnio}
        ";
        $enviados = $db->query($queryEnviados)->getRow()->total;

        // Emails fallidos
        $queryFallidos = "
            SELECT COUNT(*) as total
            FROM notificaciones n
            INNER JOIN auditorias a ON a.id_auditoria = n.id_auditoria
            WHERE n.tipo = 'pdf_cliente'
            AND n.estado_envio = 'error'
            {$filtroAnio}
        ";
        $fallidos = $db->query($queryFallidos)->getRow()->total;

        return [
            'total_clientes_cerradas' => $totalClientes,
            'emails_enviados' => $enviados,
            'emails_fallidos' => $fallidos,
            'pendientes' => $totalClientes - $enviados
        ];
    }
}
