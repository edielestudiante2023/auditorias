<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría Global - Informe Interno</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }

        .header {
            background-color: #0056b3;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 20pt;
            margin-bottom: 10px;
        }

        h2 {
            color: #0056b3;
            font-size: 14pt;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 5px;
        }

        h3 {
            color: #333;
            font-size: 11pt;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        .info-section {
            background-color: #f8f9fa;
            padding: 12px;
            margin-bottom: 15px;
            border-left: 4px solid #0056b3;
        }

        .info-row {
            margin-bottom: 5px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }

        .porcentaje-box {
            background-color: #28a745;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 15px 0;
            border-radius: 5px;
        }

        .porcentaje-box.medio {
            background-color: #ffc107;
        }

        .porcentaje-box.bajo {
            background-color: #dc3545;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th {
            background-color: #0056b3;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        table td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .calificacion-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9pt;
            color: white;
        }

        .calificacion-cumple {
            background-color: #28a745;
        }

        .calificacion-parcial {
            background-color: #ffc107;
            color: #333;
        }

        .calificacion-no_cumple {
            background-color: #dc3545;
        }

        .calificacion-no_aplica {
            background-color: #6c757d;
        }

        .clientes-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .cliente-card {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }

        .cliente-card h4 {
            color: #0056b3;
            margin-bottom: 5px;
        }

        .firma-section {
            margin-top: 40px;
            text-align: center;
        }

        .firma-img {
            max-width: 200px;
            max-height: 80px;
            margin-bottom: 10px;
        }

        .firma-line {
            border-top: 1px solid #333;
            width: 250px;
            margin: 0 auto;
            padding-top: 5px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #666;
            text-align: center;
        }

        .confidencial-notice {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .summary-stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-box {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }

        .stat-box .number {
            font-size: 24pt;
            font-weight: bold;
            color: #0056b3;
        }

        .stat-box .label {
            font-size: 9pt;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO -->
    <div class="header">
        <h1>INFORME GLOBAL DE AUDITORÍA</h1>
        <p style="font-size: 12pt;">Documento Interno - Cycloid Talent SAS</p>
        <p style="font-size: 10pt; margin-top: 5px;">ID Auditoría: #<?= esc($auditoria['id']) ?></p>
    </div>

    <!-- AVISO DE CONFIDENCIALIDAD -->
    <div class="confidencial-notice">
        ⚠ DOCUMENTO INTERNO - CONFIDENCIAL<br>
        Este informe contiene información agregada de todos los clientes asignados a esta auditoría
    </div>

    <!-- INFORMACIÓN GENERAL -->
    <div class="info-section">
        <h3>Información del Proveedor Auditado</h3>
        <div class="info-row">
            <span class="info-label">Proveedor:</span>
            <span><?= esc($auditoria['proveedor_nombre']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">NIT:</span>
            <span><?= esc($auditoria['nit']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span><?= esc($auditoria['proveedor_email']) ?></span>
        </div>
    </div>

    <div class="info-section">
        <h3>Información de la Auditoría</h3>
        <div class="info-row">
            <span class="info-label">ID Auditoría:</span>
            <span>#<?= esc($auditoria['id']) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Estado:</span>
            <span><?= esc(ucfirst(str_replace('_', ' ', $auditoria['estado']))) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha Creación:</span>
            <span><?= date('d/m/Y', strtotime($auditoria['created_at'])) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha Cierre:</span>
            <span><?= date('d/m/Y H:i', strtotime($auditoria['updated_at'])) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Consultor:</span>
            <span><?= esc($auditoria['consultor_nombres'] . ' ' . $auditoria['consultor_apellidos']) ?></span>
        </div>
    </div>

    <!-- ESTADÍSTICAS RESUMIDAS -->
    <h2>Estadísticas de Evaluación</h2>
    <?php
    $totalItems = count($items);
    $cumple = count(array_filter($items, fn($i) => $i['calificacion'] === 'cumple'));
    $parcial = count(array_filter($items, fn($i) => $i['calificacion'] === 'parcial'));
    $noCumple = count(array_filter($items, fn($i) => $i['calificacion'] === 'no_cumple'));
    $noAplica = count(array_filter($items, fn($i) => $i['calificacion'] === 'no_aplica'));
    ?>
    <div class="summary-stats">
        <div class="stat-box">
            <div class="number"><?= $cumple ?></div>
            <div class="label">CUMPLE</div>
        </div>
        <div class="stat-box">
            <div class="number"><?= $parcial ?></div>
            <div class="label">PARCIAL</div>
        </div>
        <div class="stat-box">
            <div class="number"><?= $noCumple ?></div>
            <div class="label">NO CUMPLE</div>
        </div>
        <div class="stat-box">
            <div class="number"><?= $noAplica ?></div>
            <div class="label">NO APLICA</div>
        </div>
    </div>

    <!-- PORCENTAJE GLOBAL -->
    <?php
    $colorClass = '';
    if ($auditoria['porcentaje_cumplimiento'] >= 70) {
        $colorClass = '';
    } elseif ($auditoria['porcentaje_cumplimiento'] >= 40) {
        $colorClass = 'medio';
    } else {
        $colorClass = 'bajo';
    }
    ?>
    <div class="porcentaje-box <?= $colorClass ?>">
        PORCENTAJE GLOBAL DE CUMPLIMIENTO: <?= number_format($auditoria['porcentaje_cumplimiento'], 2) ?>%
    </div>

    <!-- CLIENTES ASIGNADOS -->
    <h2>Clientes Asignados (<?= count($clientes) ?>)</h2>
    <div class="clientes-grid">
        <?php foreach ($clientes as $cliente): ?>
            <div class="cliente-card">
                <h4><?= esc($cliente['razon_social']) ?></h4>
                <div class="info-row">
                    <span class="info-label">NIT:</span>
                    <span><?= esc($cliente['nit']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">% Cumplimiento:</span>
                    <span><strong><?= number_format($cliente['porcentaje_cumplimiento'], 2) ?>%</strong></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- TABLA DE ÍTEMS EVALUADOS -->
    <h2>Detalle de Evaluación Global por Ítem</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Código</th>
                <th style="width: 30%;">Ítem</th>
                <th style="width: 15%;">Calificación</th>
                <th style="width: 45%;">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><strong><?= esc($item['codigo_item']) ?></strong></td>
                <td>
                    <strong><?= esc($item['titulo']) ?></strong>
                    <?php if (!empty($item['descripcion'])): ?>
                        <br><span style="font-size: 9pt; color: #666;"><?= esc($item['descripcion']) ?></span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?php
                    $calificacionClass = 'calificacion-' . $item['calificacion'];
                    $calificacionText = [
                        'cumple' => 'CUMPLE',
                        'parcial' => 'PARCIAL',
                        'no_cumple' => 'NO CUMPLE',
                        'no_aplica' => 'NO APLICA'
                    ][$item['calificacion']] ?? '';
                    ?>
                    <span class="calificacion-badge <?= $calificacionClass ?>">
                        <?= $calificacionText ?>
                    </span>
                </td>
                <td>
                    <?php if (!empty($item['comentario_proveedor'])): ?>
                        <p><strong>Proveedor:</strong> <?= nl2br(esc($item['comentario_proveedor'])) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($item['comentario_consultor'])): ?>
                        <p><strong>Consultor:</strong> <?= nl2br(esc($item['comentario_consultor'])) ?></p>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- FIRMA DEL CONSULTOR -->
    <div class="firma-section">
        <?php if (!empty($auditoria['firma_path'])): ?>
            <?php
            $firmaPath = FCPATH . 'writable/uploads/' . $auditoria['firma_path'];
            if (file_exists($firmaPath)):
            ?>
                <img src="<?= $firmaPath ?>" class="firma-img" alt="Firma Consultor">
            <?php endif; ?>
        <?php endif; ?>
        <div class="firma-line">
            <strong><?= esc($auditoria['consultor_nombres'] . ' ' . $auditoria['consultor_apellidos']) ?></strong><br>
            <span style="font-size: 9pt;">Consultor Auditor</span><br>
            <span style="font-size: 8pt;"><?= esc($auditoria['consultor_email']) ?></span>
        </div>
    </div>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p>Documento generado automáticamente el <?= date('d/m/Y H:i:s', strtotime($fecha_generacion)) ?></p>
        <p>Cycloid Talent SAS - Sistema de Gestión de Auditorías a Proveedores</p>
        <p style="margin-top: 5px; font-weight: bold; color: #dc3545;">
            ⚠ DOCUMENTO CONFIDENCIAL - USO INTERNO EXCLUSIVO
        </p>
    </div>
</body>
</html>
