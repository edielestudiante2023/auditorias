<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría Asignada</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .content h2 {
            color: #667eea;
            font-size: 20px;
            margin-top: 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
        .info-box p {
            margin: 5px 0;
        }
        .instructions {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .instructions h3 {
            margin-top: 0;
            color: #856404;
            font-size: 16px;
        }
        .instructions ol {
            margin: 10px 0 0 20px;
            padding: 0;
        }
        .instructions li {
            margin: 8px 0;
            color: #856404;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.4);
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(102, 126, 234, 0.5);
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>🎯 Auditoría Asignada</h1>
            <p>Cycloid Talent - Sistema de Auditorías</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Estimado <?= esc($proveedor['razon_social']) ?>,</h2>

            <p>Se le ha asignado una nueva auditoría que requiere su atención y diligenciamiento.</p>

            <!-- Información de la Auditoría -->
            <div class="info-box">
                <strong>📋 Información de la Auditoría</strong>
                <p><strong>ID:</strong> #<?= esc($auditoria['id_auditoria']) ?></p>
                <?php if (!empty($auditoria['codigo_formato'])): ?>
                    <p><strong>Código:</strong> <?= esc($auditoria['codigo_formato']) ?></p>
                <?php endif; ?>
                <?php if (!empty($auditoria['fecha_programada'])): ?>
                    <p><strong>Fecha programada:</strong> <?= date('d/m/Y', strtotime($auditoria['fecha_programada'])) ?></p>
                <?php endif; ?>
                <p><strong>NIT Proveedor:</strong> <?= esc($proveedor['nit']) ?></p>
            </div>

            <!-- Instrucciones -->
            <div class="instructions">
                <h3>📝 Instrucciones</h3>
                <ol>
                    <li>Haga clic en el botón "Acceder al Wizard" para iniciar el proceso</li>
                    <li>Complete todos los ítems requeridos según las instrucciones</li>
                    <li>Adjunte las evidencias solicitadas para cada ítem</li>
                    <li>Los ítems globales se completan una sola vez</li>
                    <li>Los ítems por cliente deben completarse para cada cliente asignado</li>
                    <li>Una vez completado todo al 100%, finalice y envíe a revisión</li>
                </ol>
            </div>

            <p><strong>Importante:</strong> Asegúrese de completar todos los ítems antes de finalizar la auditoría. Una vez enviada, será revisada por el consultor asignado.</p>

            <div class="divider"></div>

            <!-- Call to Action -->
            <div class="button-container">
                <a href="<?= esc($linkAcceso) ?>" class="button">
                    🚀 Acceder al Wizard de Auditoría
                </a>
            </div>

            <p style="text-align: center; font-size: 12px; color: #6c757d;">
                O copie y pegue este enlace en su navegador:<br>
                <a href="<?= esc($linkAcceso) ?>" style="color: #667eea; word-break: break-all;"><?= esc($linkAcceso) ?></a>
            </p>

            <div class="divider"></div>

            <!-- Additional Info -->
            <p style="font-size: 14px; color: #6c757d;">
                Si tiene alguna pregunta o necesita asistencia, por favor contacte al equipo de Cycloid Talent.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Cycloid Talent</strong></p>
            <p>Sistema de Gestión de Auditorías</p>
            <p style="margin-top: 10px;">
                Este es un email automático, por favor no responda a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
