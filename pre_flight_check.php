<?php

/**
 * Pre-Flight Check Script
 *
 * Valida que el sistema esté correctamente configurado antes de QA
 * Uso: php pre_flight_check.php
 */

// Colores para consola
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

$errors = [];
$warnings = [];
$passed = 0;
$failed = 0;

echo "\n";
echo COLOR_BLUE . "╔════════════════════════════════════════════════════════╗" . COLOR_RESET . "\n";
echo COLOR_BLUE . "║       Pre-Flight Check - Sistema de Auditorías       ║" . COLOR_RESET . "\n";
echo COLOR_BLUE . "╚════════════════════════════════════════════════════════╝" . COLOR_RESET . "\n";
echo "\n";

/**
 * Verifica una condición y muestra resultado
 */
function check($condition, $message, $errorMsg = null, $isWarning = false)
{
    global $passed, $failed, $errors, $warnings;

    if ($condition) {
        echo COLOR_GREEN . "✓ " . COLOR_RESET . $message . "\n";
        $passed++;
        return true;
    } else {
        if ($isWarning) {
            echo COLOR_YELLOW . "⚠ " . COLOR_RESET . $message . "\n";
            if ($errorMsg) {
                $warnings[] = $errorMsg;
            }
        } else {
            echo COLOR_RED . "✗ " . COLOR_RESET . $message . "\n";
            if ($errorMsg) {
                $errors[] = $errorMsg;
            }
            $failed++;
        }
        return false;
    }
}

// =====================================================
// 1. VERIFICAR ESTRUCTURA DE ARCHIVOS
// =====================================================
echo COLOR_YELLOW . "→ Verificando estructura de archivos..." . COLOR_RESET . "\n";

check(
    file_exists('.env'),
    "Archivo .env existe",
    "Crea el archivo .env copiando .env.example"
);

check(
    file_exists('spark'),
    "Archivo spark existe",
    "El archivo spark de CodeIgniter no se encuentra"
);

check(
    file_exists('app/Config/Routes.php'),
    "Routes.php existe",
    "Archivo de rutas no encontrado"
);

check(
    is_dir('writable'),
    "Directorio writable/ existe",
    "El directorio writable/ es requerido"
);

check(
    is_dir('vendor'),
    "Directorio vendor/ existe",
    "Ejecuta: composer install"
);

// =====================================================
// 2. VERIFICAR PERMISOS
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Verificando permisos de escritura..." . COLOR_RESET . "\n";

$writableDirs = [
    'writable/',
    'writable/uploads/',
    'writable/logs/',
    'writable/cache/',
    'writable/session/',
];

foreach ($writableDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    check(
        is_writable($dir),
        "Permisos de escritura en $dir",
        "Ejecuta: chmod -R 775 $dir (Linux/Mac) o ajusta permisos en Windows"
    );
}

// =====================================================
// 3. VERIFICAR CONFIGURACIÓN .env
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Verificando configuración .env..." . COLOR_RESET . "\n";

if (file_exists('.env')) {
    $envContent = file_get_contents('.env');

    check(
        strpos($envContent, 'app.baseURL') !== false,
        "app.baseURL configurada",
        "Define app.baseURL en .env (ej: http://localhost/auditorias/)",
        true
    );

    check(
        strpos($envContent, 'database.default.database') !== false,
        "database.default.database configurada",
        "Define el nombre de la base de datos en .env"
    );

    check(
        strpos($envContent, 'database.default.username') !== false,
        "database.default.username configurada",
        "Define el usuario de base de datos en .env"
    );

    $hasSendGrid = strpos($envContent, 'sendgrid.apiKey') !== false &&
                   strpos($envContent, 'sendgrid.apiKey = \'\'') === false;

    check(
        $hasSendGrid,
        "SendGrid API Key configurada",
        null,
        true // Warning, no error
    );

    if (!$hasSendGrid) {
        $warnings[] = "SendGrid no configurado (opcional para QA básica)";
    }
}

