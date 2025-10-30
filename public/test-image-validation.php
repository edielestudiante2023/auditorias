<?php

// Test de validación de imágenes
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Validación de Imágenes</h1>";

// Verificar extensión fileinfo
echo "<h2>1. Extensión fileinfo</h2>";
if (extension_loaded('fileinfo')) {
    echo "✓ Extensión fileinfo está HABILITADA<br>";
} else {
    echo "✗ Extensión fileinfo NO está disponible<br>";
}

// Verificar función finfo_open
echo "<h2>2. Función finfo_open</h2>";
if (function_exists('finfo_open')) {
    echo "✓ Función finfo_open está disponible<br>";

    $finfo = @finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        echo "✗ finfo_open() falló al ejecutarse<br>";
        echo "Error: " . error_get_last()['message'] . "<br>";
    } else {
        echo "✓ finfo_open() ejecutado exitosamente<br>";
        finfo_close($finfo);
    }
} else {
    echo "✗ Función finfo_open NO está disponible<br>";
}

// Test de archivo subido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    echo "<h2>3. Análisis del Archivo Subido</h2>";

    $file = $_FILES['test_image'];

    echo "<pre>";
    echo "Información del archivo:\n";
    echo "  name: " . $file['name'] . "\n";
    echo "  type: " . $file['type'] . "\n";
    echo "  tmp_name: " . $file['tmp_name'] . "\n";
    echo "  error: " . $file['error'] . "\n";
    echo "  size: " . $file['size'] . " bytes\n";
    echo "</pre>";

    // Verificar si existe el archivo temporal
    if (file_exists($file['tmp_name'])) {
        echo "✓ Archivo temporal existe<br>";

        // Extensión
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        echo "Extensión: " . $ext . "<br>";

        if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
            echo "✓ Extensión válida<br>";
        } else {
            echo "✗ Extensión NO válida<br>";
        }

        // MIME del cliente
        $clientMime = strtolower($file['type']);
        echo "MIME del cliente: " . $clientMime . "<br>";

        if (in_array($clientMime, ['image/png', 'image/jpeg', 'image/jpg', 'image/pjpeg'])) {
            echo "✓ MIME del cliente válido<br>";
        } else {
            echo "✗ MIME del cliente NO válido<br>";
        }

        // MIME con finfo
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detectedMime = @finfo_file($finfo, $file['tmp_name']);
                @finfo_close($finfo);

                echo "MIME detectado con finfo: " . $detectedMime . "<br>";

                if (in_array($detectedMime, ['image/png', 'image/jpeg', 'image/jpg'])) {
                    echo "✓ MIME detectado válido<br>";
                } else {
                    echo "✗ MIME detectado NO válido<br>";
                }
            } else {
                echo "✗ No se pudo usar finfo para detectar MIME<br>";
            }
        }

        // Resultado final
        echo "<h3>Resultado de isImage():</h3>";

        // Simular la función isImage
        $isValid = false;

        // Por extensión
        if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $isValid = true;
            echo "✓ Validado por EXTENSIÓN<br>";
        }

        // Por tipo del cliente
        if (!$isValid && in_array($clientMime, ['image/png', 'image/jpeg', 'image/jpg', 'image/pjpeg'])) {
            $isValid = true;
            echo "✓ Validado por MIME DEL CLIENTE<br>";
        }

        if ($isValid) {
            echo "<h2 style='color: green;'>✓ LA IMAGEN ES VÁLIDA</h2>";
        } else {
            echo "<h2 style='color: red;'>✗ LA IMAGEN NO ES VÁLIDA</h2>";
        }

    } else {
        echo "✗ Archivo temporal NO existe<br>";
    }
}
?>

<h2>Subir Imagen de Prueba</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_image" accept="image/png,image/jpeg,image/jpg">
    <button type="submit">Probar Imagen</button>
</form>
