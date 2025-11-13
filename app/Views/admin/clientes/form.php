<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | Auditorías</title>
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

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-building"></i> <?= esc($title) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= esc($form_action) ?>" method="POST" enctype="multipart/form-data">
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
                                   value="<?= old('razon_social', $cliente['razon_social'] ?? '') ?>"
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
                                   value="<?= old('nit', $cliente['nit'] ?? '') ?>"
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
                                   value="<?= old('email_contacto', $cliente['email_contacto'] ?? '') ?>"
                                   maxlength="150"
                                   placeholder="contacto@empresa.com">
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
                                   value="<?= old('telefono_contacto', $cliente['telefono_contacto'] ?? '') ?>"
                                   maxlength="50"
                                   placeholder="(+57) 300 123 4567">
                            <?php if (isset($errors['telefono_contacto'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['telefono_contacto']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Dirección -->
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control <?= isset($errors['direccion']) ? 'is-invalid' : '' ?>"
                                      id="direccion"
                                      name="direccion"
                                      rows="2"
                                      maxlength="200"
                                      placeholder="Calle 123 # 45-67, Bogotá"><?= old('direccion', $cliente['direccion'] ?? '') ?></textarea>
                            <?php if (isset($errors['direccion'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['direccion']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select <?= isset($errors['estado']) ? 'is-invalid' : '' ?>"
                                    id="estado"
                                    name="estado">
                                <option value="activo" <?= old('estado', $cliente['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>
                                    Activo
                                </option>
                                <option value="inactivo" <?= old('estado', $cliente['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>
                                    Inactivo
                                </option>
                            </select>
                            <?php if (isset($errors['estado'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['estado']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Logo del Cliente -->
                        <div class="mb-3">
                            <label for="logo_cliente" class="form-label">
                                Logo del Cliente
                            </label>

                            <?php if (!empty($cliente['logo_cliente_path']) && uploadExists($cliente['logo_cliente_path'])):
                                $filename = basename($cliente['logo_cliente_path']);
                                $logoUrl = site_url('files/logo/' . $filename);
                            ?>
                                <div class="mb-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= $logoUrl ?>"
                                             alt="Logo actual"
                                             class="img-thumbnail"
                                             style="width: 100px; height: 100px; object-fit: contain;">
                                        <div>
                                            <p class="mb-1 text-muted small">Logo actual</p>
                                            <a href="<?= site_url('admin/clientes/eliminar-logo/' . ($cliente['id_cliente'] ?? '')) ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('¿Está seguro de eliminar el logo?')">
                                                <i class="bi bi-trash"></i> Eliminar Logo
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file"
                                   class="form-control <?= isset($errors['logo_cliente']) ? 'is-invalid' : '' ?>"
                                   id="logo_cliente"
                                   name="logo_cliente"
                                   accept="image/png, image/jpeg, image/jpg">
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i>
                                Formatos permitidos: PNG, JPG. Tamaño máximo: 20 MB.
                                <?php if (!empty($cliente['logo_cliente_path'])): ?>
                                    Subir un nuevo archivo reemplazará el logo actual.
                                <?php endif; ?>
                            </div>
                            <?php if (isset($errors['logo_cliente'])): ?>
                                <div class="invalid-feedback d-block"><?= esc($errors['logo_cliente']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= site_url('admin/clientes') ?>" class="btn btn-secondary">
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
<script>
    // Preview del logo al seleccionar archivo
    document.getElementById('logo_cliente').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                // Crear o actualizar preview
                let preview = document.getElementById('logo-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = 'logo-preview';
                    preview.className = 'mt-2';
                    e.target.parentElement.appendChild(preview);
                }
                preview.innerHTML = `
                    <p class="text-muted small mb-1">Vista previa:</p>
                    <img src="${event.target.result}"
                         alt="Preview"
                         class="img-thumbnail"
                         style="width: 100px; height: 100px; object-fit: contain;">
                `;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>
