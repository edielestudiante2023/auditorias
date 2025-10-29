<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proveedor Completó Auditoría</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" style="width: 600px; border-collapse: collapse; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #28a745 0%, #20873a 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                                ✅ Auditoría Completada por Proveedor
                            </h1>
                            <p style="margin: 10px 0 0 0; color: #e3f2fd; font-size: 16px;">
                                El proveedor ha finalizado el proceso de auditoría
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
                                Le notificamos que el proveedor ha completado el proceso de auditoría y está listo para su revisión y calificación.
                            </p>

                            <!-- Info Box -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa; border-left: 4px solid #28a745; margin: 25px 0;">
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
                                            <strong style="color: #333;">Fecha de finalización:</strong> <?= esc($fecha_finalizacion) ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Status Badge -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 25px 0;">
                                <tr>
                                    <td style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 20px; text-align: center; border-radius: 8px;">
                                        <p style="margin: 0; font-size: 16px; font-weight: bold;">
                                            📋 Estado: EN REVISIÓN DEL CONSULTOR
                                        </p>
                                        <p style="margin: 10px 0 0 0; font-size: 14px;">
                                            El proveedor ha completado todos los ítems de la auditoría
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Next Steps -->
                            <h2 style="margin: 30px 0 15px 0; color: #28a745; font-size: 20px; border-bottom: 2px solid #28a745; padding-bottom: 10px;">
                                📝 Próximos Pasos
                            </h2>

                            <ol style="margin: 0 0 25px 0; padding-left: 20px; font-size: 14px; line-height: 1.8; color: #333;">
                                <li style="margin-bottom: 10px;">Acceder a la auditoría usando el botón de abajo</li>
                                <li style="margin-bottom: 10px;">Revisar cada ítem completado por el proveedor</li>
                                <li style="margin-bottom: 10px;">Verificar las evidencias adjuntas</li>
                                <li style="margin-bottom: 10px;">Calificar cada ítem (Cumple / Parcial / No Cumple / No Aplica)</li>
                                <li style="margin-bottom: 10px;">Agregar comentarios del consultor cuando sea necesario</li>
                                <li style="margin-bottom: 10px;">Asignar clientes a la auditoría (si aplica)</li>
                                <li>Cerrar la auditoría para generar los informes PDF</li>
                            </ol>

                            <!-- Access Button -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="<?= esc($url_auditoria) ?>"
                                           style="display: inline-block; padding: 15px 40px; background-color: #28a745; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 5px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                            🔍 Revisar Auditoría Ahora
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info Notice -->
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #e7f3ff; border: 1px solid #b3d9ff; margin: 25px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #0056b3;">
                                            ℹ️ Información Importante:
                                        </p>
                                        <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #333; line-height: 1.8;">
                                            <li>La auditoría permanece en estado "En Revisión" hasta que usted la califique y cierre</li>
                                            <li>Puede revisar y calificar los ítems de forma gradual</li>
                                            <li>Asegúrese de verificar todas las evidencias antes de calificar</li>
                                            <li>Una vez cerrada, se generarán automáticamente los informes PDF para cada cliente asignado</li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>

                            <!-- Summary Stats -->
                            <h3 style="margin: 25px 0 15px 0; color: #333; font-size: 16px;">
                                📊 Resumen de la Auditoría
                            </h3>
                            <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8f9fa; border: 1px solid #dee2e6;">
                                <tr>
                                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">
                                        <span style="font-size: 14px; color: #666;">
                                            <strong>Proveedor auditado:</strong> <?= esc($proveedor_nombre) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">
                                        <span style="font-size: 14px; color: #666;">
                                            <strong>NIT:</strong> <?= esc($proveedor_nit) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px;">
                                        <span style="font-size: 14px; color: #666;">
                                            <strong>Estado actual:</strong> <span style="color: #28a745; font-weight: bold;">En Revisión del Consultor</span>
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 25px 0 0 0; font-size: 14px; line-height: 1.6; color: #666;">
                                Si tiene alguna pregunta o necesita asistencia, no dude en contactar al equipo de soporte.
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
