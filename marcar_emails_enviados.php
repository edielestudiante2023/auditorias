<?php
/**
 * Script para marcar todos los clientes de auditorías cerradas como "email enviado"
 * Esto es para inicializar el sistema de tracking de emails
 *
 * Ejecutar una sola vez: php marcar_emails_enviados.php
 */

// Cargar el framework de CodeIgniter
require_once __DIR__ . '/vendor/autoload.php';

$paths = new \Config\Paths();
require APPPATH . 'Config/Paths.php';

// Bootstrap CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(FCPATH);

// Minimal bootstrap para acceder a la base de datos
$app = \Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();

echo "=== Marcando emails como enviados para auditorías cerradas ===\n\n";

// Obtener todos los clientes de auditorías cerradas
$clientesAuditorias = $db->query("
    SELECT
        a.id_auditoria,
        c.id_cliente,
        c.razon_social as nombre_cliente,
        c.email_contacto,
        p.razon_social as nombre_proveedor
    FROM auditorias a
    INNER JOIN auditoria_clientes ac ON ac.id_auditoria = a.id_auditoria
    INNER JOIN clientes c ON c.id_cliente = ac.id_cliente
    INNER JOIN proveedores p ON p.id_proveedor = a.id_proveedor
    WHERE a.estado = 'cerrada'
    ORDER BY a.id_auditoria, c.id_cliente
")->getResultArray();

$insertados = 0;
$yaExistentes = 0;

foreach ($clientesAuditorias as $row) {
    // Verificar si ya existe registro
    $existe = $db->table('notificaciones')
        ->where('id_auditoria', $row['id_auditoria'])
        ->where('tipo', 'pdf_cliente')
        ->like('payload_json', '"id_cliente":' . $row['id_cliente'])
        ->countAllResults();

    if ($existe > 0) {
        $yaExistentes++;
        echo "  [EXISTE] Auditoría {$row['id_auditoria']} - Cliente {$row['id_cliente']} ({$row['nombre_cliente']})\n";
        continue;
    }

    // Crear payload
    $payload = [
        'id_auditoria' => (int)$row['id_auditoria'],
        'id_cliente' => (int)$row['id_cliente'],
        'nombre_cliente' => $row['nombre_cliente'],
        'email_destinatario' => $row['email_contacto'] ?? 'no-registrado@example.com'
    ];

    // Insertar registro
    $data = [
        'id_auditoria' => $row['id_auditoria'],
        'tipo' => 'pdf_cliente',
        'payload_json' => json_encode($payload),
        'fecha_envio' => date('Y-m-d H:i:s'),
        'estado_envio' => 'enviado',
        'detalle_error' => null
    ];

    $db->table('notificaciones')->insert($data);
    $insertados++;

    echo "  [OK] Auditoría {$row['id_auditoria']} - Cliente {$row['id_cliente']} ({$row['nombre_cliente']}) -> {$row['nombre_proveedor']}\n";
}

echo "\n=== Resumen ===\n";
echo "Registros insertados: {$insertados}\n";
echo "Ya existentes: {$yaExistentes}\n";
echo "Total procesados: " . count($clientesAuditorias) . "\n";
echo "\nListo! El reporte de emails ahora mostrará todos como 'Enviados'.\n";
