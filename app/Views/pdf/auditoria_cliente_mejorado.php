<?php
/**
 * Plantilla PDF mejorada para informe de auditoría por cliente
 *
 * Variables disponibles:
 * - $auditoria: Datos de la auditoría
 * - $cliente: Datos del cliente
 * - $porcentaje_cliente: Porcentaje de cumplimiento
 * - $items_globales: Ítems globales
 * - $items_por_cliente: Ítems específicos del cliente
 * - $fecha_generacion: Fecha de generación del PDF
 */

// Cargar helper de PDF
helper('pdf');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe de Auditoría - <?= escaparHTMLPdf($cliente['razon_social']) ?></title>
    <style>
        /* Configuración de página A4 */
        @page {
            size: A4;
            margin: 2cm 1.5cm 2.5cm 1.5cm;
        }

        /* Estilos generales */
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            line-height: 1.5;
        }

        /* Encabezado con logos */
        .header {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #0066cc;
            padding-bottom: 10px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
            padding: 5px;
        }

        .logo-container {
            text-align: center;
        }

        .logo-proveedor, .logo-cliente {
            max-width: 120px;
            max-height: 80px;
            height: auto;
        }

        .header-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            color: #0066cc;
            padding: 10px 0;
        }

        .header-subtitle {
            text-align: center;
            font-size: 11pt;
            color: #666;
            margin-top: 5px;
        }

        /* Información general */
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0066cc;
            padding: 12px;
            margin: 15px 0;
        }

        .info-row {
            margin: 6px 0;
            font-size: 9.5pt;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
            color: #0066cc;
        }

        .info-value {
            display: inline;
        }

        /* Cuadro de porcentaje */
        .porcentaje-box {
            background-color: <?= $porcentaje_cliente >= 80 ? '#d4edda' : ($porcentaje_cliente >= 60 ? '#fff3cd' : '#f8d7da') ?>;
            border: 3px solid <?= $porcentaje_cliente >= 80 ? '#28a745' : ($porcentaje_cliente >= 60 ? '#ffc107' : '#dc3545') ?>;
            color: <?= $porcentaje_cliente >= 80 ? '#155724' : ($porcentaje_cliente >= 60 ? '#856404' : '#721c24') ?>;
            text-align: center;
            padding: 15px;
            margin: 20px auto;
            width: 60%;
            font-size: 20pt;
            font-weight: bold;
            border-radius: 8px;
        }

        .porcentaje-label {
            font-size: 10pt;
            margin-top: 5px;
            display: block;
        }

        /* Títulos de sección */
        .section-title {
            background-color: #0066cc;
            color: white;
            padding: 10px 15px;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 12pt;
            font-weight: bold;
            border-radius: 4px;
        }

        /* Tabla de ítems */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9pt;
        }

        .items-table thead {
            background-color: #0066cc;
            color: white;
        }

        .items-table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
        }

        .items-table tbody tr {
            border-bottom: 1px solid #dee2e6;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .items-table td {
            padding: 10px 8px;
            vertical-align: top;
        }

        /* Calificación */
        .calificacion-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11pt;
            color: white;
            text-align: center;
            min-width: 40px;
        }

        .calificacion-A {
            background-color: #28a745;
        }

        .calificacion-B {
            background-color: #ffc107;
            color: #333;
        }

        .calificacion-C {
            background-color: #dc3545;
        }

        .calificacion-N {
            background-color: #6c757d;
        }

        /* Comentarios */
        .comentario-box {
            background-color: #f8f9fa;
            padding: 8px;
            margin: 5px 0;
            border-left: 3px solid #0066cc;
            font-size: 8.5pt;
            line-height: 1.4;
        }

        .comentario-label {
            font-weight: bold;
            color: #0066cc;
            display: block;
            margin-bottom: 3px;
        }

        /* Sección de evidencias */
        .evidencias-section {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .evidencias-title {
            font-weight: bold;
            color: #0066cc;
            margin-bottom: 10px;
            font-size: 10pt;
        }

        .evidencias-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .evidencia-item {
            display: table-row;
            border-bottom: 1px solid #dee2e6;
        }

        .evidencia-cell {
            display: table-cell;
            padding: 8px;
            vertical-align: middle;
        }

        .evidencia-thumbnail {
            max-width: 100px;
            max-height: 100px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .evidencia-icon {
            font-size: 24pt;
            color: #0066cc;
        }

        .evidencia-info {
            font-size: 8.5pt;
        }

        .evidencia-nombre {
            font-weight: bold;
            color: #333;
            word-wrap: break-word;
        }

        .evidencia-detalles {
            color: #666;
            margin-top: 3px;
        }

        /* Pie de página */
        .footer {
            position: fixed;
            bottom: 1cm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }

        .footer .page-number:after {
            content: counter(page);
        }

        /* Saltos de página */
        .item-block {
            page-break-inside: avoid;
            margin-bottom: 20px;
        }

        .section-break {
            page-break-before: always;
        }

        /* Tabla compacta para detalles de ítem */
        .item-detail-table {
            width: 100%;
            margin: 10px 0;
        }

        .item-detail-table td {
            padding: 5px;
            vertical-align: top;
        }

        .item-detail-label {
            width: 120px;
            font-weight: bold;
            color: #0066cc;
        }

        /* Firma del consultor */
        .firma-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .firma-box {
            text-align: center;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin: 40px auto 0 auto;
            width: 300px;
        }

        .firma-imagen {
            max-width: 200px;
            max-height: 100px;
            margin-bottom: 10px;
        }

        .firma-nombre {
            font-weight: bold;
        }

        .firma-licencia {
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td width="25%" class="logo-container">
                    <?php if (!empty($auditoria['logo_proveedor_path'])): ?>
                        <img src="<?= obtenerRutaAbsolutaArchivo($auditoria['logo_proveedor_path']) ?>"
                             alt="Logo Proveedor"
                             class="logo-proveedor">
                    <?php else: ?>
                        <div style="font-size: 9pt; color: #999;">
                            Logo Proveedor
                        </div>
                    <?php endif; ?>
                </td>
                <td width="50%" style="text-align: center;">
                    <div class="header-title">INFORME DE AUDITORÍA</div>
                    <div class="header-subtitle"><?= escaparHTMLPdf($cliente['razon_social']) ?></div>
                </td>
                <td width="25%" class="logo-container">
                    <?php if (!empty($cliente['logo_path'])): ?>
                        <img src="<?= obtenerRutaAbsolutaArchivo($cliente['logo_path']) ?>"
                             alt="Logo Cliente"
                             class="logo-cliente">
                    <?php else: ?>
                        <div style="font-size: 9pt; color: #999;">
                            Logo Cliente
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- INFORMACIÓN GENERAL -->
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Código de Auditoría:</span>
            <span class="info-value"><?= formatearCodigoAuditoria($auditoria['id_auditoria'], $auditoria['codigo_formato'] ?? null) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Proveedor:</span>
            <span class="info-value"><?= escaparHTMLPdf($auditoria['proveedor_nombre']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">NIT Proveedor:</span>
            <span class="info-value"><?= formatearNIT($auditoria['proveedor_nit']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span class="info-value"><?= escaparHTMLPdf($cliente['razon_social']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">NIT Cliente:</span>
            <span class="info-value"><?= formatearNIT($cliente['nit']) ?></span>
        </div>
        <?php if (!empty($auditoria['id_contrato'])): ?>
        <div class="info-row">
            <span class="info-label">Contrato No.:</span>
            <span class="info-value"><?= str_pad($auditoria['id_contrato'], 6, '0', STR_PAD_LEFT) ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="info-label">Fecha de Generación:</span>
            <span class="info-value"><?= formatearFechaHoraPdf($fecha_generacion) ?></span>
        </div>
        <?php if (!empty($auditoria['fecha_programada'])): ?>
        <div class="info-row">
            <span class="info-label">Fecha Programada:</span>
            <span class="info-value"><?= formatearFechaPdf($auditoria['fecha_programada']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- PORCENTAJE DE CUMPLIMIENTO -->
    <div class="porcentaje-box">
        <?= number_format($porcentaje_cliente, 1) ?>%
        <span class="porcentaje-label">Cumplimiento General</span>
    </div>

    <!-- ÍTEMS GLOBALES -->
    <?php if (!empty($items_globales)): ?>
    <div class="section-title">📋 ÍTEMS GLOBALES</div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 12%;">Código</th>
                <th style="width: 25%;">Ítem</th>
                <th style="width: 10%;">Estado</th>
                <th style="width: 53%;">Comentarios</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items_globales as $item): ?>
            <tr>
                <td style="font-weight: bold; color: #0066cc;">
                    <?= escaparHTMLPdf($item['codigo_item'] ?? 'N/A') ?>
                </td>
                <td>
                    <strong><?= escaparHTMLPdf($item['titulo']) ?></strong>
                    <?php if (!empty($item['descripcion'])): ?>
                    <div style="font-size: 8pt; color: #666; margin-top: 3px;">
                        <?= escaparHTMLPdf(truncarTextoPdf($item['descripcion'], 80)) ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?php
                    $calificacion = strtoupper($item['calificacion'] ?? 'N');
                    $claseCalificacion = 'calificacion-' . $calificacion;
                    ?>
                    <span class="calificacion-badge <?= $claseCalificacion ?>">
                        <?= $calificacion !== 'N' ? $calificacion : '-' ?>
                    </span>
                </td>
                <td>
                    <?php if (!empty($item['comentario_proveedor'])): ?>
                    <div class="comentario-box">
                        <span class="comentario-label">Proveedor:</span>
                        <?= escaparHTMLPdf($item['comentario_proveedor']) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($item['comentario_consultor'])): ?>
                    <div class="comentario-box" style="border-left-color: #28a745;">
                        <span class="comentario-label" style="color: #28a745;">Consultor:</span>
                        <?= escaparHTMLPdf($item['comentario_consultor']) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($item['comentario_proveedor']) && empty($item['comentario_consultor'])): ?>
                    <span style="color: #999; font-style: italic;">Sin comentarios</span>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Evidencias del ítem global -->
            <?php if (tieneEvidencias($item['evidencias'])): ?>
            <tr>
                <td colspan="4">
                    <div class="evidencias-section">
                        <div class="evidencias-title">
                            📎 Evidencias (<?= contarEvidencias($item['evidencias']) ?>)
                        </div>
                        <div class="evidencias-grid">
                            <?php foreach ($item['evidencias'] as $evidencia): ?>
                            <div class="evidencia-item">
                                <div class="evidencia-cell" style="width: 120px;">
                                    <?php if (esImagenPdf($evidencia['nombre_archivo_original'])): ?>
                                        <?php
                                        $miniatura = generarMiniaturaBase64($evidencia['ruta_archivo'], 100, 100);
                                        if ($miniatura):
                                        ?>
                                            <img src="<?= $miniatura ?>"
                                                 alt="Evidencia"
                                                 class="evidencia-thumbnail">
                                        <?php else: ?>
                                            <span class="evidencia-icon"><?= obtenerIconoArchivoPdf($evidencia['nombre_archivo_original']) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="evidencia-icon"><?= obtenerIconoArchivoPdf($evidencia['nombre_archivo_original']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="evidencia-cell">
                                    <div class="evidencia-info">
                                        <div class="evidencia-nombre">
                                            <?= escaparHTMLPdf($evidencia['nombre_archivo_original']) ?>
                                        </div>
                                        <div class="evidencia-detalles">
                                            Tipo: <?= strtoupper(obtenerExtensionArchivo($evidencia['nombre_archivo_original'])) ?>
                                            | Tamaño: <?= formatearTamanioArchivo($evidencia['tamanio_bytes'] ?? null) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- SALTO DE PÁGINA -->
    <?php if (!empty($items_por_cliente)): ?>
    <div class="section-break"></div>
    <?php endif; ?>

    <!-- ÍTEMS POR CLIENTE -->
    <?php if (!empty($items_por_cliente)): ?>
    <div class="section-title">🏢 ÍTEMS ESPECÍFICOS DEL CLIENTE</div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 12%;">Código</th>
                <th style="width: 25%;">Ítem</th>
                <th style="width: 10%;">Estado</th>
                <th style="width: 53%;">Comentarios</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items_por_cliente as $item): ?>
            <tr>
                <td style="font-weight: bold; color: #0066cc;">
                    <?= escaparHTMLPdf($item['codigo_item'] ?? 'N/A') ?>
                </td>
                <td>
                    <strong><?= escaparHTMLPdf($item['titulo']) ?></strong>
                    <?php if (!empty($item['descripcion'])): ?>
                    <div style="font-size: 8pt; color: #666; margin-top: 3px;">
                        <?= escaparHTMLPdf(truncarTextoPdf($item['descripcion'], 80)) ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?php
                    $calificacion = strtoupper($item['calificacion'] ?? 'N');
                    $claseCalificacion = 'calificacion-' . $calificacion;
                    ?>
                    <span class="calificacion-badge <?= $claseCalificacion ?>">
                        <?= $calificacion !== 'N' ? $calificacion : '-' ?>
                    </span>
                </td>
                <td>
                    <?php if (!empty($item['comentario_proveedor_cliente'])): ?>
                    <div class="comentario-box">
                        <span class="comentario-label">Proveedor:</span>
                        <?= escaparHTMLPdf($item['comentario_proveedor_cliente']) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($item['comentario_cliente'])): ?>
                    <div class="comentario-box" style="border-left-color: #28a745;">
                        <span class="comentario-label" style="color: #28a745;">Consultor:</span>
                        <?= escaparHTMLPdf($item['comentario_cliente']) ?>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($item['comentario_proveedor_cliente']) && empty($item['comentario_cliente'])): ?>
                    <span style="color: #999; font-style: italic;">Sin comentarios</span>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Evidencias del ítem por cliente -->
            <?php if (tieneEvidencias($item['evidencias'])): ?>
            <tr>
                <td colspan="4">
                    <div class="evidencias-section">
                        <div class="evidencias-title">
                            📎 Evidencias (<?= contarEvidencias($item['evidencias']) ?>)
                        </div>
                        <div class="evidencias-grid">
                            <?php foreach ($item['evidencias'] as $evidencia): ?>
                            <div class="evidencia-item">
                                <div class="evidencia-cell" style="width: 120px;">
                                    <?php if (esImagenPdf($evidencia['nombre_archivo_original'])): ?>
                                        <?php
                                        $miniatura = generarMiniaturaBase64($evidencia['ruta_archivo'], 100, 100);
                                        if ($miniatura):
                                        ?>
                                            <img src="<?= $miniatura ?>"
                                                 alt="Evidencia"
                                                 class="evidencia-thumbnail">
                                        <?php else: ?>
                                            <span class="evidencia-icon"><?= obtenerIconoArchivoPdf($evidencia['nombre_archivo_original']) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="evidencia-icon"><?= obtenerIconoArchivoPdf($evidencia['nombre_archivo_original']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="evidencia-cell">
                                    <div class="evidencia-info">
                                        <div class="evidencia-nombre">
                                            <?= escaparHTMLPdf($evidencia['nombre_archivo_original']) ?>
                                        </div>
                                        <div class="evidencia-detalles">
                                            Tipo: <?= strtoupper(obtenerExtensionArchivo($evidencia['nombre_archivo_original'])) ?>
                                            | Tamaño: <?= formatearTamanioArchivo($evidencia['tamanio_bytes'] ?? null) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- FIRMA DEL CONSULTOR -->
    <?php if (!empty($auditoria['consultor_nombre'])): ?>
    <div class="firma-section">
        <div class="firma-box">
            <?php if (!empty($auditoria['firma_path'])): ?>
            <img src="<?= obtenerRutaAbsolutaArchivo($auditoria['firma_path']) ?>"
                 alt="Firma"
                 class="firma-imagen">
            <?php endif; ?>
            <div class="firma-nombre"><?= escaparHTMLPdf($auditoria['consultor_nombre']) ?></div>
            <div>Consultor SST</div>
            <?php if (!empty($auditoria['licencia_sst'])): ?>
            <div class="firma-licencia">Licencia: <?= escaparHTMLPdf($auditoria['licencia_sst']) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <div>
            Documento generado el <?= formatearFechaHoraPdf($fecha_generacion) ?>
            | Página <span class="page-number"></span>
        </div>
        <div style="margin-top: 5px; font-size: 7pt;">
            Este documento es confidencial y de uso exclusivo del cliente.
        </div>
    </div>

    <!-- Script para numeración de páginas -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $size = 8;
            $font = $fontMetrics->getFont("Arial");
            $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 40;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>
