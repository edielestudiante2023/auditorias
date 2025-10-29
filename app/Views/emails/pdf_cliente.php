<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Auditoría</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #667eea;
            margin-top: 0;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box strong {
            color: #667eea;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">📄</div>
            <h1>Informe de Auditoría</h1>
        </div>

        <div class="content">
            <h2>Estimado/a <?= esc($nombre_cliente) ?>,</h2>

            <p>Se adjunta el <strong>informe de auditoría</strong> correspondiente a su empresa.</p>

            <div class="info-box">
                <strong>📋 Auditoría:</strong> #<?= esc($id_auditoria) ?><br>
                <strong>📅 Fecha de emisión:</strong> <?= esc($fecha_envio) ?><br>
                <strong>🏢 Cliente:</strong> <?= esc($nombre_cliente) ?>
            </div>

            <p>El informe adjunto contiene:</p>
            <ul>
                <li>✅ Resultados de la evaluación</li>
                <li>📊 Porcentaje de cumplimiento</li>
                <li>📝 Observaciones y comentarios</li>
                <li>📎 Evidencias documentales</li>
            </ul>

            <p><strong>Nota importante:</strong> Este documento es confidencial y está destinado únicamente para uso interno de su organización.</p>
        </div>

        <div class="footer">
            <p><strong>Cycloid Talent - Sistema de Auditorías</strong></p>
            <p>Este es un correo automático, por favor no responder directamente.</p>
            <p>Para consultas, contacte con su consultor asignado.</p>
        </div>
    </div>
</body>
</html>
