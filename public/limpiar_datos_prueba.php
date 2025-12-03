<?php
/**
 * Script para eliminar datos de prueba de la base de datos
 * EJECUTAR UNA SOLA VEZ Y LUEGO ELIMINAR ESTE ARCHIVO
 */

// Cargar el framework
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../app/Config/Boot/production.php';

// Inicializar servicios
$app = \Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();

echo "<pre>";
echo "=== LIMPIEZA DE DATOS DE PRUEBA ===\n\n";

// Lista de planetas (proveedores de prueba)
$planetas = ['JUPITER', 'MARTE', 'SATURNO', 'VENUS', 'MERCURIO', 'NEPTUNO', 'URANO', 'PLUTON', 'TIERRA'];

// Lista de animales (consultores de prueba)
$animales = ['NUTRIA', 'DELFIN', 'BALLENA', 'TIBURON', 'AGUILA', 'LEON', 'TIGRE', 'OSO', 'LOBO', 'ZORRO', 'CONEJO', 'GATO', 'PERRO'];

// Lista de gemas (usuarios de prueba)
$gemas = ['AMATISTA', 'ESMERALDA', 'RUBI', 'ZAFIRO', 'DIAMANTE', 'TOPACIO', 'OPALO', 'PERLA', 'JADE', 'ONIX', 'CUARZO', 'TURQUESA'];

// Deshabilitar foreign key checks temporalmente
$db->query('SET FOREIGN_KEY_CHECKS = 0');

// ============================================
// 1. BUSCAR Y ELIMINAR PROVEEDORES DE PRUEBA
// ============================================
echo "1. BUSCANDO PROVEEDORES DE PRUEBA (Planetas)...\n";

$proveedoresConditions = [];
foreach ($planetas as $planeta) {
    $proveedoresConditions[] = "nombre LIKE '%{$planeta}%'";
}
$whereProveedores = implode(' OR ', $proveedoresConditions);

$proveedoresPrueba = $db->query("SELECT id_proveedor, nombre, nit FROM proveedores WHERE {$whereProveedores}")->getResultArray();

