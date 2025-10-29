<?php
// TEST: Enviar email de prueba con SendGrid

// Cargar autoload de Composer
require_once __DIR__ . '/vendor/autoload.php';

// Leer archivo .env manualmente
function readEnvFile($filePath) {
    $env = [];
    if (!file_exists($filePath)) {
        return $env;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parsear línea KEY = VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B'\"");
            $env[$key] = $value;
        }
    }
    return $env;
}

$envVars = readEnvFile(__DIR__ . '/.env');

// Configuración desde .env
$apiKey = $envVars['sendgrid.apiKey'] ?? '';
$fromEmail = $envVars['email.fromEmail'] ?? '';
$fromName = $envVars['email.fromName'] ?? '';

echo "<h1>🧪 Test de SendGrid</h1>";
echo "<hr>";

// Verificar configuración
echo "<h3>1. Verificar Configuración:</h3>";
echo "<ul>";
echo "<li><strong>API Key:</strong> " . (empty($apiKey) ? '<span style="color:red;">❌ NO CONFIGURADA</span>' : '<span style="color:green;">✅ Configurada (' . substr($apiKey, 0, 20) . '...)</span>') . "</li>";
echo "<li><strong>From Email:</strong> " . ($fromEmail ?: '<span style="color:red;">No configurado</span>') . "</li>";
echo "<li><strong>From Name:</strong> " . ($fromName ?: '<span style="color:red;">No configurado</span>') . "</li>";
echo "</ul>";

if (empty($apiKey)) {
    echo "<div style='background-color: #ffcccc; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<strong>❌ ERROR:</strong> No se encontró la API Key de SendGrid en el archivo .env<br>";
    echo "Verifica que exista la variable: <code>sendgrid.apiKey</code>";
    echo "</div>";
    exit;
}

// Formulario para enviar email de prueba
if (!isset($_POST['enviar'])) {
    ?>
    <hr>
    <h3>2. Enviar Email de Prueba:</h3>
    <form method="POST">
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold;">Email Destinatario:</label>
            <input type="email" name="to_email" required style="width: 400px; padding: 8px;"
                   placeholder="consultor@ejemplo.com"
                   value="<?= htmlspecialchars($_POST['to_email'] ?? '') ?>">
            <small style="display: block; color: #666;">Ingresa el email del consultor o cualquier email de prueba</small>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: bold;">Nombre Destinatario:</label>
            <input type="text" name="to_name" required style="width: 400px; padding: 8px;"
                   placeholder="Juan Pérez"
                   value="<?= htmlspecialchars($_POST['to_name'] ?? '') ?>">
        </div>

        <button type="submit" name="enviar" value="1"
                style="background-color: #0d6efd; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
            📧 Enviar Email de Prueba
        </button>
    </form>
    <?php
    exit;
}

// Procesar envío
echo "<hr>";
echo "<h3>2. Enviando Email de Prueba...</h3>";

$toEmail = $_POST['to_email'] ?? '';
$toName = $_POST['to_name'] ?? '';

if (empty($toEmail)) {
    echo "<div style='background-color: #ffcccc; padding: 15px;'>❌ Email destinatario requerido</div>";
    exit;
}

try {
    // Usar SendGrid
    $email = new \SendGrid\Mail\Mail();
    $email->setFrom($fromEmail, $fromName);
    $email->setSubject("🧪 Email de Prueba - Sistema de Auditorías Cycloid");
    $email->addTo($toEmail, $toName);

    // Contenido HTML del email
    $htmlContent = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { background-color: #f8f9fa; padding: 30px; border-radius: 0 0 5px 5px; }
            .button { display: inline-block; background-color: #28a745; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🧪 Email de Prueba</h1>
            </div>
            <div class='content'>
                <h2>¡Hola {$toName}!</h2>
                <p>Este es un <strong>email de prueba</strong> del Sistema de Auditorías de Cycloid Talent.</p>

                <p>Si estás viendo este mensaje, significa que:</p>
                <ul>
                    <li>✅ La configuración de SendGrid está correcta</li>
                    <li>✅ El servidor puede enviar emails</li>
                    <li>✅ Tu email está llegando correctamente</li>
                </ul>

                <p><strong>Detalles técnicos:</strong></p>
                <ul>
                    <li><strong>Fecha/Hora:</strong> " . date('d/m/Y H:i:s') . "</li>
                    <li><strong>Remitente:</strong> {$fromEmail}</li>
                    <li><strong>API:</strong> SendGrid (Twilio)</li>
                </ul>

                <p>Cuando un proveedor finalice una auditoría, recibirás un email similar con un enlace directo para revisar.</p>

                <div style='text-align: center;'>
                    <a href='http://localhost/auditorias/public/index.php/consultor/auditorias' class='button'>
                        Ver Mis Auditorías
                    </a>
                </div>
            </div>
            <div class='footer'>
                <p>Este es un email automático del Sistema de Auditorías de Cycloid Talent<br>
                No responder a este correo</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $email->addContent("text/html", $htmlContent);

    // Enviar
    $sendgrid = new \SendGrid($apiKey);
    $response = $sendgrid->send($email);

    // Verificar respuesta
    $statusCode = $response->statusCode();
    $body = $response->body();
    $headers = $response->headers();

    echo "<div style='background-color: " . ($statusCode >= 200 && $statusCode < 300 ? '#d4edda' : '#f8d7da') . "; padding: 20px; border-radius: 5px; margin-top: 20px;'>";

    if ($statusCode >= 200 && $statusCode < 300) {
        echo "<h2 style='color: #155724;'>✅ Email Enviado Exitosamente</h2>";
        echo "<p><strong>Status Code:</strong> {$statusCode}</p>";
        echo "<p><strong>Destinatario:</strong> {$toEmail}</p>";
        echo "<p><strong>Remitente:</strong> {$fromEmail}</p>";
        echo "<hr>";
        echo "<p>📧 <strong>Revisa la bandeja de entrada de {$toEmail}</strong></p>";
        echo "<p style='color: #666;'><small>Nota: Si no aparece en la bandeja principal, revisa en SPAM o correo no deseado.</small></p>";
    } else {
        echo "<h2 style='color: #721c24;'>❌ Error al Enviar Email</h2>";
        echo "<p><strong>Status Code:</strong> {$statusCode}</p>";
        echo "<p><strong>Error:</strong> {$body}</p>";
        echo "<p><strong>Posibles causas:</strong></p>";
        echo "<ul>";
        echo "<li>API Key inválida o expirada</li>";
        echo "<li>Email remitente no verificado en SendGrid</li>";
        echo "<li>Cuenta de SendGrid suspendida</li>";
        echo "</ul>";
    }

    echo "</div>";

    // Mostrar detalles técnicos
    echo "<hr>";
    echo "<details>";
    echo "<summary style='cursor: pointer; font-weight: bold;'>Ver Detalles Técnicos</summary>";
    echo "<pre style='background-color: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
    echo "<strong>Response Headers:</strong>\n";
    print_r($headers);
    echo "\n\n<strong>Response Body:</strong>\n";
    echo $body;
    echo "</pre>";
    echo "</details>";

} catch (\Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 20px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h2 style='color: #721c24;'>❌ Excepción Capturada</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo $e->getTraceAsString();
    echo "</pre>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='test_sendgrid.php' style='display: inline-block; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>← Enviar Otro Email</a></p>";
?>
