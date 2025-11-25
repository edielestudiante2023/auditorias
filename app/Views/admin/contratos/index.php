<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | Auditorías</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
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
                    <a class="nav-link" href="<?= site_url('admin/items') ?>">
                        <i class="bi bi-list-check"></i> Items Banco
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('admin/consultores') ?>">
                        <i class="bi bi-person-badge"></i> Consultores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('admin/clientes') ?>">
                        <i class="bi bi-building"></i> Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('admin/proveedores') ?>">
                        <!-- Cambio de ícono: reemplazado bi-truck por bi-shop para representar alianza con proveedores -->
                        <i class="bi bi-shop"></i> Proveedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?= site_url('admin/contratos') ?>">
                        <i class="bi bi-diagram-3"></i> Relaciones
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
    <?= view('partials/breadcrumbs', isset($breadcrumbs) ? ['breadcrumbs' => $breadcrumbs] : []) ?>

    <!-- Flash Messages -->
    <?= view('partials/flash') ?>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-diagram-3"></i> <?= esc($title) ?></h4>
            <small class="text-muted">Asigna proveedores de aseo y vigilancia a tus clientes para auditoría SG-SST</small>
        </div>
        <a href="<?= site_url('admin/contratos/crear') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Relación
        </a>
    </div>

    <!-- Statistics -->
    <?php
    $total = count($contratos);
    $activos = count(array_filter($contratos, fn($c) => $c['estado'] === 'activo'));
    $inactivos = $total - $activos;
    ?>

    <?php if ($total > 0): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Total Relaciones</h6>
                            <h3 class="mb-0"><?= $total ?></h3>
                        </div>
                        <div class="text-primary" style="font-size: 2rem;">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Activas</h6>
                            <h3 class="mb-0 text-success"><?= $activos ?></h3>
                        </div>
                        <div class="text-success" style="font-size: 2rem;">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Inactivas</h6>
                            <h3 class="mb-0 text-secondary"><?= $inactivos ?></h3>
                        </div>
                        <div class="text-secondary" style="font-size: 2rem;">
                            <i class="bi bi-x-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de contratos -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($contratos)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-diagram-3 text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">No hay relaciones cliente-proveedor registradas.</p>
                    <a href="<?= site_url('admin/contratos/crear') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Crear Primera Relación
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table id="tablaContratos" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Proveedor</th>
                                <th>Servicio</th>
                                <th>Consultor</th>
                                <th>Responsable Proveedor</th>
                                <th>Tipo de Auditoría</th>
                                <th>Fecha de Creación</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                            <tr class="filters">
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contratos as $contrato): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($contrato['cliente_nombre']) ?></strong>
                                        <?php if (!empty($contrato['cliente_email'])): ?>
                                            <br><small class="text-muted">
                                                <i class="bi bi-envelope"></i> <?= esc($contrato['cliente_email']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($contrato['proveedor_nombre']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($contrato['servicio_nombre'])): ?>
                                            <span class="badge bg-info">
                                                <i class="bi bi-briefcase"></i> <?= esc($contrato['servicio_nombre']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Sin asignar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-person-badge text-primary"></i>
                                        <?= esc($contrato['consultor_nombre']) ?>
                                    </td>
                                    <td>
                                        <i class="bi bi-person-circle text-success"></i>
                                        <strong><?= esc($contrato['usuario_responsable_nombre']) ?></strong>
                                        <br><small class="text-muted">
                                            <i class="bi bi-envelope-fill"></i> <?= esc($contrato['usuario_responsable_email']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $tipoTexto = '';
                                        $badgeClass = 'bg-info';
                                        if ($contrato['tipo_auditoria'] === 'basica') {
                                            $tipoTexto = 'Auditoría Básica SST';
                                            $badgeClass = 'bg-primary';
                                        } elseif ($contrato['tipo_auditoria'] === 'alto_riesgo') {
                                            $tipoTexto = 'Auditoría Integral SST - Alto Riesgo';
                                            $badgeClass = 'bg-danger';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= esc($tipoTexto) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($contrato['created_at'])): ?>
                                            <?= date('d/m/Y', strtotime($contrato['created_at'])) ?>
                                            <br><small class="text-muted"><?= date('H:i', strtotime($contrato['created_at'])) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($contrato['estado'] === 'activo'): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= site_url('admin/contratos/editar/' . $contrato['id_contrato']) ?>"
                                               class="btn btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    onclick="confirmarEliminar(<?= $contrato['id_contrato'] ?>, '<?= esc($contrato['cliente_nombre']) ?>', '<?= esc($contrato['proveedor_nombre']) ?>')"
                                                    title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
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

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" method="post" style="display: none;">
    <?= csrf_field() ?>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#tablaContratos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
                className: 'btn btn-success btn-sm',
                title: 'Relaciones Cliente-Proveedor',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            }
        ],
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 25,
        responsive: true,
        initComplete: function() {
            var api = this.api();

            api.columns().eq(0).each(function(colIdx) {
                var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                var input = $('input', cell);

                input.off('keyup change').on('keyup change', function() {
                    api.column(colIdx).search(this.value).draw();
                });
            });
        }
    });
});

function confirmarEliminar(id, cliente, proveedor) {
    const mensaje = `¿Está seguro de ELIMINAR la relación:\n\nCliente: "${cliente}"\nProveedor: "${proveedor}"\n\nNOTA: No se puede eliminar si tiene auditorías asociadas.`;

    if (confirm(mensaje)) {
        const form = document.getElementById('formEliminar');
        form.action = '<?= site_url('admin/contratos/eliminar/') ?>' + id;
        form.submit();
    }
}
</script>

</body>
</html>
