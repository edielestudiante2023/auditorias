<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría Finalizada</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
            color: #28a745;
            font-size: 20px;
            margin-top: 0;
        }
        .success-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box strong {
            color: #28a745;
            display: block;
            margin-bottom: 5px;
        }
        .info-box p {
            margin: 5px 0;
        }
        .progress-box {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .progress-box h3 {
            margin: 0 0 10px 0;
            color: #155724;
            font-size: 18px;
        }
        .progress-number {
            font-size: 48px;
            font-weight: 700;
            color: #28a745;
            margin: 10px 0;
        }
        .table-responsive {
            overflow-x: auto;
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
        }
        thead {
            background-color: #28a745;
            color: #ffffff;
        }
        th {
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
        }
        .stat-card h4 {
            margin: 0;
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
        }
        .stat-card p {
            margin: 10px 0 0 0;
            font-size: 24px;
            font-weight: 700;
            color: #28a745;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(40, 167, 69, 0.4);
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(40, 167, 69, 0.5);
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
        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 20px 0;
        }
        .icon {
            font-size: 20px;
            vertical-align: middle;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>✅ Auditoría Finalizada</h1>
            <p>El proveedor ha completado la auditoría</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div style="text-align: center;">
                <span class="success-badge">🎉 Completado 100%</span>
            </div>

            <h2>Estimado Consultor,</h2>

            <p>El proveedor <strong><?= esc($auditoria['proveedor_nombre'] ?? 'N/A') ?></strong> ha finalizado y enviado la auditoría para su revisión.</p>

            <!-- Información de la Auditoría -->
            <div class="info-box">
                <strong>📋 Información de la Auditoría</strong>
                <p><strong>ID:</strong> #<?= esc($auditoria['id_auditoria'] ?? 'N/A') ?></p>
                <p><strong>Proveedor:</strong> <?= esc($auditoria['proveedor_nombre'] ?? 'N/A') ?></p>
                <p><strong>NIT:</strong> <?= esc($auditoria['proveedor_nit'] ?? 'N/A') ?></p>
                <?php if (!empty($auditoria['codigo_formato'])): ?>
                    <p><strong>Código:</strong> <?= esc($auditoria['codigo_formato']) ?></p>
                <?php endif; ?>
                <p><strong>Fecha de finalización:</strong> <?= date('d/m/Y H:i') ?></p>
            </div>

            <!-- Progreso -->
            <div class="progress-box">
                <h3>Progreso de Completitud</h3>
                <div class="progress-number"><?= number_format($resumen['porcentaje_total'] ?? 100, 0) ?>%</div>
                <p style="margin: 0; color: #155724;">Todos los ítems han sido completados</p>
            </div>

            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Ítems Globales</h4>
                    <p><?= esc($resumen['globales_completos'] ?? 0) ?> / <?= esc($resumen['globales_total'] ?? 0) ?></p>
                </div>
                <div class="stat-card">
                    <h4>Ítems por Cliente</h4>
                    <p><?= esc($resumen['por_cliente_completos'] ?? 0) ?> / <?= esc($resumen['por_cliente_total'] ?? 0) ?></p>
                </div>
            </div>

            <?php if (!empty($resumen['clientes'])): ?>
                <div class="divider"></div>

                <!-- Tabla de Clientes -->
                <h3 style="color: #28a745; margin-top: 20px;">📊 Resumen por Cliente</h3>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th><span class="icon">🏢</span>Cliente</th>
                                <th><span class="icon">📋</span>NIT</th>
                                <th style="text-align: center;"><span class="icon">✅</span>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resumen['clientes'] as $cliente): ?>
                                <tr>
                                    <td><?= esc($cliente['razon_social'] ?? 'N/A') ?></td>
                                    <td><?= esc($cliente['nit'] ?? 'N/A') ?></td>
                                    <td style="text-align: center;">
                                        <?php if (!empty($cliente['completo'])): ?>
                                            <span style="color: #28a745; font-weight: 600;">✓ Completo</span>
                                        <?php else: ?>
                                            <span style="color: #ffc107; font-weight: 600;">⚠ Parcial</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="divider"></div>

            <!-- Próximos Pasos -->
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin-top: 0; color: #856404; font-size: 16px;">🔔 Próximos Pasos</h3>
                <ol style="margin: 10px 0 0 20px; padding: 0;">
                    <li style="margin: 8px 0; color: #856404;">Revise los ítems completados por el proveedor</li>
                    <li style="margin: 8px 0; color: #856404;">Califique cada ítem según los criterios (A/B/C)</li>
                    <li style="margin: 8px 0; color: #856404;">Agregue comentarios si es necesario</li>
                    <li style="margin: 8px 0; color: #856404;">Cierre la auditoría para generar los informes PDF</li>
                </ol>
            </div>

            <!-- Call to Action -->
            <div class="button-container">
                <?php if (!empty($auditoria['url_revision'])): ?>
                    <a href="<?= esc($auditoria['url_revision']) ?>" class="button">
                        👁️ Revisar Auditoría Ahora
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('consultor/auditorias') ?>" class="button">
                        👁️ Ver Auditorías
                    </a>
                <?php endif; ?>
            </div>

            <div class="divider"></div>

            <p style="font-size: 14px; color: #6c757d;">
                Si tiene alguna pregunta o necesita más información, por favor acceda al sistema para revisar los detalles completos.
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
