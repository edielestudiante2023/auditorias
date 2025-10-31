<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Usuarios') ?> | Auditorías</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= site_url('admin/dashboard') ?>">
            <i class="bi bi-shield-check"></i> Auditorías - Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Salir</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Usuarios</li>
        </ol>
    </nav>

    <!-- Flash Messages -->
    <?= view('partials/flash') ?>

    <!-- Header con filtros -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="mb-0"><i class="bi bi-people"></i> <?= esc($title ?? 'Usuarios') ?></h4>
            <small class="text-muted">Gestiona los usuarios del sistema</small>
        </div>
        <form class="d-flex align-items-center gap-2" method="get" action="<?= current_url() ?>">
            <select name="rol" class="form-select" onchange="this.form.submit()">
                <option value="">Todos los roles</option>
                <option value="1" <?= ($f_rol ?? '')==='1'?'selected':'' ?>>Super Admin</option>
                <option value="2" <?= ($f_rol ?? '')==='2'?'selected':'' ?>>Consultor</option>
                <option value="3" <?= ($f_rol ?? '')==='3'?'selected':'' ?>>Proveedor</option>
            </select>
            <select name="estado" class="form-select" onchange="this.form.submit()">
                <option value="">Todos</option>
                <option value="activo" <?= ($f_estado ?? '')==='activo'?'selected':'' ?>>Activo</option>
                <option value="inactivo" <?= ($f_estado ?? '')==='inactivo'?'selected':'' ?>>Inactivo</option>
            </select>
            <a href="<?= site_url('admin/usuarios/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo Usuario
            </a>
        </form>
    </div>

    <!-- Estadísticas -->
    <?php
    $total = count($usuarios);
    $activos = count(array_filter($usuarios, fn($u) => ($u['estado'] ?? 'inactivo') === 'activo'));
    $inactivos = $total - $activos;
    $admins = count(array_filter($usuarios, fn($u) => ($u['id_roles'] ?? 0) == 1));
    $consultores = count(array_filter($usuarios, fn($u) => ($u['id_roles'] ?? 0) == 2));
    $proveedores = count(array_filter($usuarios, fn($u) => ($u['id_roles'] ?? 0) == 3));
    ?>

    <?php if ($total > 0): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-primary"><?= $total ?></h3>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success"><?= $activos ?></h3>
                    <small class="text-muted">Activos</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-secondary"><?= $inactivos ?></h3>
                    <small class="text-muted">Inactivos</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-dark"><?= $admins ?></h3>
                    <small class="text-muted">Admins</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-info"><?= $consultores ?></h3>
                    <small class="text-muted">Consultores</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-warning"><?= $proveedores ?></h3>
                    <small class="text-muted">Proveedores</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de usuarios -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (empty($usuarios)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3">No hay usuarios registrados.</p>
                    <a href="<?= site_url('admin/usuarios/create') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Crear Primer Usuario
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th class="text-center">Rol</th>
                                <th class="text-center">Vinculado a</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <?php
                                $rid = (int)($u['id_roles'] ?? 0);
                                $badge = 'secondary';
                                $label = 'Desconocido';
                                $icon = 'person';

                                if ($rid===1) {
                                    $badge='dark';
                                    $label='Super Admin';
                                    $icon='shield-fill-check';
                                }
                                elseif ($rid===2) {
                                    $badge='info';
                                    $label='Consultor';
                                    $icon='person-badge';
                                }
                                elseif ($rid===3) {
                                    $badge='warning';
                                    $label='Proveedor';
                                    $icon='truck';
                                }
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar bg-<?= $badge ?> bg-opacity-10 rounded-circle p-2 me-2">
                                            <i class="bi bi-<?= $icon ?> text-<?= $badge ?>"></i>
                                        </div>
                                        <div>
                                            <strong><?= esc($u['nombre'] ?? '-') ?></strong>
                                            <br>
                                            <small class="text-muted">ID: <?= $u['id_users'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small><?= esc($u['email']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $badge ?>"><?= $label ?></span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    // Verificar vinculación
                                    $vinculado = '<span class="text-muted">-</span>';
                                    if ($rid == 2) {
                                        // Buscar si es consultor
                                        $db = \Config\Database::connect();
                                        $consultor = $db->table('consultores')
                                            ->where('id_users', $u['id_users'])
                                            ->get()
                                            ->getRowArray();
                                        if ($consultor) {
                                            $nombreConsultor = esc($consultor['nombre_completo'] ?? 'Consultor #' . $consultor['id_consultor']);
                                            $vinculado = '<a href="' . site_url('admin/consultores/editar/' . $consultor['id_consultor']) . '" class="badge bg-info text-decoration-none" title="Ver consultor"><i class="bi bi-person-badge"></i> ' . $nombreConsultor . '</a>';
                                        }
                                    } elseif ($rid == 3) {
                                        // Buscar proveedores vinculados (tabla intermedia)
                                        $db = \Config\Database::connect();
                                        $proveedores = $db->table('usuarios_proveedores')
                                            ->select('proveedores.id_proveedor, proveedores.razon_social')
                                            ->join('proveedores', 'proveedores.id_proveedor = usuarios_proveedores.id_proveedor')
                                            ->where('usuarios_proveedores.id_users', $u['id_users'])
                                            ->where('usuarios_proveedores.activo', 1)
                                            ->get()
                                            ->getResultArray();

                                        if (!empty($proveedores)) {
                                            $badges = [];
                                            foreach ($proveedores as $prov) {
                                                $razonSocial = esc($prov['razon_social']);
                                                // Cambio de ícono: usando bi-shop para representar proveedor comercial
                                                $badges[] = '<a href="' . site_url('admin/proveedores/' . $prov['id_proveedor'] . '/edit') . '" class="badge bg-warning text-decoration-none" title="Ver proveedor"><i class="bi bi-shop"></i> ' . $razonSocial . '</a>';
                                            }
                                            $vinculado = implode(' ', $badges);
                                        }
                                    }
                                    echo $vinculado;
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if (($u['estado'] ?? 'inactivo') === 'activo'): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-x-circle"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?= site_url('admin/usuarios/' . $u['id_users'] . '/edit') ?>"
                                           class="btn btn-outline-primary"
                                           title="Editar usuario">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-outline-warning"
                                                onclick="resetearPassword(<?= $u['id_users'] ?>, '<?= esc($u['nombre']) ?>')"
                                                title="Resetear contraseña">
                                            <i class="bi bi-key"></i>
                                        </button>
                                        <?php if ($u['id_users'] != userId()): ?>
                                        <button type="button"
                                                class="btn btn-outline-danger"
                                                onclick="confirmarEliminar(<?= $u['id_users'] ?>, '<?= esc($u['nombre']) ?>')"
                                                title="Eliminar usuario">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <?php endif; ?>
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

<!-- Formulario oculto para resetear contraseña -->
<form id="formResetPassword" method="post" style="display: none;">
    <?= csrf_field() ?>
</form>

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" method="post" style="display: none;">
    <?= csrf_field() ?>
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function resetearPassword(id, nombre) {
    const mensaje = `¿Está seguro de resetear la contraseña del usuario "${nombre}"?\n\nSe generará una nueva contraseña temporal que se mostrará en pantalla.`;

    if (confirm(mensaje)) {
        const form = document.getElementById('formResetPassword');
        form.action = '<?= site_url('admin/usuarios/') ?>' + id + '/reset-password';
        form.submit();
    }
}

function confirmarEliminar(id, nombre) {
    const mensaje = `¿Está seguro de ELIMINAR al usuario "${nombre}"?\n\nEsta acción no se puede deshacer.\n\nNOTA: No se puede eliminar si está vinculado a un consultor o proveedor.`;

    if (confirm(mensaje)) {
        const form = document.getElementById('formEliminar');
        form.action = '<?= site_url('admin/usuarios/') ?>' + id + '/delete';
        form.submit();
    }
}
</script>
</body>
</html>
