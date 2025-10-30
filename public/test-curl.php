<?php
echo "<h1>Test de cURL</h1>";

if (function_exists('curl_version')) {
    $version = curl_version();
    echo "<p style='color: green;'><strong>✓ cURL está habilitado</strong></p>";
    echo "<ul>";
    echo "<li><strong>Versión cURL:</strong> " . $version['version'] . "</li>";
    echo "<li><strong>SSL Versión:</strong> " . $version['ssl_version'] . "</li>";
    echo "<li><strong>Protocolos:</strong> " . implode(', ', $version['protocols']) . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'><strong>✗ cURL NO está habilitado</strong></p>";
    echo "<p>Necesitas habilitar la extensión cURL en php.ini</p>";
}

echo "<hr>";
echo "<h2>Información de PHP</h2>";
echo "<ul>";
echo "<li><strong>Versión PHP:</strong> " . phpversion() . "</li>";
echo "<li><strong>Sistema Operativo:</strong> " . PHP_OS . "</li>";
echo "</ul>";

echo "<hr>";
echo "<h2>Variables de Entorno (SendGrid)</h2>";
echo "<ul>";
echo "<li><strong>email.fromEmail:</strong> " . (getenv('email.fromEmail') ?: '<span style="color:red;">NO CONFIGURADO</span>') . "</li>";
echo "<li><strong>email.fromName:</strong> " . (getenv('email.fromName') ?: '<span style="color:red;">NO CONFIGURADO</span>') . "</li>";
echo "<li><strong>sendgrid.apiKey:</strong> " . (getenv('sendgrid.apiKey') ? '<span style="color:green;">CONFIGURADO (oculto por seguridad)</span>' : '<span style="color:red;">NO CONFIGURADO</span>') . "</li>";
echo "</ul>";
?>
