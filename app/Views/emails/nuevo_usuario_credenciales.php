<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: #0d6efd; color: #fff; padding: 20px; text-align: center; }
    .content { background: #fff; padding: 30px; border: 1px solid #ddd; }
    .credentials { background: #f0f8ff; padding: 20px; border-left: 4px solid #0d6efd; margin: 20px 0; border-radius: 5px; }
    .credential-row { margin: 10px 0; }
    .credential-label { font-weight: bold; color: #0d6efd; }
    .credential-value { font-family: 'Courier New', monospace; background: #fff; padding: 5px 10px; border-radius: 3px; display: inline-block; margin-top: 5px; }
    .button { display: inline-block; padding: 12px 30px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
    .alert { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    .icon { font-size: 48px; margin-bottom: 10px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="icon">🔐</div>
      <h1>Bienvenido al Sistema de Auditorías</h1>
      <p>Cycloid Talent SAS</p>
    </div>

    <div class="content">
      <h2>Hola, <?= esc($nombreUsuario) ?></h2>

      <p>Se ha creado una cuenta de acceso para ti en nuestro sistema de auditorías.</p>

      <p>Tu rol en el sistema es: <strong><?= esc($nombreRol) ?></strong></p>

      <div class="credentials">
        <h3 style="margin-top: 0; color: #0d6efd;">🔑 Tus Credenciales de Acceso</h3>

        <div class="credential-row">
          <div class="credential-label">📧 Usuario (Email):</div>
          <div class="credential-value"><?= esc($email) ?></div>
        </div>

        <div class="credential-row">
          <div class="credential-label">🔒 Contraseña Temporal:</div>
          <div class="credential-value"><?= esc($passwordTemporal) ?></div>
        </div>

        <div class="credential-row" style="margin-top: 20px;">
          <div class="credential-label">🌐 URL de Acceso:</div>
          <div><a href="<?= $urlLogin ?>" style="color: #0d6efd;"><?= $urlLogin ?></a></div>
        </div>
      </div>

      <div style="text-align: center;">
        <a href="<?= $urlLogin ?>" class="button">🚀 Ingresar al Sistema</a>
      </div>

      <div class="alert">
        <strong>⚠️ IMPORTANTE - Seguridad:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
          <li>Esta es una <strong>contraseña temporal</strong></li>
          <li>Por favor, <strong>cámbiala inmediatamente</strong> después de tu primer ingreso</li>
          <li>Usa una contraseña segura que solo tú conozcas</li>
          <li>No compartas tus credenciales con nadie</li>
        </ul>
      </div>

      <p style="margin-top: 30px;">Si tienes alguna duda o problema para acceder, no dudes en contactarnos.</p>

      <p>Saludos,<br>
      <strong>Equipo de Cycloid Talent</strong></p>
    </div>

    <div class="footer">
      <p>Este es un correo automático, por favor no responder.</p>
      <p>&copy; <?= date('Y') ?> Cycloid Talent SAS. Todos los derechos reservados.</p>
      <p style="color: #999; font-size: 11px; margin-top: 10px;">
        Por seguridad, este email solo se envía una vez. Guarda tus credenciales en un lugar seguro.
      </p>
    </div>
  </div>
</body>
</html>
