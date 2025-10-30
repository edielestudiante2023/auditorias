<?php
/**
 * Script de Verificación del Servidor
 * Ejecuta este script en el servidor para verificar que todo esté configurado correctamente
 *
 * Uso: php check-server.php
 * O visita: https://tudominio.com/check-server.php
 *
 * IMPORTANTE: ELIMINA ESTE ARCHIVO DESPUÉS DE VERIFICAR (por seguridad)
 */

echo "===========================================\n";
echo "VERIFICACIÓN DEL SERVIDOR - Cycloid Auditorías\n";
echo "===========================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Versión de PHP
echo "📌 Verificando versión de PHP...\n";
$phpVersion = phpversion();
echo "   Versión actual: PHP $phpVersion\n";
if (version_compare($phpVersion, '8.1.0', '>=')) {
    $success[] = "✅ Versión de PHP correcta ($phpVersion)";
} else {
    $errors[] = "❌ Versión de PHP muy antigua ($phpVersion). Se requiere PHP 8.1+";
}
echo "\n";

// 2. Extensión fileinfo
echo "📌 Verificando extensión fileinfo...\n";
if (extension_loaded('fileinfo')) {
    $success[] = "✅ Extensión fileinfo está habilitada";
    echo "   Estado: HABILITADA ✓\n";
} else {
    $warnings[] = "⚠️ Extensión fileinfo NO está habilitada (el sistema funcionará pero con menor seguridad)";
    echo "   Estado: DESHABILITADA ⚠️\n";
    echo "   Solución: sudo apt-get install php-fileinfo && sudo systemctl restart apache2\n";
}
echo "\n";

// 3. Límites de subida
echo "📌 Verificando límites de subida de archivos...\n";
$uploadMaxSize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');
$memoryLimit = ini_get('memory_limit');
echo "   upload_max_filesize: $uploadMaxSize\n";
echo "   post_max_size: $postMaxSize\n";
echo "   memory_limit: $memoryLimit\n";

if (intval($uploadMaxSize) >= 10 && intval($postMaxSize) >= 10) {
    $success[] = "✅ Límites de subida correctos";
} else {
    $warnings[] = "⚠️ Límites de subida pequeños. Recomendado: 10M o más";
}
echo "\n";

// 4. Extensiones requeridas
echo "📌 Verificando extensiones de PHP requeridas...\n";
$requiredExtensions = [
    'mysqli' => 'Base de datos MySQL',
    'mbstring' => 'Manejo de strings multibyte',
    'json' => 'Procesamiento JSON',
    'curl' => 'Conexiones HTTP (SendGrid)',
    'intl' => 'Internacionalización',
    'gd' => 'Procesamiento de imágenes'
];

foreach ($requiredExtensions as $ext => $description) {
    if (extension_loaded($ext)) {
        echo "   ✓ $ext ($description)\n";
        $success[] = "✅ Extensión $ext habilitada";
    } else {
        echo "   ✗ $ext ($description) - NO DISPONIBLE\n";
        $errors[] = "❌ Extensión $ext NO está habilitada";
    }
}
echo "\n";

// 5. Permisos de directorios
echo "📌 Verificando permisos de escritura...\n";
$writableDirs = [
    'writable/logs',
    'writable/session',
    'writable/cache',
    'writable/uploads',
    'writable/uploads/firmas',
    'writable/uploads/logos',
    'writable/uploads/soportes',
    'writable/uploads/evidencias',
    'writable/uploads/pdf'
];

foreach ($writableDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    if (is_dir($fullPath)) {
        if (is_writable($fullPath)) {
            echo "   ✓ $dir (escribible)\n";
        } else {
            echo "   ✗ $dir (NO escribible)\n";
            $errors[] = "❌ Directorio $dir no tiene permisos de escritura";
        }
    } else {
        echo "   ? $dir (no existe)\n";
        $warnings[] = "⚠️ Directorio $dir no existe";
    }
}
echo "\n";

