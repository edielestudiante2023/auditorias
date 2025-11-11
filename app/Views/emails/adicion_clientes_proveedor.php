<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevos Clientes Agregados - Auditoría</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .alert-info {
            background-color: #d1ecf1;
            border-left: 4px solid #0c5460;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-left: 4px solid #856404;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .credentials-box {
            background: white;
            border: 2px solid #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .clientes-list {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .cliente-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .cliente-item:last-child {
            border-bottom: none;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🆕 Nuevos Clientes Agregados</h1>
        <p>Actualización de Auditoría en Curso</p>
    </div>

    <div class="content">
        <p><strong>Hola <?= esc($nombreProveedor) ?>,</strong></p>

        <div class="alert-info">
            <strong>ℹ️ Actualización Importante:</strong>
            <p>Se han agregado nuevos clientes a la auditoría que ya tienes en proceso. <strong>Solo necesitas completar la información de los nuevos clientes.</strong></p>
        </div>

        <h3>📋 Nuevos Clientes a Evaluar:</h3>
        <div class="clientes-list">
            <?php if (!empty($clientesNuevos)): ?>
                <?php foreach ($clientesNuevos as $cliente): ?>
                    <div class="cliente-item">
                        <strong>🏢 <?= esc($cliente['razon_social'] ?? $cliente['nombre'] ?? 'Cliente') ?></strong>
                        <?php if (!empty($cliente['nit'])): ?>
                            <br><small>NIT: <?= esc($cliente['nit']) ?></small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No se proporcionaron detalles de clientes.</p>
            <?php endif; ?>
        </div>

        <div class="alert-warning">
            <strong>⚠️ Importante - Optimiza tu Tiempo:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li><strong>✅ Items Globales:</strong> Ya están completados previamente</li>
                <li><strong>📝 Items Individuales:</strong> Solo debes completar para los <strong>nuevos clientes</strong></li>
                <li><strong>⏱️ Tiempo estimado:</strong> Aproximadamente <?= count($itemsPorCliente) ?> item(s) por cada cliente nuevo</li>
            </ul>
        </div>

        <?php if (!empty($itemsPorCliente)): ?>
        <h3>📝 Items a Completar por Cliente:</h3>
        <div class="clientes-list">
            <?php foreach ($itemsPorCliente as $item): ?>
                <div class="cliente-item">
                    <?= esc($item['titulo'] ?? $item['nombre'] ?? 'Item') ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <h3>🔐 Credenciales de Acceso Actualizadas:</h3>
        <div class="credentials-box">
            <p><strong>Usuario:</strong> <?= esc($usuario) ?></p>
            <p><strong>Contraseña Temporal:</strong> <code style="background: #f0f0f0; padding: 5px 10px; border-radius: 4px; font-size: 14px;"><?= esc($clave) ?></code></p>
            <p style="color: #856404; font-size: 14px;"><strong>⚠️ Nota:</strong> Por seguridad, se ha generado una nueva contraseña temporal.</p>
        </div>

        <div style="text-align: center;">
            <a href="<?= esc($urlAuditoria) ?>" class="btn">
                🚀 Acceder a la Auditoría
            </a>
        </div>

        <p style="margin-top: 20px; font-size: 14px; color: #666;">
            Si tienes problemas para acceder, también puedes iniciar sesión en:<br>
            <a href="<?= esc($urlLogin) ?>"><?= esc($urlLogin) ?></a>
        </p>

        <div class="alert-info" style="margin-top: 30px;">
            <strong>💡 Recordatorio:</strong>
            <p>Esta auditoría ya está en proceso. Solo necesitas:</p>
            <ol style="margin: 10px 0; padding-left: 20px;">
                <li>Acceder al sistema con tus nuevas credenciales</li>
                <li>Localizar los nuevos clientes agregados</li>
                <li>Completar únicamente los items individuales para esos clientes</li>
                <li>Enviar cuando termines</li>
            </ol>
        </div>
    </div>

    <div class="footer">
        <p><strong>Cycloid Talent SAS</strong></p>
        <p>Sistema de Gestión de Seguridad y Salud en el Trabajo</p>
        <p style="color: #999; font-size: 11px;">
            Este es un correo automático, por favor no responder directamente.<br>
            Para soporte técnico, contacta a tu consultor asignado.
        </p>
    </div>
</body>
</html>