// =====================================================
// 4. VERIFICAR EXTENSIONES PHP
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Verificando extensiones PHP..." . COLOR_RESET . "\n";

$requiredExtensions = [
    'mysqli' => 'MySQLi',
    'mbstring' => 'Multibyte String',
    'intl' => 'Internationalization',
    'json' => 'JSON',
    'curl' => 'cURL',
    'gd' => 'GD (imágenes)',
];

foreach ($requiredExtensions as $ext => $name) {
    check(
        extension_loaded($ext),
        "Extensión PHP: $name",
        "Habilita extension=$ext en php.ini"
    );
}

// =====================================================
// 5. VERIFICAR LÍMITES PHP
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Verificando límites PHP..." . COLOR_RESET . "\n";

$uploadMax = ini_get('upload_max_filesize');
$postMax = ini_get('post_max_size');
$memoryLimit = ini_get('memory_limit');

function convertToBytes($value)
{
    $value = trim($value);
    $last = strtolower($value[strlen($value) - 1]);
    $value = (int) $value;

    switch ($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }

    return $value;
}

check(
    convertToBytes($uploadMax) >= convertToBytes('10M'),
    "upload_max_filesize >= 10M (actual: $uploadMax)",
    "Aumenta upload_max_filesize a 20M en php.ini",
    true
);

check(
    convertToBytes($postMax) >= convertToBytes('15M'),
    "post_max_size >= 15M (actual: $postMax)",
    "Aumenta post_max_size a 25M en php.ini",
    true
);

check(
    convertToBytes($memoryLimit) >= convertToBytes('128M'),
    "memory_limit >= 128M (actual: $memoryLimit)",
    "Aumenta memory_limit a 256M en php.ini",
    true
);

// =====================================================
// 6. VERIFICAR DEPENDENCIAS COMPOSER
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Verificando dependencias Composer..." . COLOR_RESET . "\n";

check(
    file_exists('vendor/autoload.php'),
    "vendor/autoload.php existe",
    "Ejecuta: composer install"
);

check(
    is_dir('vendor/dompdf'),
    "Dompdf instalado",
    "Ejecuta: composer require dompdf/dompdf"
);

check(
    is_dir('vendor/sendgrid'),
    "SendGrid instalado",
    "Ejecuta: composer require sendgrid/sendgrid",
    true
);

// =====================================================
// 7. VERIFICAR BASE DE DATOS
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Verificando conexión a base de datos..." . COLOR_RESET . "\n";

if (file_exists('.env')) {
    $envContent = file_get_contents('.env');

    // Parsear configuración básica
    preg_match('/database\.default\.hostname\s*=\s*(.+)/', $envContent, $hostMatch);
    preg_match('/database\.default\.database\s*=\s*(.+)/', $envContent, $dbMatch);
    preg_match('/database\.default\.username\s*=\s*(.+)/', $envContent, $userMatch);
    preg_match('/database\.default\.password\s*=\s*(.*)/', $envContent, $passMatch);

    $host = trim($hostMatch[1] ?? 'localhost');
    $db = trim($dbMatch[1] ?? '');
    $user = trim($userMatch[1] ?? '');
    $pass = trim($passMatch[1] ?? '');

    if ($db && $user) {
        try {
            $conn = @new mysqli($host, $user, $pass);

            if ($conn->connect_error) {
                check(false, "Conexión a MySQL", "Error: " . $conn->connect_error);
            } else {
                check(true, "Conexión a MySQL exitosa", null);

                // Verificar si la base de datos existe
                $result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$db'");

                if ($result && $result->num_rows > 0) {
                    check(true, "Base de datos '$db' existe", null);

                    // Conectar a la base de datos y verificar tablas
                    $conn->select_db($db);

                    $tables = ['users', 'roles', 'clientes', 'proveedores', 'contratos_proveedor_cliente', 'auditorias'];
                    $existingTables = [];

                    foreach ($tables as $table) {
                        $result = $conn->query("SHOW TABLES LIKE '$table'");
                        if ($result && $result->num_rows > 0) {
                            $existingTables[] = $table;
                        }
                    }

                    check(
                        count($existingTables) >= 4,
                        "Tablas principales existen (" . count($existingTables) . "/" . count($tables) . ")",
                        count($existingTables) === 0 ? "Ejecuta: php spark migrate" : null,
                        count($existingTables) > 0 && count($existingTables) < count($tables)
                    );

                    // Verificar datos de prueba
                    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE id_roles = 1");
                    if ($result) {
                        $row = $result->fetch_assoc();
                        $hasAdmin = $row['count'] > 0;

                        check(
                            $hasAdmin,
                            "Usuario admin existe",
                            null,
                            true
                        );

                        if (!$hasAdmin) {
                            $warnings[] = "No hay usuario admin. Ejecuta: php spark db:seed AdminQuickSeed";
                        }
                    }
                } else {
                    check(false, "Base de datos '$db' existe", "Crea la base de datos: CREATE DATABASE $db");
                }

                $conn->close();
            }
        } catch (Exception $e) {
            check(false, "Conexión a MySQL", "Error: " . $e->getMessage());
        }
    } else {
        check(false, "Configuración de BD completa", "Completa la configuración de base de datos en .env", true);
    }
}

