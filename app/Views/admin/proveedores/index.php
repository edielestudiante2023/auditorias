<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | Auditorías</title>
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
                    <a class="nav-link active" href="<?= site_url('admin/proveedores') ?>">
                        <!-- Cambio de ícono: reemplazado bi-truck por bi-shop para representar alianza con proveedores -->
                        <i class="bi bi-shop"></i> Proveedores
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
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <!-- Cambio de ícono: reemplazado bi-truck por bi-shop para representar alianza con proveedores -->
            <h4 class="mb-0"><i class="bi bi-shop"></i> <?= esc($title) ?></h4>
            <small class="text-muted">Administra los proveedores del sistema</small>
        </div>
        <form class="d-flex" role="search" method="get" action="<?= current_url() ?>">
            <input type="search" name="q" class="form-control me-2" placeholder="Buscar por Razón Social o NIT" value="<?= esc($q ?? '') ?>">
            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
        </form>
        <a href="<?= site_url('admin/proveedores/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Proveedor
        </a>
    </div>

    <!-- Statistics -->
    <?php
    $total = count($proveedores);
    $conUsuario = count(array_filter($proveedores, fn($p) => !empty($p['id_users'])));
    $conEmail = count(array_filter($proveedores, fn($p) => !empty($p['email_contacto'])));
    $conTelefono = count(array_filter($proveedores, fn($p) => !empty($p['telefono_contacto'])));
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
                            <!-- Cambio de ícono: reemplazado bi-truck por bi-shop para representar alianza con proveedores -->
                            <i class="bi bi-shop"></i>
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
                            <h6 class="text-muted mb-0">Con Usuario</h6>
                            <h3 class="mb-0 text-success"><?= $conUsuario ?></h3>
                        </div>
                        <div class="text-success" style="font-size: 2rem;">
                            <i class="bi bi-person-check"></i>
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
                            <h6 class="text-muted mb-0">Con Email</h6>
                            <h3 class="mb-0 text-info"><?= $conEmail ?></h3>
                        </div>
                        <div class="text-info" style="font-size: 2rem;">
                            <i class="bi bi-envelope"></i>
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
                            <h6 class="text-muted mb-0">Con Teléfono</h6>
                            <h3 class="mb-0 text-warning"><?= $conTelefono ?></h3>
                        </div>
                        <div class="text-warning" style="font-size: 2rem;">
                            <i class="bi bi-telephone"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de proveedores -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($proveedores)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">No hay proveedores registrados.</p>
                    <a href="<?= site_url('admin/proveedores/create') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Crear Primer Proveedor
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>Razón Social</th>
                                <th>NIT</th>
                                <th>Contacto</th>
                                <th>Usuarios Vinculados</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($proveedor['logo_path'])): ?>
                                            <img src="<?= base_url('writable/' . $proveedor['logo_path']) ?>" alt="Logo" class="me-2" style="width:32px;height:32px;object-fit:contain;border:1px solid #eee;">
                                        <?php endif; ?>
                                        <strong><?= esc($proveedor['razon_social']) ?></strong>
                                        <?php if (!empty($proveedor['observaciones'])): ?>
                                            <br>
                                            <small class="text-muted" title="<?= esc($proveedor['observaciones']) ?>">
                                                <i class="bi bi-info-circle"></i>
                                                <?= esc(strlen($proveedor['observaciones']) > 50 ? substr($proveedor['observaciones'], 0, 50) . '...' : $proveedor['observaciones']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= esc($proveedor['nit']) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($proveedor['email_contacto'])): ?>
                                            <div class="mb-1">
                                                <i class="bi bi-envelope text-muted"></i>
                                                <small><?= esc($proveedor['email_contacto']) ?></small>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($proveedor['telefono_contacto'])): ?>
                                            <div>
                                                <i class="bi bi-telephone text-muted"></i>
                                                <small><?= esc($proveedor['telefono_contacto']) ?></small>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (empty($proveedor['email_contacto']) && empty($proveedor['telefono_contacto'])): ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Obtener usuarios vinculados desde usuarios_proveedores
                                        $db = \Config\Database::connect();
                                        $usuarios = $db->table('usuarios_proveedores')
                                            ->select('users.nombre, users.email')
                                            ->join('users', 'users.id_users = usuarios_proveedores.id_users')
                                            ->where('usuarios_proveedores.id_proveedor', $proveedor['id_proveedor'])
                                            ->where('usuarios_proveedores.activo', 1)
                                            ->get()
                                            ->getResultArray();
                                        ?>
                                        <?php if (!empty($usuarios)): ?>
                                            <?php foreach ($usuarios as $usuario): ?>
                                                <div class="mb-1">
                                                    <i class="bi bi-person-circle text-success"></i>
                                                    <small><?= esc($usuario['nombre']) ?></small>
                                                    <br>
                                                    <small class="text-muted ms-3"><?= esc($usuario['email']) ?></small>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Sin usuarios</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (($proveedor['estado'] ?? 'activo') === 'activo'): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= site_url('admin/proveedores/' . $proveedor['id_proveedor'] . '/edit') ?>"
                                               class="btn btn-outline-primary"
                                               title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-danger"
                                                    onclick="confirmarEliminar(<?= $proveedor['id_proveedor'] ?>, '<?= esc($proveedor['razon_social']) ?>')"
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
                    <div class="mt-3">
                        <?= $pager->links('proveedores', 'default_full') ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
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
    const mensaje = `¿Está seguro de ELIMINAR al proveedor "${nombre}"?\n\nNOTA: No se puede eliminar si tiene contratos asociados.`;

    if (confirm(mensaje)) {
        const form = document.getElementById('formEliminar');
        form.action = '<?= site_url('admin/proveedores/') ?>' + id + '/delete';
        form.submit();
    }
}
</script>

</body>
</html>

