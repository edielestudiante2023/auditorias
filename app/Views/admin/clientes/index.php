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
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
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
                    <a class="nav-link active" href="<?= site_url('admin/clientes') ?>">
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="bi bi-building"></i> <?= esc($title) ?></h4>
        <a href="<?= site_url('admin/clientes/crear') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo
        </a>
    </div>

    <?php if (empty($clientes)): ?>
        <?= view('partials/_empty', [
            'icon' => 'bi-building',
            'title' => 'No hay clientes registrados',
            'message' => 'Comienza agregando tu primer cliente',
            'button_text' => 'Crear Primer Cliente',
            'button_url' => 'admin/clientes/crear'
        ]) ?>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaClientes" class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th width="80px">Logo</th>
                                <th>Razón Social</th>
                                <th width="15%">NIT</th>
                                <th width="20%">Email / Teléfono</th>
                                <th width="10%" class="text-center">Estado</th>
                                <th width="15%" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($cliente['logo_cliente_path']) && uploadExists($cliente['logo_cliente_path'])):
                                            $filename = basename($cliente['logo_cliente_path']);
                                            $logoUrl = site_url('files/logo/' . $filename);
                                        ?>
                                            <img src="<?= $logoUrl ?>"
                                                 alt="Logo <?= esc($cliente['razon_social']) ?>"
                                                 class="img-thumbnail"
                                                 style="width: 60px; height: 60px; object-fit: contain; cursor: pointer;"
                                                 onclick="verLogo('<?= $logoUrl ?>', '<?= esc($cliente['razon_social']) ?>')">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                 style="width: 60px; height: 60px; border: 1px dashed #ccc;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= esc($cliente['razon_social']) ?></strong>
                                        <?php if (!empty($cliente['direccion'])): ?>
                                            <br><small class="text-muted">
                                                <i class="bi bi-geo-alt"></i> <?= esc($cliente['direccion']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($cliente['nit']) ?></td>
                                    <td>
                                        <?php if (!empty($cliente['email_contacto'])): ?>
                                            <i class="bi bi-envelope"></i> <?= esc($cliente['email_contacto']) ?><br>
                                        <?php endif; ?>
                                        <?php if (!empty($cliente['telefono_contacto'])): ?>
                                            <i class="bi bi-telephone"></i> <?= esc($cliente['telefono_contacto']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($cliente['estado'] === 'activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="<?= site_url('admin/clientes/' . $cliente['id_cliente'] . '/editar') ?>"
                                               class="btn btn-sm btn-warning"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="confirmarEliminacion(<?= $cliente['id_cliente'] ?>, '<?= esc($cliente['razon_social']) ?>')"
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
                <?php if (isset($pager)) : ?>
                    <div class="mt-3" id="paginacionServidor">
                        <?= $pager->links('clientes', 'default_full') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-primary">
                    <div class="card-body">
                        <h6 class="card-title text-muted mb-3">
                            <i class="bi bi-bar-chart"></i> Resumen
                        </h6>
                        <div class="row text-center">
                            <?php
                            $total = count($clientes);
                            $activos = count(array_filter($clientes, fn($c) => $c['estado'] === 'activo'));
                            $inactivos = $total - $activos;
                            $conLogo = count(array_filter($clientes, fn($c) => !empty($c['logo_cliente_path'])));
                            ?>
                            <div class="col-6 col-md-3">
                                <div class="border-end py-2">
                                    <h3 class="text-primary mb-0"><?= $total ?></h3>
                                    <small class="text-muted">Total Clientes</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border-end py-2">
                                    <h3 class="text-success mb-0"><?= $activos ?></h3>
                                    <small class="text-muted">Activos</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border-end py-2">
                                    <h3 class="text-secondary mb-0"><?= $inactivos ?></h3>
                                    <small class="text-muted">Inactivos</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="py-2">
                                    <h3 class="text-info mb-0"><?= $conLogo ?></h3>
                                    <small class="text-muted">Con Logo</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= view('admin/clientes/_delete_modal') ?>

<!-- Modal para ver logo en grande -->
<div class="modal fade" id="modalLogo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLogoTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalLogoImagen" src="" alt="Logo" class="img-fluid" style="max-height: 400px;">
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));
    const modalLogo = new bootstrap.Modal(document.getElementById('modalLogo'));

    function confirmarEliminacion(id, nombre) {
        document.getElementById('clienteNombre').textContent = nombre;
        document.getElementById('formEliminar').action = '<?= site_url('admin/clientes/') ?>' + id + '/eliminar';
        modalEliminar.show();
    }

    function verLogo(url, nombre) {
        document.getElementById('modalLogoTitulo').textContent = 'Logo de ' + nombre;
        document.getElementById('modalLogoImagen').src = url;
        modalLogo.show();
    }

    // Inicializar DataTables
    <?php if (!empty($clientes)): ?>
    $(document).ready(function() {
        $('#tablaClientes').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            },
            pageLength: 10,
            order: [[1, 'asc']], // Ordenar por Razón Social
            columnDefs: [
                { orderable: false, targets: [0, 5] } // Logo y Acciones no ordenables
            ]
        });
        // Ocultar paginación del servidor
        $('#paginacionServidor').hide();
    });
    <?php endif; ?>
</script>
</body>
</html>
