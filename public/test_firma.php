<?php
// Test de diagnóstico para firma

define('WRITEPATH', dirname(__DIR__) . '/writable/');

echo "<h2>Diagnóstico de Firma</h2>";

$firma_path = 'uploads/firmas_consultor/consultor_3_firma-nata_1761000389_8ff900.png';

echo "<h3>1. Información de rutas:</h3>";
echo "WRITEPATH: " . WRITEPATH . "<br>";
echo "firma_path (relativo): " . $firma_path . "<br>";
echo "Ruta completa: " . WRITEPATH . $firma_path . "<br>";

echo "<h3>2. Verificación de existencia:</h3>";
if (file_exists(WRITEPATH . $firma_path)) {
    echo "✅ El archivo existe en: " . WRITEPATH . $firma_path . "<br>";
    echo "Tamaño: " . filesize(WRITEPATH . $firma_path) . " bytes<br>";
} else {
    echo "❌ El archivo NO existe en: " . WRITEPATH . $firma_path . "<br>";
}

echo "<h3>3. Verificación de helper uploadExists:</h3>";
require_once dirname(__DIR__) . '/app/Helpers/upload_helper.php';

if (function_exists('uploadExists')) {
    echo "✅ Función uploadExists() está definida<br>";

    if (uploadExists($firma_path)) {
        echo "✅ uploadExists() retorna TRUE<br>";
    } else {
        echo "❌ uploadExists() retorna FALSE<br>";
    }
} else {
    echo "❌ Función uploadExists() NO está definida<br>";
}

echo "<h3>4. Prueba de visualización:</h3>";
$url = 'http://localhost/auditorias/public/../writable/' . $firma_path;
echo "URL: " . $url . "<br>";
echo '<img src="' . $url . '" style="max-width: 200px; border: 2px solid red;"><br>';

echo "<h3>5. Listado de archivos en directorio:</h3>";
$dir = WRITEPATH . 'uploads/firmas_consultor/';
if (is_dir($dir)) {
    $files = scandir($dir);
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>" . $file . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "❌ El directorio no existe: " . $dir;
}
?>
