<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Editar Consultor') ?> | Auditorías</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <?= view('partials/flash') ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-person-badge"></i> <?= esc($title ?? 'Editar Consultor') ?></h4>
        <a href="<?= site_url('admin/consultores') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?= esc($form_action) ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre" value="<?= esc(old('nombre', $consultor['nombre'] ?? '')) ?>" required minlength="3">
                    <?php if (!empty($errors['nombre'])): ?><div class="invalid-feedback"><?= esc($errors['nombre']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= esc(old('email', $consultor['email'] ?? '')) ?>" required>
                    <?php if (!empty($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control <?= isset($errors['telefono']) ? 'is-invalid' : '' ?>" id="telefono" name="telefono" value="<?= esc(old('telefono', $consultor['telefono'] ?? '')) ?>" maxlength="50">
                    <?php if (!empty($errors['telefono'])): ?><div class="invalid-feedback"><?= esc($errors['telefono']) ?></div><?php endif; ?>
                </div>

                <div class="form-check form-switch mb-3">
                    <?php $activo = old('estado', $consultor['estado'] ?? 'activo') === 'activo'; ?>
                    <input class="form-check-input" type="checkbox" role="switch" id="estado" name="estado" value="activo" <?= $activo ? 'checked' : '' ?>>
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

