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

      <?php if (!empty($clientes)): ?>
      <div style="background: #d4edda; border-left: 4px solid #28a745; padding: 20px; margin: 30px 0; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #155724; font-size: 16px;">🏢 Clientes a Evaluar (<?= count($clientes) ?>)</h3>
        <p style="margin: 10px 0 15px 0; font-size: 14px; color: #155724;">
          Esta auditoría evaluará los siguientes clientes. Deberás completar los ítems <strong>"Por Cliente"</strong> para cada uno de ellos:
        </p>

        <table style="width: 100%; border-collapse: collapse; margin: 15px 0; background: #fff; border: 1px solid #c3e6cb; border-radius: 4px;">
          <thead>
            <tr style="background: #28a745; color: #fff;">
              <th style="padding: 10px 12px; text-align: left; font-weight: 600; font-size: 14px; border-bottom: 2px solid #218838;">#</th>
              <th style="padding: 10px 12px; text-align: left; font-weight: 600; font-size: 14px; border-bottom: 2px solid #218838;">Razón Social</th>
              <th style="padding: 10px 12px; text-align: left; font-weight: 600; font-size: 14px; border-bottom: 2px solid #218838;">NIT</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clientes as $index => $cliente): ?>
            <tr style="<?= $index % 2 === 0 ? 'background-color: #f8f9fa;' : 'background-color: #ffffff;' ?>">
              <td style="padding: 10px 12px; border-bottom: 1px solid #c3e6cb; font-size: 14px; font-weight: 600; color: #28a745;">
                <?= $index + 1 ?>
              </td>
              <td style="padding: 10px 12px; border-bottom: 1px solid #c3e6cb; font-size: 14px; color: #333;">
                <?= esc($cliente['razon_social']) ?>
              </td>
              <td style="padding: 10px 12px; border-bottom: 1px solid #c3e6cb; font-size: 13px; color: #666; font-family: 'Courier New', monospace;">
                <?= esc($cliente['nit']) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <p style="margin: 15px 0 0 0; font-size: 13px; color: #155724;">
          <strong>💡 Importante:</strong> Prepara la documentación específica de cada cliente para completar los ítems que tienen alcance "Por Cliente".
        </p>
      </div>
      <?php endif; ?>

      <?php if (!empty($items)): ?>
      <div style="margin: 30px 0;">
        <h3 style="color: #667eea; margin-bottom: 15px;">📋 Ítems a Evaluar (<?= count($items) ?>)</h3>
        <p style="margin-bottom: 15px; color: #6c757d;">A continuación, la documentación que deberá reunir y completar en el sistema:</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0; background: #fff; border: 1px solid #dee2e6;">
          <thead>
            <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
              <th style="padding: 12px 10px; text-align: left; font-weight: 600; font-size: 14px; border-bottom: 2px solid #5a67d8;">Título</th>
              <th style="padding: 12px 10px; text-align: left; font-weight: 600; font-size: 14px; border-bottom: 2px solid #5a67d8;">Descripción</th>
              <th style="padding: 12px 10px; text-align: center; font-weight: 600; font-size: 14px; border-bottom: 2px solid #5a67d8; width: 100px;">Alcance</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $index => $item): ?>
            <tr style="<?= $index % 2 === 0 ? 'background-color: #f8f9fa;' : 'background-color: #ffffff;' ?>">
              <td style="padding: 12px 10px; border-bottom: 1px solid #dee2e6; font-size: 14px; font-weight: 600; color: #333;">
                <?= esc($item['titulo']) ?>
              </td>
              <td style="padding: 12px 10px; border-bottom: 1px solid #dee2e6; font-size: 13px; color: #555; line-height: 1.5;">
                <?= esc($item['descripcion']) ?>
              </td>
              <td style="padding: 12px 10px; border-bottom: 1px solid #dee2e6; text-align: center; font-size: 12px;">
                <?php if ($item['alcance'] === 'global'): ?>
                  <span style="background: #d1ecf1; color: #0c5460; padding: 4px 8px; border-radius: 12px; font-weight: 600; display: inline-block;">🌐 Global</span>
                <?php elseif ($item['alcance'] === 'por_cliente'): ?>
                  <span style="background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 12px; font-weight: 600; display: inline-block;">👥 Por Cliente</span>
                <?php else: ?>
                  <span style="background: #e2e3e5; color: #383d41; padding: 4px 8px; border-radius: 12px; font-weight: 600; display: inline-block;">📋 <?= esc(ucfirst($item['alcance'])) ?></span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div style="background: #e7f3ff; border-left: 4px solid #0066cc; padding: 15px; margin: 20px 0; border-radius: 4px;">
          <p style="margin: 0; font-size: 13px; color: #004085;"><strong>💡 Nota sobre alcances:</strong></p>
          <ul style="margin: 10px 0 0 0; padding-left: 20px; font-size: 13px; color: #004085;">
            <li><strong>Global (🌐):</strong> Se completa una sola vez para toda la auditoría</li>
            <li><strong>Por Cliente (👥):</strong> Debe completarse para cada cliente asignado</li>
          </ul>
        </div>
      </div>
      <?php endif; ?>

      <div style="background: #e7f3ff; border-left: 4px solid #0066cc; padding: 20px; margin: 30px 0; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #004085; font-size: 16px;">🎥 Tutorial en Video</h3>
        <p style="margin: 10px 0; font-size: 14px; color: #004085;">
          ¿Primera vez usando el sistema? Mira este tutorial paso a paso sobre cómo completar tu auditoría:
        </p>

        <div style="text-align: center; margin: 20px 0;">
          <a href="https://www.youtube.com/watch?v=XgU_5wdEaCU" target="_blank" style="display: inline-block; padding: 15px 30px; background: #FF0000; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px;">
            ▶️ Ver Tutorial en YouTube
          </a>
        </div>

        <p style="margin: 10px 0 0 0; font-size: 12px; color: #6c757d; text-align: center; font-style: italic;">
          El video te guiará en el proceso completo de carga de evidencias
        </p>
      </div>

      <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 30px 0; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #856404; font-size: 16px;">🛠️ Herramientas Útiles para Preparar tu Documentación</h3>
        <p style="margin: 10px 0; font-size: 14px; color: #856404;">
          Cada ítem permite <strong>subir un solo archivo</strong> (puede ser PDF, Word, Excel o imagen). Si necesitas adjuntar múltiples documentos, te recomendamos convertirlos y unirlos en un solo PDF usando estas herramientas gratuitas:
        </p>

        <table style="width: 100%; margin: 15px 0; border-collapse: collapse;">
          <tr>
            <td style="padding: 10px; background: #fff; border-radius: 4px; margin-bottom: 8px; vertical-align: top; width: 50%;">
              <strong style="color: #856404; font-size: 14px;">📄 Unir varios PDF en uno:</strong><br>
              <a href="https://www.ilovepdf.com/es/unir_pdf" target="_blank" style="color: #0066cc; font-size: 13px; text-decoration: none;">
                → iLovePDF - Unir PDF
              </a><br>
              <span style="font-size: 12px; color: #666; font-style: italic;">Combina múltiples archivos PDF en un solo documento</span>
            </td>
            <td style="padding: 10px; background: #fff; border-radius: 4px; margin-bottom: 8px; vertical-align: top; width: 50%;">
              <strong style="color: #856404; font-size: 14px;">🖼️ Convertir imágenes a PDF:</strong><br>
              <a href="https://www.ilovepdf.com/es/jpg_a_pdf" target="_blank" style="color: #0066cc; font-size: 13px; text-decoration: none;">
                → iLovePDF - JPG a PDF
              </a><br>
              <span style="font-size: 12px; color: #666; font-style: italic;">Convierte fotos y capturas a formato PDF</span>
            </td>
          </tr>
          <tr>
            <td style="padding: 10px; background: #fff; border-radius: 4px; margin-bottom: 8px; vertical-align: top;">
              <strong style="color: #856404; font-size: 14px;">📝 Comprimir PDF pesado:</strong><br>
              <a href="https://www.ilovepdf.com/es/comprimir_pdf" target="_blank" style="color: #0066cc; font-size: 13px; text-decoration: none;">
                → iLovePDF - Comprimir PDF
              </a><br>
              <span style="font-size: 12px; color: #666; font-style: italic;">Reduce el tamaño sin perder calidad</span>
            </td>
            <td style="padding: 10px; background: #fff; border-radius: 4px; margin-bottom: 8px; vertical-align: top;">
              <strong style="color: #856404; font-size: 14px;">🔄 Convertir Word/Excel a PDF:</strong><br>
              <a href="https://www.ilovepdf.com/es/office_a_pdf" target="_blank" style="color: #0066cc; font-size: 13px; text-decoration: none;">
                → iLovePDF - Office a PDF
              </a><br>
              <span style="font-size: 12px; color: #666; font-style: italic;">Convierte documentos de Office a PDF</span>
            </td>
          </tr>
          <tr>
            <td style="padding: 10px; background: #fff; border-radius: 4px; margin-bottom: 8px; vertical-align: top;">
              <strong style="color: #856404; font-size: 14px;">🔒 Agregar marca de agua al PDF:</strong><br>
              <a href="https://www.ilovepdf.com/es/anadir_marca_agua_pdf" target="_blank" style="color: #0066cc; font-size: 13px; text-decoration: none;">
                → iLovePDF - Marca de Agua
              </a><br>
              <span style="font-size: 12px; color: #666; font-style: italic;">Protege tu documentación con marca de agua</span>
            </td>
            <td style="padding: 10px; background: #fff; border-radius: 4px; margin-bottom: 8px; vertical-align: top;">
              <strong style="color: #856404; font-size: 14px;">🌐 Alternativa: Smallpdf</strong><br>
              <a href="https://smallpdf.com/es" target="_blank" style="color: #0066cc; font-size: 13px; text-decoration: none;">
                → Smallpdf.com
              </a><br>
              <span style="font-size: 12px; color: #666; font-style: italic;">Todas las herramientas PDF en un solo sitio</span>
            </td>
          </tr>
        </table>

        <div style="background: #d1ecf1; border-left: 4px solid #0c5460; padding: 15px; margin: 20px 0; border-radius: 4px;">
          <p style="margin: 0 0 10px 0; font-size: 14px; color: #0c5460;"><strong>🔒 Protege tu Documentación Sensible:</strong></p>
          <p style="margin: 0 0 10px 0; font-size: 13px; color: #0c5460;">
            Si vas a compartir documentación confidencial (certificados, contratos, datos financieros, información de empleados, etc.),
            <strong>te recomendamos agregar una marca de agua</strong> a tus PDFs para proteger la información.
          </p>
          <p style="margin: 0; font-size: 13px; color: #0c5460;">
            <strong>Texto sugerido:</strong> <span style="background: #fff; padding: 3px 8px; border-radius: 3px; font-family: 'Courier New', monospace; font-size: 12px;">"Válido únicamente para Auditoría SST - Cycloid Talent SAS"</span>
          </p>
        </div>

        <p style="margin: 15px 0 5px 0; font-size: 13px; color: #856404;">
          <strong>💡 Consejo:</strong> Organiza tu documentación <strong>antes</strong> de ingresar al sistema. Ten todos tus archivos listos para agilizar el proceso. Aunque el sistema acepta Word, Excel e imágenes, recomendamos usar PDF para mejor compatibilidad.
        </p>
      </div>

      <p style="text-align: center; margin: 30px 0;">
        <a href="https://auditorias.cycloidtalent.com/" class="button">📋 Ir a la Auditoría</a>
      </p>

      <div class="alert">
        <strong>⚠️ Importante:</strong>
        <ul style="margin: 5px 0 0 0; padding-left: 20px;">
          <li><strong>Guarda tu contraseña</strong> en un lugar seguro</li>
          <li>Se recomienda <strong>cambiar tu contraseña</strong> después del primer ingreso</li>
          <li>Cada ítem acepta <strong>solo un archivo</strong> (PDF, Word, Excel o imagen). Si tienes múltiples documentos, usa las herramientas arriba para unirlos</li>
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