// =====================================================
// 8. VERIFICAR ARCHIVOS CRÍTICOS
// =====================================================
echo "\n" . COLOR_YELLOW . "→ Verificando archivos críticos..." . COLOR_RESET . "\n";

$criticalFiles = [
    'app/Controllers/AuthController.php' => 'Controlador de autenticación',
    'app/Controllers/Admin/DashboardController.php' => 'Controlador dashboard admin',
    'app/Models/UserModel.php' => 'Modelo de usuarios',
    'app/Database/Seeds/AdminQuickSeed.php' => 'Seeder de datos de prueba',
    'app/Filters/AuthFilter.php' => 'Filtro de autenticación',
    'app/Filters/RoleFilter.php' => 'Filtro de roles',
];

foreach ($criticalFiles as $file => $desc) {
    check(
        file_exists($file),
        "$desc existe",
        "Archivo $file no encontrado"
    );
}

// =====================================================
// RESUMEN FINAL
// =====================================================
echo "\n";
echo COLOR_BLUE . "╔════════════════════════════════════════════════════════╗" . COLOR_RESET . "\n";
echo COLOR_BLUE . "║                    RESUMEN FINAL                       ║" . COLOR_RESET . "\n";
echo COLOR_BLUE . "╚════════════════════════════════════════════════════════╝" . COLOR_RESET . "\n";
echo "\n";

echo COLOR_GREEN . "Verificaciones exitosas: $passed" . COLOR_RESET . "\n";
echo COLOR_RED . "Verificaciones fallidas: $failed" . COLOR_RESET . "\n";
echo COLOR_YELLOW . "Advertencias: " . count($warnings) . COLOR_RESET . "\n";
echo "\n";

if (!empty($errors)) {
    echo COLOR_RED . "❌ ERRORES CRÍTICOS:" . COLOR_RESET . "\n";
    foreach ($errors as $i => $error) {
        echo "  " . ($i + 1) . ". $error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo COLOR_YELLOW . "⚠️  ADVERTENCIAS:" . COLOR_RESET . "\n";
    foreach ($warnings as $i => $warning) {
        echo "  " . ($i + 1) . ". $warning\n";
    }
    echo "\n";
}

if ($failed === 0) {
    echo COLOR_GREEN . "✅ Sistema listo para QA" . COLOR_RESET . "\n";
    echo "\nPróximos pasos:\n";
    echo "  1. Acceder a: http://localhost/auditorias/login\n";
    echo "  2. Login: superadmin@cycloidtalent.com / Admin123*\n";
    echo "  3. Ejecutar: php test_admin_workflow.php\n";
    echo "\n";
    exit(0);
} else {
    echo COLOR_RED . "❌ Sistema NO listo para QA" . COLOR_RESET . "\n";
    echo "\nCorrige los errores críticos arriba antes de continuar.\n\n";
    exit(1);
}
