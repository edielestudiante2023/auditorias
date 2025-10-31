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
                    <a class="nav-link active" href="<?= site_url('admin/consultores') ?>">
                        <i class="bi bi-person-badge"></i> Consultores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('admin/clientes') ?>">
                        <i class="bi bi-building"></i> Clientes
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

<div class="container py-4">
    <!-- Breadcrumbs -->
    <?= view('partials/breadcrumbs', isset($breadcrumbs) ? ['breadcrumbs' => $breadcrumbs] : []) ?>

    <!-- Flash Messages -->
    <?= view('partials/flash') ?>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="bi bi-person-badge"></i> <?= esc($title) ?></h4>
            <small class="text-muted">Administra los consultores auditores del sistema</small>
        </div>
        <a href="<?= site_url('admin/consultores/crear') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Consultor
        </a>
    </div>

    <!-- Statistics -->
    <?php
    $total = count($consultores);
    $activos = count(array_filter($consultores, fn($c) => $c['estado_usuario'] === 'activo'));
    $inactivos = $total - $activos;
    $conFirma = count(array_filter($consultores, fn($c) => !empty($c['firma_path'])));
    ?>

    <?php if ($total > 0): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Total</h6>
                            <h3 class="mb-0"><?= $total ?></h3>
                        </div>
                        <div class="text-primary" style="font-size: 2rem;">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Activos</h6>
                            <h3 class="mb-0 text-success"><?= $activos ?></h3>
                        </div>
                        <div class="text-success" style="font-size: 2rem;">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Inactivos</h6>
                            <h3 class="mb-0 text-danger"><?= $inactivos ?></h3>
                        </div>
                        <div class="text-danger" style="font-size: 2rem;">
                            <i class="bi bi-person-x-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-0">Con Firma</h6>
                            <h3 class="mb-0 text-info"><?= $conFirma ?></h3>
                        </div>
                        <div class="text-info" style="font-size: 2rem;">
                            <i class="bi bi-pen-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de consultores -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($consultores)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">No hay consultores registrados.</p>
                    <a href="<?= site_url('admin/consultores/crear') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Crear Primer Consultor
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Firma</th>
                                <th>Consultor</th>
                                <th>Documento</th>
                                <th>Licencia SST</th>
                                <th>Usuario</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consultores as $consultor): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($consultor['firma_path']) && uploadExists($consultor['firma_path'])):
                                            $filename = basename($consultor['firma_path']);
                                            $firmaUrl = site_url('files/firma/' . $filename);
                                        ?>
                                            <img src="<?= $firmaUrl ?>"
                                                 alt="Firma <?= esc($consultor['nombre_completo']) ?>"
                                                 class="img-thumbnail"
                                                 style="width: 60px; height: 60px; object-fit: contain; cursor: pointer;"
                                                 onclick="verFirma('<?= $firmaUrl ?>', '<?= esc($consultor['nombre_completo']) ?>')">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                 style="width: 60px; height: 60px; border: 1px dashed #ccc;">
                                                <i class="bi bi-pen text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($consultor['nombre_completo']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= esc($consultor['tipo_documento']) ?></span>
                                        <?= esc($consultor['numero_documento']) ?>
                                    </td>
                                    <td>
                                        <?= !empty($consultor['licencia_sst']) ? esc($consultor['licencia_sst']) : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= esc($consultor['email']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($consultor['estado_usuario'] === 'activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= site_url('admin/consultores/editar/' . $consultor['id_consultor']) ?>"
                                               class="btn btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    onclick="confirmarEliminar(<?= $consultor['id_consultor'] ?>, '<?= esc($consultor['nombre_completo']) ?>')"
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

<!-- Modal para ver firma ampliada -->
<div class="modal fade" id="firmaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="firmaModalLabel">Firma Digital</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="firmaModalImg" src="" alt="Firma" class="img-fluid" style="max-height: 400px;">
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" method="post" style="display: none;">
    <?= csrf_field() ?>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmarEliminar(id, nombre) {
    const mensaje = `¿Está seguro de ELIMINAR al consultor "${nombre}"?\n\nEsta acción eliminará también su firma digital (si existe).\n\nNOTA: No se puede eliminar si tiene auditorías asignadas.`;

    if (confirm(mensaje)) {
        const form = document.getElementById('formEliminar');
        form.action = '<?= site_url('admin/consultores/eliminar/') ?>' + id;
        form.submit();
    }
}

function verFirma(url, nombre) {
    document.getElementById('firmaModalImg').src = url;
    document.getElementById('firmaModalLabel').textContent = 'Firma de ' + nombre;
    new bootstrap.Modal(document.getElementById('firmaModal')).show();
}
</script>

</body>
</html>
