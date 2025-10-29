<?php

/**
 * Script de prueba del flujo Admin
 *
 * Este script verifica:
 * 1. Inicio de sesión como admin
 * 2. Dashboard con contadores > 0
 * 3. Acceso a cada módulo desde los botones
 * 4. Carga de índices sin error
 * 5. Creación y edición de registros
 *
 * Uso: php test_admin_workflow.php
 */

// Configuración
$baseUrl = 'http://localhost/auditorias';
$adminEmail = 'superadmin@cycloidtalent.com';
$adminPassword = 'Admin123*';

// Colores para consola
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

echo "\n";
echo COLOR_BLUE . "=== Test de Flujo Admin ===" . COLOR_RESET . "\n\n";

// Inicializar cURL con manejo de cookies
$cookieFile = tempnam(sys_get_temp_dir(), 'cookie');
$csrfToken = null;

/**
 * Realiza una petición HTTP
 */
function request($method, $url, $data = null, $headers = [])
{
    global $cookieFile, $csrfToken;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => $response,
    ];
}

/**
 * Extrae el token CSRF de una respuesta HTML
 */
function extractCsrfToken($html)
{
    if (preg_match('/name=["\']csrf_test_name["\'] value=["\']([^"\']+)["\']/', $html, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Verifica si una respuesta contiene un texto
 */
function assertContains($response, $needle, $message)
{
    if (strpos($response['body'], $needle) !== false) {
        echo COLOR_GREEN . "✓ " . COLOR_RESET . $message . "\n";
        return true;
    } else {
        echo COLOR_RED . "✗ " . COLOR_RESET . $message . "\n";
        return false;
    }
}

/**
 * Verifica el código HTTP
 */
function assertHttpCode($response, $expected, $message)
{
    if ($response['code'] === $expected) {
        echo COLOR_GREEN . "✓ " . COLOR_RESET . $message . " (HTTP {$response['code']})\n";
        return true;
    } else {
        echo COLOR_RED . "✗ " . COLOR_RESET . $message . " (HTTP {$response['code']}, esperado {$expected})\n";
        return false;
    }
}

// =====================================================
// TEST 1: Inicio de sesión como admin
// =====================================================
echo COLOR_YELLOW . "→ Test 1: Inicio de sesión como admin" . COLOR_RESET . "\n";

$loginPage = request('GET', "$baseUrl/login");
$csrfToken = extractCsrfToken($loginPage['body']);

if (!$csrfToken) {
    echo COLOR_RED . "✗ No se pudo obtener el token CSRF\n" . COLOR_RESET;
    exit(1);
}

$loginData = [
    'email' => $adminEmail,
    'password' => $adminPassword,
    'csrf_test_name' => $csrfToken,
];

$loginResponse = request('POST', "$baseUrl/login", $loginData);
assertHttpCode($loginResponse, 200, "Login exitoso");
assertContains($loginResponse, 'Panel de Administración', "Redirección a dashboard");

// =====================================================
// TEST 2: Dashboard con contadores > 0
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Test 2: Dashboard con contadores > 0" . COLOR_RESET . "\n";

$dashboard = request('GET', "$baseUrl/admin/dashboard");
assertHttpCode($dashboard, 200, "Dashboard cargado");
assertContains($dashboard, 'Ítems del Banco', "Contiene módulo Ítems");
assertContains($dashboard, 'Clientes', "Contiene módulo Clientes");
assertContains($dashboard, 'Proveedores', "Contiene módulo Proveedores");
assertContains($dashboard, 'Consultores', "Contiene módulo Consultores");
assertContains($dashboard, 'Contratos', "Contiene módulo Contratos");
assertContains($dashboard, 'Usuarios', "Contiene módulo Usuarios");

// Verificar que hay contadores > 0
$hasCounters = preg_match_all('/<span class="badge bg-[^"]+ position-absolute[^>]*>(\d+)<\/span>/', $dashboard['body'], $matches);
if ($hasCounters) {
    $counters = array_map('intval', $matches[1]);
    $hasPositive = count(array_filter($counters, fn($c) => $c > 0)) > 0;
    if ($hasPositive) {
        echo COLOR_GREEN . "✓ " . COLOR_RESET . "Contadores positivos detectados\n";
    } else {
        echo COLOR_YELLOW . "⚠ " . COLOR_RESET . "Contadores en cero (ejecuta: php spark db:seed AdminQuickSeed)\n";
    }
}

// =====================================================
// TEST 3: Acceso a módulos desde botones
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Test 3: Acceso a módulos" . COLOR_RESET . "\n";

$modules = [
    'clientes' => "$baseUrl/admin/clientes",
    'proveedores' => "$baseUrl/admin/proveedores",
    'consultores' => "$baseUrl/admin/consultores",
    'contratos' => "$baseUrl/admin/contratos",
    'usuarios' => "$baseUrl/admin/usuarios",
];

foreach ($modules as $name => $url) {
    $response = request('GET', $url);
    assertHttpCode($response, 200, "Módulo '$name' cargado correctamente");
}

// =====================================================
// TEST 4: Crear registro en módulo Clientes
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Test 4: Crear registro en Clientes" . COLOR_RESET . "\n";

$createClientePage = request('GET', "$baseUrl/admin/clientes/create");
$csrfToken = extractCsrfToken($createClientePage['body']);

if ($csrfToken) {
    $clienteData = [
        'razon_social' => 'Cliente Test ' . time(),
        'nit' => '999' . rand(100000, 999999) . '-' . rand(1, 9),
        'estado' => 'activo',
        'csrf_test_name' => $csrfToken,
    ];

    $createResponse = request('POST', "$baseUrl/admin/clientes", $clienteData);
    if ($createResponse['code'] === 200 || $createResponse['code'] === 302) {
        echo COLOR_GREEN . "✓ " . COLOR_RESET . "Cliente de prueba creado\n";
    } else {
        echo COLOR_RED . "✗ " . COLOR_RESET . "Error al crear cliente (HTTP {$createResponse['code']})\n";
    }
} else {
    echo COLOR_RED . "✗ " . COLOR_RESET . "No se pudo obtener token CSRF para crear cliente\n";
}

// =====================================================
// TEST 5: Editar registro en módulo Proveedores
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Test 5: Editar registro en Proveedores" . COLOR_RESET . "\n";

// Obtener el primer proveedor de la lista
$proveedoresPage = request('GET', "$baseUrl/admin/proveedores");
if (preg_match('/\/admin\/proveedores\/(\d+)\/edit/', $proveedoresPage['body'], $matches)) {
    $idProveedor = $matches[1];

    $editPage = request('GET', "$baseUrl/admin/proveedores/$idProveedor/edit");
    $csrfToken = extractCsrfToken($editPage['body']);

    if ($csrfToken) {
        $proveedorData = [
            'razon_social' => 'Proveedor Actualizado ' . time(),
            'nit' => '888' . rand(100000, 999999) . '-' . rand(1, 9),
            'csrf_test_name' => $csrfToken,
        ];

        $updateResponse = request('POST', "$baseUrl/admin/proveedores/$idProveedor", $proveedorData);
        if ($updateResponse['code'] === 200 || $updateResponse['code'] === 302) {
            echo COLOR_GREEN . "✓ " . COLOR_RESET . "Proveedor editado correctamente\n";
        } else {
            echo COLOR_RED . "✗ " . COLOR_RESET . "Error al editar proveedor (HTTP {$updateResponse['code']})\n";
        }
    } else {
        echo COLOR_RED . "✗ " . COLOR_RESET . "No se pudo obtener token CSRF para editar proveedor\n";
    }
} else {
    echo COLOR_YELLOW . "⚠ " . COLOR_RESET . "No se encontraron proveedores para editar\n";
}

// =====================================================
// RESUMEN
// =====================================================
echo "\n" . COLOR_BLUE . "=== Resumen del Test ===" . COLOR_RESET . "\n";
echo "Todos los tests básicos completados.\n";
echo "Revisa los resultados arriba para ver si hay errores.\n\n";

// Limpiar archivo de cookies
unlink($cookieFile);