if (count($proveedoresPrueba) > 0) {
    echo "   Encontrados " . count($proveedoresPrueba) . " proveedores de prueba:\n";
    foreach ($proveedoresPrueba as $p) {
        echo "   - [{$p['id_proveedor']}] {$p['nombre']} (NIT: {$p['nit']})\n";
    }

    $idsProveedores = array_column($proveedoresPrueba, 'id_proveedor');
    $idsProveedoresStr = implode(',', $idsProveedores);

    // Obtener auditorías de estos proveedores
    $auditoriasProv = $db->query("SELECT id_auditoria FROM auditorias WHERE id_proveedor IN ({$idsProveedoresStr})")->getResultArray();
    $idsAuditoriasProv = array_column($auditoriasProv, 'id_auditoria');

    if (count($idsAuditoriasProv) > 0) {
        $idsAuditoriasProvStr = implode(',', $idsAuditoriasProv);

        // Eliminar evidencias_cliente
        $db->query("DELETE FROM evidencias_cliente WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
        echo "   - Eliminadas evidencias_cliente\n";

        // Eliminar auditoria_item_cliente
        $db->query("DELETE FROM auditoria_item_cliente WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
        echo "   - Eliminadas auditoria_item_cliente\n";

        // Eliminar auditoria_clientes
        $db->query("DELETE FROM auditoria_clientes WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
        echo "   - Eliminadas auditoria_clientes\n";

        // Eliminar evidencias
        $db->query("DELETE FROM evidencias WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
        echo "   - Eliminadas evidencias\n";

        // Eliminar auditoria_items
        $db->query("DELETE FROM auditoria_items WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
        echo "   - Eliminadas auditoria_items\n";

        // Eliminar auditoria_log
        $db->query("DELETE FROM auditoria_log WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
        echo "   - Eliminados auditoria_log\n";

        // Eliminar auditorías
        $db->query("DELETE FROM auditorias WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
        echo "   - Eliminadas " . count($idsAuditoriasProv) . " auditorías\n";
    }

    // Eliminar usuarios_proveedores
    $db->query("DELETE FROM usuarios_proveedores WHERE id_proveedor IN ({$idsProveedoresStr})");
    echo "   - Eliminados usuarios_proveedores\n";

    // Eliminar contratos_proveedor_cliente
    $db->query("DELETE FROM contratos_proveedor_cliente WHERE id_proveedor IN ({$idsProveedoresStr})");
    echo "   - Eliminados contratos\n";

    // Eliminar proveedores
    $db->query("DELETE FROM proveedores WHERE id_proveedor IN ({$idsProveedoresStr})");
    echo "   - Eliminados " . count($proveedoresPrueba) . " proveedores\n";
} else {
    echo "   No se encontraron proveedores de prueba.\n";
}

echo "\n";

// ============================================
// 2. BUSCAR Y ELIMINAR CONSULTORES DE PRUEBA
// ============================================
echo "2. BUSCANDO CONSULTORES DE PRUEBA (Animales)...\n";

$consultoresConditions = [];
foreach ($animales as $animal) {
    $consultoresConditions[] = "nombre LIKE '%{$animal}%'";
}
$whereConsultores = implode(' OR ', $consultoresConditions);

$consultoresPrueba = $db->query("SELECT id_consultor, nombre, email FROM consultores WHERE {$whereConsultores}")->getResultArray();

if (count($consultoresPrueba) > 0) {
    echo "   Encontrados " . count($consultoresPrueba) . " consultores de prueba:\n";
    foreach ($consultoresPrueba as $c) {
        echo "   - [{$c['id_consultor']}] {$c['nombre']} ({$c['email']})\n";
    }

    $idsConsultores = array_column($consultoresPrueba, 'id_consultor');
    $idsConsultoresStr = implode(',', $idsConsultores);

    // Obtener auditorías de estos consultores
    $auditoriasConsultor = $db->query("SELECT id_auditoria FROM auditorias WHERE id_consultor IN ({$idsConsultoresStr})")->getResultArray();
    $idsAuditoriasConsultor = array_column($auditoriasConsultor, 'id_auditoria');

    if (count($idsAuditoriasConsultor) > 0) {
        $idsAuditoriasConsultorStr = implode(',', $idsAuditoriasConsultor);

        // Eliminar registros relacionados
        $db->query("DELETE FROM evidencias_cliente WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
        $db->query("DELETE FROM auditoria_item_cliente WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
        $db->query("DELETE FROM auditoria_clientes WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
        $db->query("DELETE FROM evidencias WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
        $db->query("DELETE FROM auditoria_items WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
        $db->query("DELETE FROM auditoria_log WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
        $db->query("DELETE FROM auditorias WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
        echo "   - Eliminadas " . count($idsAuditoriasConsultor) . " auditorías relacionadas\n";
    }

    // Eliminar personal_asignado relacionado
    $db->query("DELETE FROM personal_asignado WHERE id_consultor IN ({$idsConsultoresStr})");
    echo "   - Eliminado personal_asignado\n";

    // Eliminar consultores
    $db->query("DELETE FROM consultores WHERE id_consultor IN ({$idsConsultoresStr})");
    echo "   - Eliminados " . count($consultoresPrueba) . " consultores\n";
} else {
    echo "   No se encontraron consultores de prueba.\n";
}

echo "\n";

// ============================================
// 3. BUSCAR Y ELIMINAR USUARIOS DE PRUEBA
// ============================================
echo "3. BUSCANDO USUARIOS DE PRUEBA (Gemas)...\n";

$usersConditions = [];
foreach ($gemas as $gema) {
    $usersConditions[] = "nombre LIKE '%{$gema}%'";
    $usersConditions[] = "username LIKE '%{$gema}%'";
    $usersConditions[] = "email LIKE '%{$gema}%'";
}
$whereUsers = implode(' OR ', $usersConditions);

$usersPrueba = $db->query("SELECT id, nombre, username, email, rol FROM users WHERE {$whereUsers}")->getResultArray();

if (count($usersPrueba) > 0) {
    echo "   Encontrados " . count($usersPrueba) . " usuarios de prueba:\n";
    foreach ($usersPrueba as $u) {
        echo "   - [{$u['id']}] {$u['nombre']} ({$u['username']}) - {$u['rol']}\n";
    }

    $idsUsers = array_column($usersPrueba, 'id');
    $idsUsersStr = implode(',', $idsUsers);

    // Eliminar notificaciones
    $db->query("DELETE FROM notificaciones WHERE id_usuario IN ({$idsUsersStr})");
    echo "   - Eliminadas notificaciones\n";

    // Eliminar usuarios
    $db->query("DELETE FROM users WHERE id IN ({$idsUsersStr})");
    echo "   - Eliminados " . count($usersPrueba) . " usuarios\n";
} else {
    echo "   No se encontraron usuarios de prueba.\n";
}

// Rehabilitar foreign key checks
$db->query('SET FOREIGN_KEY_CHECKS = 1');

echo "\n=== LIMPIEZA COMPLETADA ===\n";
echo "\n⚠️  IMPORTANTE: ELIMINA ESTE ARCHIVO DESPUÉS DE EJECUTARLO\n";
echo "</pre>";
