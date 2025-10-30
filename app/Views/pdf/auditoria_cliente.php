<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Auditoría - <?= esc($cliente['razon_social']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
            border: 2px solid #000;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header td {
            border: 1px solid #000;
            padding: 10px;
            vertical-align: middle;
        }
        .logo-cliente {
            width: 150px;
            height: auto;
            max-height: 100px;
        }
        .header-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
        }
        .header-info {
            font-size: 9pt;
            text-align: left;
        }
        .header-info-label {
            font-weight: bold;
            display: inline-block;
            width: 60px;
        }
        .info-section {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 15px 0;
            border-left: 4px solid #0066cc;
        }
        .info-row {
            margin: 5px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 180px;
        }
        .porcentaje-box {
            background-color: <?= $porcentaje_cliente >= 80 ? '#d4edda' : ($porcentaje_cliente >= 60 ? '#fff3cd' : '#f8d7da') ?>;
            border: 2px solid <?= $porcentaje_cliente >= 80 ? '#28a745' : ($porcentaje_cliente >= 60 ? '#ffc107' : '#dc3545') ?>;
            color: <?= $porcentaje_cliente >= 80 ? '#155724' : ($porcentaje_cliente >= 60 ? '#856404' : '#721c24') ?>;
            text-align: center;
            padding: 15px;
            margin: 20px 0;
            font-size: 18pt;
            font-weight: bold;
        }
        .section-title {
            background-color: #0066cc;
            color: white;
            padding: 8px 12px;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 13pt;
            font-weight: bold;
        }
        .item {
            page-break-inside: avoid;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .item-header {
            background-color: #e9ecef;
            padding: 6px 10px;
            font-weight: bold;
            border-left: 4px solid #0066cc;
            margin-bottom: 8px;
        }
        .item-codigo {
            color: #666;
            font-size: 9pt;
            margin-right: 10px;
        }
        .item-content {
            padding: 5px 10px;
        }
        .item-field {
            margin: 8px 0;
        }
        .item-field-label {
            font-weight: bold;
            color: #555;
            font-size: 10pt;
        }
        .item-field-value {
            margin-top: 3px;
            color: #333;
        }
        .calificacion {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10pt;
        }
        .calificacion-cumple {
            background-color: #28a745;
            color: white;
        }
        .calificacion-parcial {
            background-color: #ffc107;
            color: #333;
        }
        .calificacion-no_cumple {
            background-color: #dc3545;
            color: white;
        }
        .calificacion-no_aplica {
            background-color: #6c757d;
            color: white;
        }
        .evidencias-list {
            font-size: 9pt;
            color: #555;
            margin-top: 5px;
        }
        .evidencia-item {
            margin: 3px 0;
            padding-left: 15px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 80px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            text-align: center;
        }
        .firma-consultor {
            margin-top: 40px;
            text-align: center;
        }
        .firma-imagen {
            width: 150px;
            height: auto;
            max-height: 60px;
            margin-bottom: 5px;
        }
        .firma-nombre {
            font-weight: bold;
            font-size: 10pt;
        }
        .firma-licencia {
            font-size: 9pt;
            color: #666;
        }
        .logos-corporativos {
            margin-top: 40px;
            padding: 20px 0;
            border-top: 2px solid #e0e0e0;
            text-align: center;
        }
        .logos-corporativos table {
            width: 100%;
            margin: 0 auto;
        }
        .logos-corporativos td {
            text-align: center;
            vertical-align: middle;
            padding: 10px;
        }
        .logo-corporativo {
            max-width: 300px;
            max-height: 120px;
            height: auto;
        }
        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>

<!-- ENCABEZADO -->
<div class="header">
    <table>
        <tr>
            <td style="width: 25%; text-align: center;">
                <?php if (!empty($cliente['logo_cliente_path'])): ?>
                    <?php
                    $logoPath = WRITEPATH . $cliente['logo_cliente_path'];
                    log_message('debug', "PDF Logo Path: {$logoPath}");
                    log_message('debug', "Logo exists: " . (file_exists($logoPath) ? 'YES' : 'NO'));
                    if (file_exists($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoMime = mime_content_type($logoPath);
                        log_message('debug', "Logo MIME: {$logoMime}");
                        echo '<img src="data:' . $logoMime . ';base64,' . $logoData . '" class="logo-cliente" alt="Logo Cliente">';
                    } else {
                        echo '<div style="color: red; font-size: 8pt;">Logo no encontrado: ' . htmlspecialchars($logoPath) . '</div>';
                    }
                    ?>
                <?php else: ?>
                    <div style="color: red; font-size: 8pt;">Campo logo_cliente_path vacío</div>
                <?php endif; ?>
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="header-title">SISTEMA DE GESTIÓN DE</div>
                <div class="header-title">SEGURIDAD Y SALUD EN EL TRABAJO</div>
                <div style="margin-top: 10px;"></div>
                <div class="header-title">FORMATO DE EVALUACIÓN DE</div>
                <div class="header-title">PROVEEDORES</div>
            </td>
            <td style="width: 25%;">
                <div class="header-info">
                    <div style="margin-bottom: 5px;">
                        <span class="header-info-label">Código:</span> <?= esc($auditoria['codigo_formato'] ?? 'FT-SST-217') ?>
                    </div>
                    <div style="margin-bottom: 5px;">
                        <span class="header-info-label">Versión:</span> <?= esc($auditoria['version_formato'] ?? '001') ?>
                    </div>
                    <div>
                        <span class="header-info-label">Fecha:</span> <?= date('m/Y') ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- INFORMACIÓN GENERAL -->
<div class="info-section">
    <div class="info-row">
        <span class="info-label">Cliente:</span>
        <?= esc($cliente['razon_social']) ?>
    </div>
    <div class="info-row">
        <span class="info-label">NIT:</span>
        <?= esc($cliente['nit']) ?>
    </div>
    <div class="info-row">
        <span class="info-label">Proveedor Auditado:</span>
        <?= esc($auditoria['proveedor_nombre']) ?> (NIT: <?= esc($auditoria['proveedor_nit']) ?>)
    </div>
    <div class="info-row">
        <span class="info-label">Consultor:</span>
        <?= esc($auditoria['consultor_nombre']) ?>
        <?php if (!empty($auditoria['licencia_sst'])): ?>
            - Licencia SST: <?= esc($auditoria['licencia_sst']) ?>
        <?php endif; ?>
    </div>
    <div class="info-row">
        <span class="info-label">Fecha de Generación:</span>
        <?= date('d/m/Y H:i', strtotime($fecha_generacion)) ?>
    </div>
</div>

<!-- PORCENTAJE DE CUMPLIMIENTO -->
<div class="porcentaje-box">
    PORCENTAJE DE CUMPLIMIENTO: <?= number_format($porcentaje_cliente, 2) ?>%
</div>

<!-- ÍTEMS ESPECÍFICOS DEL CLIENTE -->
<?php if (!empty($items_por_cliente)): ?>
    <div class="section-title">ÍTEMS ESPECÍFICOS DEL CLIENTE</div>

    <?php foreach ($items_por_cliente as $item): ?>
        <div class="item">
            <div class="item-header">
                <span class="item-codigo"><?= esc($item['codigo_item']) ?></span>
                <?= esc($item['titulo']) ?>
            </div>

            <div class="item-content">
                <?php if (!empty($item['descripcion'])): ?>
                    <div class="item-field">
                        <div class="item-field-label">Descripción:</div>
                        <div class="item-field-value"><?= nl2br(esc($item['descripcion'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($item['comentario_proveedor_cliente'])): ?>
                    <div class="item-field">
                        <div class="item-field-label">Comentario del Proveedor:</div>
                        <div class="item-field-value"><?= nl2br(esc($item['comentario_proveedor_cliente'])) ?></div>
                    </div>
                <?php endif; ?>

                <div class="item-field">
                    <div class="item-field-label">Calificación:</div>
                    <div class="item-field-value">
                        <?php
                        $calificacionTexto = [
                            'cumple' => 'CUMPLE',
                            'parcial' => 'CUMPLE PARCIALMENTE',
                            'no_cumple' => 'NO CUMPLE',
                            'no_aplica' => 'NO APLICA'
                        ];
                        $calificacion = $item['calificacion'] ?? 'no_aplica';
                        ?>
                        <span class="calificacion calificacion-<?= $calificacion ?>">
                            <?= $calificacionTexto[$calificacion] ?? 'SIN CALIFICAR' ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($item['comentario_cliente'])): ?>
                    <div class="item-field">
                        <div class="item-field-label">Observaciones del Consultor:</div>
                        <div class="item-field-value"><?= nl2br(esc($item['comentario_cliente'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($item['evidencias'])): ?>
                    <div class="item-field">
                        <div class="item-field-label">Evidencias Adjuntas (<?= count($item['evidencias']) ?>):</div>
                        <div class="evidencias-list">
                            <?php foreach ($item['evidencias'] as $ev): ?>
                                <div class="evidencia-item">
                                    • <?= esc($ev['nombre_archivo_original']) ?>
                                    (<?= number_format($ev['tamanio_bytes'] / 1024, 2) ?> KB)
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- ÍTEMS CORPORATIVOS (GLOBALES) -->
<?php if (!empty($items_globales)): ?>
    <div class="section-title">ÍTEMS CORPORATIVOS DEL PROVEEDOR</div>
    <p style="font-size: 9pt; color: #666; margin-top: -10px; margin-bottom: 15px;">
        Estos ítems aplican de manera general al proveedor y son comunes para todos sus clientes.
    </p>

    <?php foreach ($items_globales as $item): ?>
        <div class="item">
            <div class="item-header">
                <span class="item-codigo"><?= esc($item['codigo_item']) ?></span>
                <?= esc($item['titulo']) ?>
            </div>

            <div class="item-content">
                <?php if (!empty($item['descripcion'])): ?>
                    <div class="item-field">
                        <div class="item-field-label">Descripción:</div>
                        <div class="item-field-value"><?= nl2br(esc($item['descripcion'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($item['comentario_proveedor'])): ?>
                    <div class="item-field">
                        <div class="item-field-label">Comentario del Proveedor:</div>
                        <div class="item-field-value"><?= nl2br(esc($item['comentario_proveedor'])) ?></div>
                    </div>
                <?php endif; ?>

                <div class="item-field">
                    <div class="item-field-label">Calificación:</div>
                    <div class="item-field-value">
                        <?php
                        $calificacionTexto = [
                            'cumple' => 'CUMPLE',
                            'parcial' => 'CUMPLE PARCIALMENTE',
                            'no_cumple' => 'NO CUMPLE',
                            'no_aplica' => 'NO APLICA'
                        ];
                        $calificacion = $item['calificacion'] ?? 'no_aplica';
                        ?>
                        <span class="calificacion calificacion-<?= $calificacion ?>">
                            <?= $calificacionTexto[$calificacion] ?? 'SIN CALIFICAR' ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($item['comentario_consultor'])): ?>
                    <div class="item-field">
                        <div class="item-field-label">Observaciones del Consultor:</div>
                        <div class="item-field-value"><?= nl2br(esc($item['comentario_consultor'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($item['evidencias'])): ?>
                    <div class="item-field">
                        <div class="item-field-label">Evidencias Adjuntas (<?= count($item['evidencias']) ?>):</div>
                        <div class="evidencias-list">
                            <?php foreach ($item['evidencias'] as $ev): ?>
                                <div class="evidencia-item">
                                    • <?= esc($ev['nombre_archivo_original']) ?>
                                    (<?= number_format($ev['tamanio_bytes'] / 1024, 2) ?> KB)
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- FIRMA DEL CONSULTOR -->
<div class="firma-consultor">
    <?php if (!empty($auditoria['firma_path'])): ?>
        <?php
        $firmaPath = WRITEPATH . $auditoria['firma_path'];
        log_message('debug', "PDF Firma Path: {$firmaPath}");
        log_message('debug', "Firma exists: " . (file_exists($firmaPath) ? 'YES' : 'NO'));
        if (file_exists($firmaPath)) {
            $firmaData = base64_encode(file_get_contents($firmaPath));

            // Detectar MIME con fallback si mime_content_type no está disponible
            if (function_exists('mime_content_type')) {
                $firmaMime = mime_content_type($firmaPath);
            } else {
                // Fallback: detectar por extensión
                $ext = strtolower(pathinfo($firmaPath, PATHINFO_EXTENSION));
                $firmaMime = match($ext) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    default => 'image/png'
                };
            }

            log_message('debug', "Firma MIME: {$firmaMime}");
            echo '<img src="data:' . $firmaMime . ';base64,' . $firmaData . '" class="firma-imagen" alt="Firma">';
        } else {
            echo '<div style="color: red; font-size: 8pt;">Firma no encontrada: ' . htmlspecialchars($firmaPath) . '</div>';
        }
        ?>
    <?php else: ?>
        <div style="color: red; font-size: 8pt;">Campo firma_path vacío</div>
    <?php endif; ?>
    <div style="border-top: 1px solid #333; width: 250px; margin: 0 auto; margin-top: 10px;">
        <div class="firma-nombre"><?= esc($auditoria['consultor_nombre']) ?></div>
        <div class="firma-licencia">Consultor SG-SST</div>
        <?php if (!empty($auditoria['licencia_sst'])): ?>
            <div class="firma-licencia">Licencia: <?= esc($auditoria['licencia_sst']) ?></div>
        <?php endif; ?>
    </div>
</div>

<!-- LOGOS CORPORATIVOS -->
<div class="logos-corporativos">
    <table>
        <tr>
            <td style="width: 33.33%;">
                <?php
                $logoPath1 = FCPATH . 'assets/images/brand/logocycloidsinfondo.png';
                log_message('debug', "Logo 1 Path: {$logoPath1}");
                log_message('debug', "Logo 1 exists: " . (file_exists($logoPath1) ? 'YES' : 'NO'));
                if (file_exists($logoPath1)) {
                    $logoData1 = base64_encode(file_get_contents($logoPath1));
                    $logoMime1 = mime_content_type($logoPath1);
                    echo '<img src="data:' . $logoMime1 . ';base64,' . $logoData1 . '" class="logo-corporativo" alt="Cycloid Talent">';
                } else {
                    echo '<div style="color: red; font-size: 7pt;">Logo 1 no encontrado</div>';
                }
                ?>
            </td>
            <td style="width: 33.33%;">
                <?php
                $logoPath2 = FCPATH . 'assets/images/brand/logoenterprisesstdorado.jpg';
                log_message('debug', "Logo 2 Path: {$logoPath2}");
                log_message('debug', "Logo 2 exists: " . (file_exists($logoPath2) ? 'YES' : 'NO'));
                if (file_exists($logoPath2)) {
                    $logoData2 = base64_encode(file_get_contents($logoPath2));
                    $logoMime2 = mime_content_type($logoPath2);
                    echo '<img src="data:' . $logoMime2 . ';base64,' . $logoData2 . '" class="logo-corporativo" alt="Enterprise SST">';
                } else {
                    echo '<div style="color: red; font-size: 7pt;">Logo 2 no encontrado</div>';
                }
                ?>
            </td>
            <td style="width: 33.33%;">
                <?php
                $logoPath3 = FCPATH . 'assets/images/brand/logosst.png';
                log_message('debug', "Logo 3 Path: {$logoPath3}");
                log_message('debug', "Logo 3 exists: " . (file_exists($logoPath3) ? 'YES' : 'NO'));
                if (file_exists($logoPath3)) {
                    $logoData3 = base64_encode(file_get_contents($logoPath3));
                    $logoMime3 = mime_content_type($logoPath3);
                    echo '<img src="data:' . $logoMime3 . ';base64,' . $logoData3 . '" class="logo-corporativo" alt="SST">';
                } else {
                    echo '<div style="color: red; font-size: 7pt;">Logo 3 no encontrado</div>';
                }
                ?>
            </td>
        </tr>
    </table>
</div>

<!-- FOOTER -->
<div class="footer">
    <div style="font-size: 8pt; color: #666;">
        Informe generado por Cycloid Talent SAS - Sistema de Gestión de Auditorías
        <br>
        Documento confidencial - Página <span class="page-number"></span>
    </div>
</div>

</body>
</html>
