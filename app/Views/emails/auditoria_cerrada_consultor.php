<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría Cerrada</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" style="width: 600px; border-collapse: collapse; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0056b3 0%, #004494 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                                ✅ Auditoría Cerrada
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #e3f2fd; font-size: 16px;">
                                Los informes PDF han sido generados exitosamente
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #333333;">
                                Estimado/a <strong><?= esc($consultor_nombre) ?></strong>,
                            </p>

                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.6; color: #333333;">
                                La auditoría al proveedor ha sido cerrada exitosamente. A continuación encontrará los detalles:
                            </p>

                            <!-- Info Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa; border-left: 4px solid #0056b3; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
                                            <strong style="color: #333;">ID Auditoría:</strong> #<?= esc($id_auditoria) ?>
                                        </p>
                                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
                                            <strong style="color: #333;">Proveedor:</strong> <?= esc($proveedor_nombre) ?>
                                        </p>
                                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
                                            <strong style="color: #333;">NIT:</strong> <?= esc($proveedor_nit) ?>
                                        </p>
                                        <p style="margin: 0; font-size: 14px; color: #666;">
                                            <strong style="color: #333;">Fecha de cierre:</strong> <?= esc($fecha_cierre) ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Results Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 25px 0;">
                                <tr>
                                    <td style="background-color: <?= $porcentaje_global >= 70 ? '#28a745' : ($porcentaje_global >= 40 ? '#ffc107' : '#dc3545') ?>; color: #ffffff; padding: 25px; text-align: center; border-radius: 8px;">
                                        <p style="margin: 0 0 10px 0; font-size: 16px; font-weight: bold;">
                                            PORCENTAJE GLOBAL DE CUMPLIMIENTO
                                        </p>
                                        <p style="margin: 0; font-size: 36px; font-weight: bold;">
                                            <?= number_format($porcentaje_global, 2) ?>%
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Clientes Section -->
                            <?php if (!empty($clientes)): ?>
                                <h2 style="margin: 30px 0 15px 0; color: #0056b3; font-size: 20px; border-bottom: 2px solid #0056b3; padding-bottom: 10px;">
                                    📊 Informes por Cliente
                                </h2>

                                <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #666;">
                                    Se han generado <?= count($clientes) ?> informe(s) personalizado(s):
                                </p>

                                <?php foreach ($clientes as $cliente): ?>
                                    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa; border: 1px solid #dee2e6; margin-bottom: 15px;">
                                        <tr>
                                            <td style="padding: 15px;">
                                                <p style="margin: 0 0 8px 0; font-size: 16px; font-weight: bold; color: #333;">
                                                    <?= esc($cliente['razon_social']) ?>
                                                </p>
                                                <p style="margin: 0 0 8px 0; font-size: 14px; color: #666;">
                                                    <strong>NIT:</strong> <?= esc($cliente['nit']) ?> &nbsp;|&nbsp;
                                                    <strong>Cumplimiento:</strong>
                                                    <span style="color: <?= $cliente['porcentaje'] >= 70 ? '#28a745' : ($cliente['porcentaje'] >= 40 ? '#ffc107' : '#dc3545') ?>; font-weight: bold;">
                                                        <?= number_format($cliente['porcentaje'], 2) ?>%
                                                    </span>
                                                </p>
                                                <p style="margin: 0; font-size: 12px; color: #999;">
                                                    📄 Ubicación: writable/<?= esc($cliente['ruta_pdf']) ?>
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- PDF Global (opcional) -->
                            <?php if (!empty($ruta_pdf_global)): ?>
                                <h2 style="margin: 30px 0 15px 0; color: #0056b3; font-size: 20px; border-bottom: 2px solid #0056b3; padding-bottom: 10px;">
                                    📋 Informe Global Interno
                                </h2>
                                <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #fff3cd; border-left: 4px solid #ffc107; margin: 15px 0;">
                                    <tr>
                                        <td style="padding: 15px;">
                                            <p style="margin: 0 0 5px 0; font-size: 14px; color: #333;">
                                                <strong>⚠ Documento confidencial de uso interno</strong>
                                            </p>
                                            <p style="margin: 0; font-size: 12px; color: #666;">
                                                📄 Ubicación: writable/<?= esc($ruta_pdf_global) ?>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            <?php endif; ?>

                            <!-- Access Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= esc($url_auditoria) ?>"
                                           style="display: inline-block; padding: 15px 40px; background-color: #0056b3; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                            📁 Ver Auditoría en el Sistema
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Instructions -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #e7f3ff; border: 1px solid #b3d9ff; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #0056b3;">
                                            ℹ️ Próximos pasos:
                                        </p>
                                        <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #333; line-height: 1.8;">
                                            <li>Los informes PDF están disponibles en el servidor en la carpeta <code>writable/reports/</code></li>
                                            <li>Cada cliente tiene un informe personalizado sin información de otros clientes</li>
                                            <li>Puede compartir los informes directamente con cada cliente correspondiente</li>
                                            <li>Los archivos permanecerán almacenados en el sistema para futuras consultas</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 20px 0 0 0; font-size: 14px; line-height: 1.6; color: #666;">
                                Gracias por utilizar el Sistema de Gestión de Auditorías de Cycloid Talent.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #dee2e6;">
                            <p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
                                <strong>Cycloid Talent SAS</strong>
                            </p>
                            <p style="margin: 0 0 5px 0; font-size: 12px; color: #999;">
                                Sistema de Gestión de Auditorías a Proveedores
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #999;">
                                Este es un correo automático, por favor no responder directamente.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
