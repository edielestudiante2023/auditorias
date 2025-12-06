<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | Auditorías</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-card.enviados { border-left-color: #198754; }
        .stat-card.pendientes { border-left-color: #ffc107; }
        .stat-card.fallidos { border-left-color: #dc3545; }
        .stat-card.total { border-left-color: #0d6efd; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= site_url('admin/dashboard') ?>">
            <i class="bi bi-shield-check"></i> Auditorías - Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('admin/auditorias') ?>">
                        <i class="bi bi-clipboard-check"></i> Auditorías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?= site_url('admin/reportes/emails-clientes') ?>">
                        <i class="bi bi-envelope-check"></i> Emails Clientes
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('logout') ?>">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Emails Clientes</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-envelope-check"></i> <?= esc($title) ?></h4>
        <?= view('partials/filtro_anio', ['anio_actual' => $anio, 'url_base' => site_url('admin/reportes/emails-clientes')]) ?>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card total shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Clientes (Cerradas)</h6>
                            <h3 class="mb-0"><?= $estadisticas['total_clientes_cerradas'] ?></h3>
                        </div>
                        <i class="bi bi-building fs-1 text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card enviados shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Emails Enviados</h6>
                            <h3 class="mb-0 text-success"><?= $estadisticas['emails_enviados'] ?></h3>
                        </div>
                        <i class="bi bi-check-circle fs-1 text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card pendientes shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pendientes</h6>
                            <h3 class="mb-0 text-warning"><?= $estadisticas['pendientes'] ?></h3>
                        </div>
                        <i class="bi bi-clock fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card fallidos shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Fallidos</h6>
                            <h3 class="mb-0 text-danger"><?= $estadisticas['emails_fallidos'] ?></h3>
                        </div>
                        <i class="bi bi-x-circle fs-1 text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de reporte -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-table"></i> Detalle por Cliente</span>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary" id="filtroTodos">Todos</button>
                <button type="button" class="btn btn-outline-success" id="filtroEnviados">Enviados</button>
                <button type="button" class="btn btn-outline-warning" id="filtroPendientes">Pendientes</button>
                <button type="button" class="btn btn-outline-danger" id="filtroFallidos">Fallidos</button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($reporte)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No hay auditorías cerradas</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaReporte">
                        <thead class="table-light">
                            <tr>
                                <th>Auditoría</th>
                                <th>Fecha Cierre</th>
                                <th>Proveedor</th>
                                <th>Cliente</th>
                                <th>% Cumpl.</th>
                                <th>Estado Email</th>
                                <th>Fecha Envío</th>
                                <th>Email Destino</th>
                                <th>Consultor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reporte as $row): ?>
                                <tr class="<?= $row['email_enviado'] ? ($row['estado_envio'] === 'ok' ? 'estado-enviado' : 'estado-fallido') : 'estado-pendiente' ?>">
                                    <td>
                                        <a href="<?= site_url('admin/auditorias/detalle/' . $row['id_auditoria']) ?>">
                                            #<?= $row['id_auditoria'] ?>
                                        </a>
                                    </td>
                                    <td><?= $row['fecha_cierre'] ? date('d/m/Y', strtotime($row['fecha_cierre'])) : '-' ?></td>
                                    <td>
                                        <strong><?= esc($row['proveedor_nombre']) ?></strong>
                                        <br><small class="text-muted"><?= esc($row['proveedor_nit']) ?></small>
                                    </td>
                                    <td><?= esc($row['cliente_nombre']) ?></td>
                                    <td>
                                        <?php
                                            $porcentaje = $row['porcentaje_cumplimiento'] ?? 0;
                                            $colorClass = $porcentaje >= 80 ? 'success' : ($porcentaje >= 60 ? 'warning' : 'danger');
                                        ?>
                                        <span class="badge bg-<?= $colorClass ?>"><?= number_format($porcentaje, 1) ?>%</span>
                                    </td>
                                    <td>
                                        <?php if ($row['email_enviado']): ?>
                                            <?php if ($row['estado_envio'] === 'ok'): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Enviado</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger" title="<?= esc($row['detalle_error']) ?>">
                                                    <i class="bi bi-x-circle"></i> Fallido
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Pendiente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $row['fecha_envio_email'] ? date('d/m/Y H:i', strtotime($row['fecha_envio_email'])) : '-' ?>
                                    </td>
                                    <td>
                                        <?php if ($row['email_destinatario']): ?>
                                            <small><?= esc($row['email_destinatario']) ?></small>
                                        <?php else: ?>
                                            <small class="text-muted"><?= esc($row['cliente_email']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= esc($row['consultor_nombre']) ?></small></td>
                                    <td>
                                        <?php if (!$row['email_enviado'] || $row['estado_envio'] === 'fallido'): ?>
                                            <a href="<?= site_url('consultor/auditoria/' . $row['id_auditoria']) ?>"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Ir a auditoría para reenviar">
                                                <i class="bi bi-send"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#tablaReporte').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        order: [[1, 'desc']],
        pageLength: 25
    });

    // Filtros
    $('#filtroTodos').click(function() {
        table.search('').draw();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });

    $('#filtroEnviados').click(function() {
        table.search('Enviado').draw();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });

    $('#filtroPendientes').click(function() {
        table.search('Pendiente').draw();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });

    $('#filtroFallidos').click(function() {
        table.search('Fallido').draw();
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
});
</script>
</body>
</html>
