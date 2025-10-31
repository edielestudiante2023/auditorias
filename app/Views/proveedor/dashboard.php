<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Panel Proveedor') ?> | Auditorías</title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
  <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
  <link rel="apple-touch-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-warning">
  <div class="container-fluid">
    <a class="navbar-brand text-dark" href="<?= site_url('proveedor/dashboard') ?>">
      <!-- Cambio de ícono: reemplazado bi-truck por bi-shop para representar alianza con proveedores -->
      <i class="bi bi-shop"></i> Auditorías - Proveedor
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-dark" href="#" role="button" data-bs-toggle="dropdown">
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
    <h4 class="mb-0"><i class="bi bi-inbox"></i> Panel de Proveedor</h4>
  </div>

  <!-- Cards de acceso rápido -->
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card h-100 shadow-sm border-warning">
        <div class="card-body text-center">
          <i class="bi bi-envelope-exclamation text-warning" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Mis Auditorías</h5>
          <p class="card-text text-muted">Auditorías asignadas</p>
          <a href="<?= site_url('proveedor/auditorias') ?>" class="btn btn-warning" target="_blank">Ver Auditorías</a>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100 shadow-sm">
        <div class="card-body text-center">
          <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Auditorías Completadas</h5>
          <p class="card-text text-muted">Historial de auditorías respondidas</p>
          <a href="<?= site_url('proveedor/auditorias/completadas') ?>" class="btn btn-success" target="_blank">Ver Completadas</a>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100 shadow-sm">
        <div class="card-body text-center">
          <i class="bi bi-file-earmark-text text-info" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Mis Evidencias</h5>
          <p class="card-text text-muted">Archivos y documentos subidos</p>
          <a href="<?= site_url('proveedor/evidencias') ?>" class="btn btn-info" target="_blank">Ver Evidencias</a>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100 shadow-sm">
        <div class="card-body text-center">
          <i class="bi bi-building text-primary" style="font-size: 3rem;"></i>
          <h5 class="card-title mt-3">Mi Empresa</h5>
          <p class="card-text text-muted">Información del proveedor</p>
          <a href="<?= site_url('proveedor/empresa') ?>" class="btn btn-primary" target="_blank">Ver Información</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
