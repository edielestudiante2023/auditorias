<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: #f8b500; color: #000; padding: 20px; text-align: center; }
    .content { background: #fff; padding: 30px; border: 1px solid #ddd; }
    .button { display: inline-block; padding: 12px 30px; background: #f8b500; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold; }
    .info-box { background: #f0f8ff; padding: 20px; border-left: 4px solid #0d6efd; margin: 20px 0; border-radius: 5px; }
    .alert { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>📋 Nueva Auditoría Asignada</h1>
    </div>

    <div class="content">
      <h2>Hola, <?= esc($nombreProveedor) ?></h2>

      <p>Se te ha asignado una nueva auditoría en el sistema de <strong>Cycloid Talent</strong>.</p>

      <p>Por favor, ingresa al sistema para completar los ítems de la auditoría lo antes posible.</p>

      <div class="info-box">
        <h3 style="margin-top: 0; color: #0d6efd;">🔑 Tus Credenciales de Acceso</h3>
        <p><strong>Usuario (Email):</strong> <?= esc($usuario) ?></p>
        <p><strong>Contraseña Temporal:</strong> <code style="background: #fff; padding: 5px 10px; border-radius: 3px; font-size: 16px; font-weight: bold; color: #d63384;"><?= esc($clave) ?></code></p>
        <p style="margin-top: 15px;"><strong>🌐 URL de Acceso:</strong><br>
        <a href="<?= $urlLogin ?>" style="color: #0d6efd;"><?= $urlLogin ?></a></p>
      </div>

      <p style="text-align: center; margin: 30px 0;">
        <a href="<?= $urlAuditoria ?>" class="button">📋 Ir a la Auditoría</a>
      </p>

      <div class="alert">
        <strong>⚠️ Importante:</strong>
        <ul style="margin: 5px 0 0 0; padding-left: 20px;">
          <li><strong>Guarda tu contraseña</strong> en un lugar seguro</li>
          <li>Se recomienda <strong>cambiar tu contraseña</strong> después del primer ingreso</li>
          <li>Completa todos los ítems de la auditoría</li>
          <li>Adjunta los soportes requeridos en cada ítem</li>
          <li>Si tienes dudas sobre algún ítem, contáctanos</li>
        </ul>
      </div>

      <p style="margin-top: 30px;">Saludos,<br>
      <strong>Equipo de Cycloid Talent</strong></p>
    </div>

    <div class="footer">
      <p>Este es un correo automático, por favor no responder.</p>
      <p>&copy; <?= date('Y') ?> Cycloid Talent SAS. Todos los derechos reservados.</p>
    </div>
  </div>
</body>
</html>
