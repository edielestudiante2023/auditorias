<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    .header { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; padding: 30px 20px; text-align: center; border-radius: 10px 10px 0 0; }
    .content { background: #fff; padding: 30px; border: 1px solid #ddd; border-top: none; }
    .reset-box { background: #f0f8ff; padding: 25px; border-left: 4px solid #667eea; margin: 25px 0; border-radius: 5px; text-align: center; }
    .button { display: inline-block; padding: 14px 40px; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; margin: 15px 0; }
    .alert { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 0 5px 5px 0; }
    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px; background: #f8f9fa; }
    .icon { font-size: 48px; margin-bottom: 10px; }
    .url-fallback { word-break: break-all; font-size: 12px; color: #667eea; margin-top: 10px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <div class="icon">🔑</div>
      <h1 style="margin: 0;">Recuperación de Contraseña</h1>
      <p style="margin: 5px 0 0;">Cycloid Talent SAS</p>
    </div>

    <div class="content">
      <h2>Hola, <?= esc($nombreUsuario) ?></h2>

      <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en el Sistema de Gestión de Auditorías.</p>

      <div class="reset-box">
        <p style="margin-top: 0; font-size: 16px;"><strong>Haz clic en el siguiente botón para crear una nueva contraseña:</strong></p>
        <a href="<?= $urlReset ?>" class="button">Restablecer mi contraseña</a>
        <p class="url-fallback">
          Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
          <a href="<?= $urlReset ?>"><?= $urlReset ?></a>
        </p>
      </div>

      <div class="alert">
        <strong>⚠️ Importante:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
          <li>Este enlace es válido por <strong>1 hora</strong></li>
          <li>Si no solicitaste este cambio, ignora este correo</li>
          <li>Tu contraseña actual no se modificará hasta que crees una nueva</li>
          <li>Por seguridad, este enlace solo puede usarse una vez</li>
        </ul>
      </div>

      <p style="margin-top: 25px;">Si tienes algún problema, contacta al administrador del sistema.</p>

      <p>Saludos,<br>
      <strong>Equipo de Cycloid Talent</strong></p>
    </div>

    <div class="footer">
      <p>Este es un correo automático, por favor no responder.</p>
      <p>&copy; <?= date('Y') ?> Cycloid Talent SAS. Todos los derechos reservados.</p>
    </div>
  </div>
</body>
</html>
