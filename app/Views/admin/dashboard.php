<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Panel Admin') ?> | Auditorías</title>

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
            <i class="bi bi-person-circle"></i> <?= esc($nombre) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-text"><small>Rol: <?= esc($rol) ?></small></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Salir</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Contenido principal -->
<div class="container py-4">

  <!-- Mensajes flash -->
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= esc(session()->getFlashdata('success')) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= esc(session()->getFlashdata('error')) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-speedometer2"></i> Panel de Administración</h4>
  </div>

  <?php
  // Detectar módulo activo basado en URL
  $currentSegment = service('uri')->getSegment(2);
  ?>

  <!-- Cards de acceso rápido -->
  <div class="row g-4">
    <!-- Ítems del Banco -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm <?= in_array($currentSegment, ['items-banco', 'items']) ? 'border-primary border-2' : '' ?>">
        <div class="card-body text-center position-relative">
          <span class="badge bg-primary position-absolute top-0 end-0 m-2"><?= $total_items_banco ?></span>
          <i class="bi bi-list-check text-primary" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Ítems del Banco</h5>
          <p class="card-text text-muted small">Total: <?= $total_items_banco ?></p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/items-banco') ?>" class="btn btn-primary" target="_blank">
              <i class="bi bi-arrow-right-circle"></i> Ir a Ítems
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Clientes -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm <?= $currentSegment === 'clientes' ? 'border-success border-2' : '' ?>">
        <div class="card-body text-center position-relative">
          <span class="badge bg-success position-absolute top-0 end-0 m-2"><?= $total_clientes ?></span>
          <i class="bi bi-building text-success" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Clientes</h5>
          <p class="card-text text-muted small">Total: <?= $total_clientes ?></p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/clientes') ?>" class="btn btn-success" target="_blank">
              <i class="bi bi-arrow-right-circle"></i> Ir a Clientes
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Proveedores -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm <?= $currentSegment === 'proveedores' ? 'border-warning border-2' : '' ?>">
        <div class="card-body text-center position-relative">
          <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2"><?= $total_proveedores ?></span>
          <!-- Cambio de ícono: usando bi-shop para representar proveedor comercial -->
          <i class="bi bi-shop text-warning" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Proveedores</h5>
          <p class="card-text text-muted small">Total: <?= $total_proveedores ?></p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/proveedores') ?>" class="btn btn-warning text-dark" target="_blank">
              <i class="bi bi-arrow-right-circle"></i> Ir a Proveedores
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Consultores -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm <?= $currentSegment === 'consultores' ? 'border-info border-2' : '' ?>">
        <div class="card-body text-center position-relative">
          <span class="badge bg-info position-absolute top-0 end-0 m-2"><?= $total_consultores ?></span>
          <i class="bi bi-person-badge text-info" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Consultores</h5>
          <p class="card-text text-muted small">Total: <?= $total_consultores ?></p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/consultores') ?>" class="btn btn-info text-white" target="_blank">
              <i class="bi bi-arrow-right-circle"></i> Ir a Consultores
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Relaciones Cliente-Proveedor -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm <?= $currentSegment === 'contratos' ? 'border-info border-2' : '' ?>">
        <div class="card-body text-center position-relative">
          <span class="badge bg-info position-absolute top-0 end-0 m-2"><?= $total_contratos ?></span>
          <i class="bi bi-diagram-3 text-info" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Relaciones Cliente-Proveedor</h5>
          <p class="card-text text-muted small">Total: <?= $total_contratos ?></p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/contratos') ?>" class="btn btn-info" target="_blank">
              <i class="bi bi-arrow-right-circle"></i> Ver Relaciones
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Usuarios -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm <?= $currentSegment === 'usuarios' ? 'border-secondary border-2' : '' ?>">
        <div class="card-body text-center position-relative">
          <span class="badge bg-secondary position-absolute top-0 end-0 m-2"><?= $total_usuarios ?></span>
          <i class="bi bi-people text-secondary" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Usuarios</h5>
          <p class="card-text text-muted small">Total: <?= $total_usuarios ?></p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/usuarios') ?>" class="btn btn-secondary" target="_blank">
              <i class="bi bi-arrow-right-circle"></i> Ir a Usuarios
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Servicios -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm <?= $currentSegment === 'servicios' ? 'border-primary border-2' : '' ?>">
        <div class="card-body text-center position-relative">
          <span class="badge bg-primary position-absolute top-0 end-0 m-2"><?= $total_servicios ?></span>
          <i class="bi bi-gear text-primary" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Servicios</h5>
          <p class="card-text text-muted small">Total: <?= $total_servicios ?></p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/servicios') ?>" class="btn btn-primary" target="_blank">
              <i class="bi bi-arrow-right-circle"></i> Ir a Servicios
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Sección de Supervisión de Auditorías -->
  <div class="mt-5 mb-3">
    <h5><i class="bi bi-graph-up"></i> Supervisión de Auditorías</h5>
    <p class="text-muted small">Monitoreo y seguimiento del proceso de auditorías</p>
  </div>

  <div class="row g-4">
    <!-- NUEVO: Reporte de Progreso en Tiempo Real -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm border-primary border-2">
        <div class="card-body text-center">
          <i class="bi bi-graph-up-arrow text-primary" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Reporte de Progreso</h5>
          <p class="card-text text-muted small">Monitoreo en tiempo real del avance de todas las auditorías</p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/auditorias/reporte-progreso') ?>" class="btn btn-primary" target="_blank">
              <i class="bi bi-speedometer2"></i> Ver Reporte
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Auditorías Completadas por Proveedores -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body text-center">
          <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Completadas por Proveedores</h5>
          <p class="card-text text-muted small">Auditorías cerradas esperando revisión</p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/auditorias/completadas-proveedores') ?>" class="btn btn-success" target="_blank">
              <i class="bi bi-arrow-right-circle"></i> Ver Completadas
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Auditorías Pendientes de Proveedores -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body text-center">
          <i class="bi bi-clipboard-check text-warning" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Pendientes - Proveedores</h5>
          <p class="card-text text-muted small">Auditorías asignadas a proveedores</p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/auditorias/pendientes-proveedores') ?>" class="btn btn-warning text-dark" target="_blank">
              <i class="bi bi-arrow-right-circle"></i> Ver Pendientes
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Auditorías en Revisión por Consultores -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body text-center">
          <i class="bi bi-eye text-info" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Revisión - Consultores</h5>
          <p class="card-text text-muted small">Todas las auditorías en el sistema</p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/auditorias/revision-consultores') ?>" class="btn btn-info text-white" target="_blank">
              <i class="bi bi-arrow-right-circle"></i> Ver Todas
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Sin permisos">
              <i class="bi bi-lock"></i> Sin permisos
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Auditorías Cerradas - Gestión y Reapertura -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm border-danger">
        <div class="card-body text-center">
          <i class="bi bi-lock-fill text-danger" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Auditorías Cerradas</h5>
          <p class="card-text text-muted small">Gestión y reapertura de auditorías finalizadas</p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/auditorias/cerradas') ?>" class="btn btn-danger text-white" target="_blank">
              <i class="bi bi-unlock"></i> Gestionar
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Solo SuperAdmin">
              <i class="bi bi-lock"></i> Solo SuperAdmin
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Auditoría de Cambios (Historial de Reaperturas) -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm border-secondary">
        <div class="card-body text-center">
          <i class="bi bi-clock-history text-secondary" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Auditoría de Cambios</h5>
          <p class="card-text text-muted small">Historial completo de reaperturas y modificaciones</p>
          <?php if ($rol_id == 1): ?>
            <a href="<?= site_url('admin/auditorias/historial-reaperturas') ?>" class="btn btn-secondary text-white" target="_blank">
              <i class="bi bi-list-ul"></i> Ver Historial
            </a>
          <?php else: ?>
            <button class="btn btn-secondary" disabled data-bs-toggle="tooltip" title="Solo SuperAdmin">
              <i class="bi bi-lock"></i> Solo SuperAdmin
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Inicializar tooltips de Bootstrap
  document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
</script>
</body>
</html>