// 6. Archivo .env
echo "📌 Verificando archivo .env...\n";
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $success[] = "✅ Archivo .env existe";
    echo "   Estado: EXISTE ✓\n";

    // Verificar configuraciones importantes
    $envContent = file_get_contents($envFile);

    if (strpos($envContent, 'app.baseURL') !== false) {
        echo "   ✓ app.baseURL está configurado\n";
    } else {
        $warnings[] = "⚠️ app.baseURL no está configurado en .env";
    }

    if (strpos($envContent, 'database.default.hostname') !== false) {
        echo "   ✓ Configuración de base de datos presente\n";
    } else {
        $warnings[] = "⚠️ Configuración de base de datos no encontrada";
    }

    if (strpos($envContent, 'sendgrid.apiKey') !== false) {
        echo "   ✓ SendGrid API Key configurado\n";
    } else {
        $warnings[] = "⚠️ SendGrid API Key no configurado";
    }
} else {
    $errors[] = "❌ Archivo .env NO existe";
    echo "   Estado: NO EXISTE ❌\n";
    echo "   Solución: Copiar .env.production.example como .env\n";
}
echo "\n";

// 7. Funciones requeridas
echo "📌 Verificando funciones de PHP...\n";
$requiredFunctions = [
    'finfo_open' => 'Detección de tipo MIME (opcional)',
    'password_hash' => 'Hashing de contraseñas',
    'mail' => 'Envío de emails (opcional con SendGrid)'
];

foreach ($requiredFunctions as $func => $description) {
    if (function_exists($func)) {
        echo "   ✓ $func() - $description\n";
    } else {
        echo "   ✗ $func() - $description - NO DISPONIBLE\n";
        if ($func === 'finfo_open') {
            $warnings[] = "⚠️ Función $func no disponible (el sistema tiene fallback)";
        } else {
            $warnings[] = "⚠️ Función $func no disponible";
        }
    }
}
echo "\n";

// 8. Resumen
echo "===========================================\n";
echo "RESUMEN DE LA VERIFICACIÓN\n";
echo "===========================================\n\n";

if (count($errors) === 0 && count($warnings) === 0) {
    echo "🎉 ¡TODO PERFECTO! El servidor está correctamente configurado.\n\n";
} else {
    if (count($errors) > 0) {
        echo "❌ ERRORES CRÍTICOS (" . count($errors) . "):\n";
        foreach ($errors as $error) {
            echo "   $error\n";
        }
        echo "\n";
    }

    if (count($warnings) > 0) {
        echo "⚠️ ADVERTENCIAS (" . count($warnings) . "):\n";
        foreach ($warnings as $warning) {
            echo "   $warning\n";
        }
        echo "\n";
    }
}

if (count($success) > 0) {
    echo "✅ CONFIGURACIONES CORRECTAS (" . count($success) . "):\n";
    foreach ($success as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

// 9. Acciones recomendadas
if (count($errors) > 0 || count($warnings) > 0) {
    echo "===========================================\n";
    echo "ACCIONES RECOMENDADAS\n";
    echo "===========================================\n\n";

    if (!extension_loaded('fileinfo')) {
        echo "1. Habilitar extensión fileinfo:\n";
        echo "   sudo apt-get install php-fileinfo\n";
        echo "   sudo systemctl restart apache2\n\n";
    }

    if (count($errors) > 0) {
        echo "2. Revisar la documentación en TROUBLESHOOTING.md\n";
        echo "3. Verificar permisos de carpetas:\n";
        echo "   sudo chown -R www-data:www-data writable/\n";
        echo "   sudo chmod -R 775 writable/\n\n";
    }
}

echo "===========================================\n";
echo "⚠️ IMPORTANTE: ELIMINA ESTE ARCHIVO check-server.php\n";
echo "   después de la verificación por seguridad.\n";
echo "===========================================\n";

// Si se ejecuta desde el navegador, mostrar en HTML
if (php_sapi_name() !== 'cli') {
    echo "\n\n<!-- Para mejor visualización, ejecuta: php check-server.php -->";
}
?>