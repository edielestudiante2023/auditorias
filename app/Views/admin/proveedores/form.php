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

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <!-- Cambio de ícono: reemplazado bi-truck por bi-shop para representar alianza con proveedores -->
                        <i class="bi bi-shop"></i> <?= esc($title) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= $proveedor ? site_url('admin/proveedores/update/' . $proveedor['id_proveedor']) : site_url('admin/proveedores/store') ?>"
                          method="POST">
                        <?= csrf_field() ?>

                        <!-- Razón Social -->
                        <div class="mb-3">
                            <label for="razon_social" class="form-label">
                                Razón Social <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control <?= isset($errors['razon_social']) ? 'is-invalid' : '' ?>"
                                   id="razon_social"
                                   name="razon_social"
                                   value="<?= old('razon_social', $proveedor['razon_social'] ?? '') ?>"
                                   maxlength="200"
                                   required>
                            <?php if (isset($errors['razon_social'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['razon_social']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- NIT -->
                        <div class="mb-3">
                            <label for="nit" class="form-label">
                                NIT <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control <?= isset($errors['nit']) ? 'is-invalid' : '' ?>"
                                   id="nit"
                                   name="nit"
                                   value="<?= old('nit', $proveedor['nit'] ?? '') ?>"
                                   maxlength="50"
                                   placeholder="Ej: 900123456-7"
                                   required>
                            <?php if (isset($errors['nit'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['nit']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Email de Contacto -->
                        <div class="mb-3">
                            <label for="email_contacto" class="form-label">Email de Contacto</label>
                            <input type="email"
                                   class="form-control <?= isset($errors['email_contacto']) ? 'is-invalid' : '' ?>"
                                   id="email_contacto"
                                   name="email_contacto"
                                   value="<?= old('email_contacto', $proveedor['email_contacto'] ?? '') ?>"
                                   maxlength="150"
                                   placeholder="contacto@proveedor.com">
                            <?php if (isset($errors['email_contacto'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['email_contacto']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Teléfono de Contacto -->
                        <div class="mb-3">
                            <label for="telefono_contacto" class="form-label">Teléfono de Contacto</label>
                            <input type="text"
                                   class="form-control <?= isset($errors['telefono_contacto']) ? 'is-invalid' : '' ?>"
                                   id="telefono_contacto"
                                   name="telefono_contacto"
                                   value="<?= old('telefono_contacto', $proveedor['telefono_contacto'] ?? '') ?>"
                                   maxlength="50"
                                   placeholder="(+57) 300 123 4567">
                            <?php if (isset($errors['telefono_contacto'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['telefono_contacto']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control <?= isset($errors['observaciones']) ? 'is-invalid' : '' ?>"
                                      id="observaciones"
                                      name="observaciones"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Información adicional sobre el proveedor (opcional)"><?= old('observaciones', $proveedor['observaciones'] ?? '') ?></textarea>
                            <div class="form-text">
                                Máximo 500 caracteres
                            </div>
                            <?php if (isset($errors['observaciones'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['observaciones']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= site_url('admin/proveedores') ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
