<?php helper('format'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría - <?= esc($auditoria['proveedor_nombre']) ?></title>
    <style>
        @page {
            size: A4;
            margin: 2cm 1.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }

        /* Encabezado con logos */
        .header-logos {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
        }

        .header-logo-left,
        .header-logo-right {
            display: table-cell;
            width: 25%;
            vertical-align: middle;
        }

        .header-logo-left {
            text-align: left;
        }

        .header-logo-right {
            text-align: right;
        }

        .header-info {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: middle;
        }

        .logo-img {
            max-width: 120px;
            max-height: 60px;
        }

        .header-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0056b3;
            margin-bottom: 3px;
        }

        .header-subtitle {
            font-size: 9pt;
            color: #666;
        }

        /* Información de la auditoría */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 30%;
            padding: 6px 10px;
            font-weight: bold;
            background-color: #e9ecef;
            border-bottom: 1px solid #ddd;
        }

        .info-value {
            display: table-cell;
            width: 70%;
            padding: 6px 10px;
            border-bottom: 1px solid #ddd;
        }

        /* Sección */
        h2 {
            color: #0056b3;
            font-size: 12pt;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 4px;
            page-break-after: avoid;
        }

        h3 {
            color: #333;
            font-size: 11pt;
            margin-top: 15px;
            margin-bottom: 8px;
            page-break-after: avoid;
        }

        /* Tabla de ítems */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            page-break-inside: auto;
        }

        table thead {
            background-color: #0056b3;
            color: white;
        }

        table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9pt;
            border: 1px solid #0056b3;
        }

        table td {
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
            font-size: 9pt;
            page-break-inside: avoid;
        }

        table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Badge de calificación */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8pt;
            color: white;
            text-align: center;
        }

        .badge-cumple {
            background-color: #28a745;
        }

        .badge-parcial {
            background-color: #ffc107;
            color: #333;
        }

        .badge-no-cumple {
            background-color: #dc3545;
        }

        .badge-no-aplica {
            background-color: #6c757d;
        }

        /* Evidencias */
        .evidencias-section {
            margin-top: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }

        .evidencias-grid {
            display: table;
            width: 100%;
            margin-top: 8px;
        }

        .evidencia-item {
            display: table-row;
            margin-bottom: 8px;
        }

        .evidencia-thumbnail {
            display: table-cell;
            width: 80px;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }

        .evidencia-thumbnail img {
            max-width: 70px;
            max-height: 70px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .evidencia-info {
            display: table-cell;
            padding: 4px 8px;
            vertical-align: middle;
        }

        .evidencia-nombre {
            font-weight: bold;
            font-size: 9pt;
            color: #0056b3;
            margin-bottom: 2px;
        }

        .evidencia-meta {
            font-size: 8pt;
            color: #666;
        }

        .file-icon {
            font-size: 40px;
            color: #6c757d;
        }

        /* Porcentaje de cumplimiento */
        .porcentaje-box {
            padding: 12px;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0;
            border-radius: 4px;
            page-break-inside: avoid;
        }

        .porcentaje-alto {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }

        .porcentaje-medio {
            background-color: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
        }

        .porcentaje-bajo {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }

        /* Firma */
        .firma-section {
            margin-top: 40px;
            text-align: center;
            page-break-inside: avoid;
        }

        .firma-img {
            max-width: 180px;
            max-height: 70px;
            margin-bottom: 8px;
        }

        .firma-line {
            border-top: 1px solid #333;
            width: 220px;
            margin: 0 auto;
            padding-top: 5px;
            font-size: 9pt;
        }

        /* Footer con paginación */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }

        .page-number:before {
            content: "Página " counter(page) " de " counter(pages);
        }

        /* Comentarios */
        .comentario {
            margin-top: 4px;
            padding: 4px 6px;
            background-color: #fff;
            border-left: 3px solid #0056b3;
            font-size: 8pt;
        }

        .comentario-label {
            font-weight: bold;
            color: #0056b3;
        }

        /* Cliente info box */
        .cliente-box {
            background-color: #e7f3ff;
            border: 2px solid #0056b3;
            padding: 10px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .cliente-box h3 {
            color: #0056b3;
            margin-top: 0;
            margin-bottom: 8px;
        }

        /* Items sin evidencias */
        .sin-evidencias {
            font-style: italic;
            color: #999;
            font-size: 8pt;
            padding: 6px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO CON LOGOS -->
    <div class="header-logos">
        <div class="header-logo-left">
            <?php if (!empty($logo_proveedor) && file_exists($logo_proveedor)): ?>
                <img src="<?= $logo_proveedor ?>" alt="Logo Proveedor" class="logo-img">
            <?php endif; ?>
        </div>
        <div class="header-info">
            <div class="header-title">INFORME DE AUDITORÍA</div>
            <div class="header-subtitle">Sistema de Gestión - Cycloid Talent SAS</div>
        </div>
        <div class="header-logo-right">
            <?php if (!empty($logo_cliente) && file_exists($logo_cliente)): ?>
                <img src="<?= $logo_cliente ?>" alt="Logo Cliente" class="logo-img">
            <?php endif; ?>
        </div>
    </div>

    <!-- INFORMACIÓN GENERAL -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Proveedor:</div>
            <div class="info-value"><?= esc($auditoria['proveedor_nombre']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">NIT:</div>
            <div class="info-value"><?= formatoNIT($auditoria['proveedor_nit']) ?></div>
        </div>
        <?php if (!empty($cliente)): ?>
        <div class="info-row">
            <div class="info-label">Cliente:</div>
            <div class="info-value"><?= esc($cliente['razon_social']) ?> - NIT: <?= formatoNIT($cliente['nit']) ?></div>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <div class="info-label">Código Formato:</div>
            <div class="info-value"><?= formatoCodigoFormato($auditoria['codigo_formato'], $auditoria['version_formato']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha Auditoría:</div>
            <div class="info-value"><?= formatoFecha($auditoria['fecha_programada']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha Cierre:</div>
            <div class="info-value"><?= formatoFecha($auditoria['updated_at']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Consultor:</div>
            <div class="info-value"><?= esc($auditoria['consultor_nombre']) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Estado:</div>
            <div class="info-value"><?= esc(ucfirst(str_replace('_', ' ', $auditoria['estado']))) ?></div>
        </div>
    </div>

    <!-- PORCENTAJE DE CUMPLIMIENTO -->
    <?php
    $porcentaje = $porcentaje_cumplimiento ?? $auditoria['porcentaje_cumplimiento'] ?? 0;
    $claseColor = $porcentaje >= 70 ? 'porcentaje-alto' : ($porcentaje >= 40 ? 'porcentaje-medio' : 'porcentaje-bajo');
    ?>
    <div class="porcentaje-box <?= $claseColor ?>">
        PORCENTAJE DE CUMPLIMIENTO: <?= number_format($porcentaje, 2) ?>%
    </div>

    <!-- TABLA DE ÍTEMS GLOBALES -->
    <?php if (!empty($items_globales)): ?>
    <h2>Ítems de Evaluación Global</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Código</th>
                <th style="width: 35%;">Ítem</th>
                <th style="width: 12%;">Estado</th>
                <th style="width: 43%;">Comentarios</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items_globales as $item): ?>
            <tr>
                <td><strong><?= esc($item['codigo_item']) ?></strong></td>
                <td>
                    <strong><?= esc($item['titulo']) ?></strong>
                    <?php if (!empty($item['descripcion'])): ?>
                        <br><span style="font-size: 8pt; color: #666;"><?= esc($item['descripcion']) ?></span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?php
                    $calificacion = $item['calificacion'] ?? 'no_aplica';
                    $badges = [
                        'cumple' => '<span class="badge badge-cumple">CUMPLE</span>',
                        'parcial' => '<span class="badge badge-parcial">PARCIAL</span>',
                        'no_cumple' => '<span class="badge badge-no-cumple">NO CUMPLE</span>',
                        'no_aplica' => '<span class="badge badge-no-aplica">NO APLICA</span>',
                    ];
                    echo $badges[$calificacion] ?? '';
                    ?>
                </td>
                <td>
                    <?php if (!empty($item['comentario_proveedor'])): ?>
                        <div class="comentario">
                            <span class="comentario-label">Proveedor:</span>
                            <?= nl2br(esc($item['comentario_proveedor'])) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($item['comentario_consultor'])): ?>
                        <div class="comentario">
                            <span class="comentario-label">Consultor:</span>
                            <?= nl2br(esc($item['comentario_consultor'])) ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- TABLA DE ÍTEMS POR CLIENTE -->
    <?php if (!empty($items_por_cliente)): ?>
    <h2>Ítems Específicos por Cliente</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Código</th>
                <th style="width: 35%;">Ítem</th>
                <th style="width: 12%;">Estado</th>
                <th style="width: 43%;">Comentarios</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items_por_cliente as $item): ?>
            <tr>
                <td><strong><?= esc($item['codigo_item']) ?></strong></td>
                <td>
                    <strong><?= esc($item['titulo']) ?></strong>
                    <?php if (!empty($item['descripcion'])): ?>
                        <br><span style="font-size: 8pt; color: #666;"><?= esc($item['descripcion']) ?></span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?php
                    $calificacion = $item['calificacion'] ?? 'no_aplica';
                    $badges = [
                        'cumple' => '<span class="badge badge-cumple">CUMPLE</span>',
                        'parcial' => '<span class="badge badge-parcial">PARCIAL</span>',
                        'no_cumple' => '<span class="badge badge-no-cumple">NO CUMPLE</span>',
                        'no_aplica' => '<span class="badge badge-no-aplica">NO APLICA</span>',
                    ];
                    echo $badges[$calificacion] ?? '';
                    ?>
                </td>
                <td>
                    <?php if (!empty($item['comentario_proveedor_cliente'])): ?>
                        <div class="comentario">
                            <span class="comentario-label">Proveedor:</span>
                            <?= nl2br(esc($item['comentario_proveedor_cliente'])) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($item['comentario_cliente'])): ?>
                        <div class="comentario">
                            <span class="comentario-label">Consultor:</span>
                            <?= nl2br(esc($item['comentario_cliente'])) ?>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- SECCIÓN DE EVIDENCIAS -->
    <?php
    $todasEvidencias = [];

    // Recopilar evidencias globales
    if (!empty($items_globales)) {
        foreach ($items_globales as $item) {
            if (!empty($item['evidencias'])) {
                foreach ($item['evidencias'] as $evidencia) {
                    $evidencia['item_codigo'] = $item['codigo_item'];
                    $evidencia['item_titulo'] = $item['titulo'];
                    $todasEvidencias[] = $evidencia;
                }
            }
        }
    }

    // Recopilar evidencias por cliente
    if (!empty($items_por_cliente)) {
        foreach ($items_por_cliente as $item) {
            if (!empty($item['evidencias'])) {
                foreach ($item['evidencias'] as $evidencia) {
                    $evidencia['item_codigo'] = $item['codigo_item'];
                    $evidencia['item_titulo'] = $item['titulo'];
                    $todasEvidencias[] = $evidencia;
                }
            }
        }
    }
    ?>

    <?php if (!empty($todasEvidencias)): ?>
    <h2>Evidencias</h2>
    <div class="evidencias-section">
        <div class="evidencias-grid">
            <?php foreach ($todasEvidencias as $evidencia): ?>
            <div class="evidencia-item">
                <div class="evidencia-thumbnail">
                    <?php
                    $rutaArchivo = WRITEPATH . 'uploads/' . ($evidencia['ruta_archivo'] ?? '');
                    $nombreArchivo = $evidencia['nombre_original'] ?? 'archivo';

                    if (esImagen($nombreArchivo) && file_exists($rutaArchivo)):
                    ?>
                        <img src="<?= $rutaArchivo ?>" alt="<?= esc($nombreArchivo) ?>">
                    <?php else: ?>
                        <div class="file-icon">📄</div>
                    <?php endif; ?>
                </div>
                <div class="evidencia-info">
                    <div class="evidencia-nombre"><?= esc($nombreArchivo) ?></div>
                    <div class="evidencia-meta">
                        Ítem: <?= esc($evidencia['item_codigo']) ?> - <?= esc($evidencia['item_titulo']) ?>
                        <br>
                        Fecha: <?= formatoFecha($evidencia['created_at'] ?? null) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <h2>Evidencias</h2>
    <div class="sin-evidencias">No se adjuntaron evidencias en esta auditoría</div>
    <?php endif; ?>

    <!-- FIRMA DEL CONSULTOR -->
    <div class="firma-section">
        <?php if (!empty($auditoria['firma_path'])): ?>
            <?php
            $firmaPath = WRITEPATH . 'uploads/' . $auditoria['firma_path'];
            if (file_exists($firmaPath)):
            ?>
                <img src="<?= $firmaPath ?>" class="firma-img" alt="Firma Consultor">
            <?php endif; ?>
        <?php endif; ?>
        <div class="firma-line">
            <strong><?= esc($auditoria['consultor_nombre']) ?></strong><br>
            <span style="font-size: 8pt;">Consultor Auditor</span>
            <?php if (!empty($auditoria['licencia_sst'])): ?>
                <br><span style="font-size: 8pt;">Licencia SST: <?= esc($auditoria['licencia_sst']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER CON PAGINACIÓN -->
    <div class="footer">
        <div>Generado: <?= formatoFecha($fecha_generacion ?? date('Y-m-d H:i:s')) ?></div>
        <div class="page-number"></div>
        <div>Cycloid Talent SAS - Sistema de Gestión de Auditorías</div>
    </div>
</body>
</html>
