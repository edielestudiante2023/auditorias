<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Crear Cliente') ?> | Auditorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .form-switch .form-check-input { width: 3em; }
    </style>
    </head>
<body class="bg-light">

<div class="container py-4">
    <!-- Breadcrumbs -->
    <?= view('partials/breadcrumbs', ['breadcrumbs' => [
        ['title' => 'Admin', 'url' => 'admin/dashboard'],
        ['title' => 'Clientes', 'url' => 'admin/clientes'],
        ['title' => 'Crear', 'url' => null]
    ]]) ?>

    <!-- Flash Messages -->
    <?= view('partials/flash') ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-building"></i> <?= esc($title ?? 'Crear Cliente') ?></h4>
        <a href="<?= site_url('admin/clientes') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?= esc($form_action) ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="razon_social" class="form-label">Razón Social</label>
                    <input type="text" class="form-control <?= isset($errors['razon_social']) ? 'is-invalid' : '' ?>" id="razon_social" name="razon_social" value="<?= esc(old('razon_social')) ?>" required minlength="3">
                    <?php if (!empty($errors['razon_social'])): ?><div class="invalid-feedback"><?= esc($errors['razon_social']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="nit" class="form-label">NIT</label>
                    <input type="text" class="form-control <?= isset($errors['nit']) ? 'is-invalid' : '' ?>" id="nit" name="nit" value="<?= esc(old('nit')) ?>" required>
                    <div class="form-text">Ejemplo: 900123456-7</div>
                    <?php if (!empty($errors['nit'])): ?><div class="invalid-feedback"><?= esc($errors['nit']) ?></div><?php endif; ?>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="estado" name="estado" value="activo" <?= old('estado','activo')==='activo'?'checked':'' ?>>
                    <label class="form-check-label" for="estado">Activo</label>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

