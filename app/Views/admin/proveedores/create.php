<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Crear Proveedor') ?> | Auditorías</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <?= view('partials/flash') ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- Cambio de ícono: reemplazado bi-truck por bi-shop para representar alianza con proveedores -->
        <h4 class="mb-0"><i class="bi bi-shop"></i> <?= esc($title ?? 'Crear Proveedor') ?></h4>
        <a href="<?= site_url('admin/proveedores') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?= esc($form_action) ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="razon_social" class="form-label">Razón Social <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= isset($errors['razon_social']) ? 'is-invalid' : '' ?>" id="razon_social" name="razon_social" value="<?= esc(old('razon_social')) ?>" required minlength="3" maxlength="200">
                    <?php if (!empty($errors['razon_social'])): ?><div class="invalid-feedback"><?= esc($errors['razon_social']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="nit" class="form-label">NIT <span class="text-danger">*</span></label>
                    <input type="text" class="form-control <?= isset($errors['nit']) ? 'is-invalid' : '' ?>" id="nit" name="nit" value="<?= esc(old('nit')) ?>" required maxlength="50" placeholder="Ej: 900123456-7">
                    <?php if (!empty($errors['nit'])): ?><div class="invalid-feedback"><?= esc($errors['nit']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email_contacto" class="form-label">Email de Contacto</label>
                    <input type="email" class="form-control <?= isset($errors['email_contacto']) ? 'is-invalid' : '' ?>" id="email_contacto" name="email_contacto" value="<?= esc(old('email_contacto')) ?>" maxlength="150" placeholder="contacto@proveedor.com">
                    <?php if (!empty($errors['email_contacto'])): ?><div class="invalid-feedback"><?= esc($errors['email_contacto']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="telefono_contacto" class="form-label">Teléfono de Contacto</label>
                    <input type="text" class="form-control <?= isset($errors['telefono_contacto']) ? 'is-invalid' : '' ?>" id="telefono_contacto" name="telefono_contacto" value="<?= esc(old('telefono_contacto')) ?>" maxlength="50" placeholder="(+57) 300 123 4567">
                    <?php if (!empty($errors['telefono_contacto'])): ?><div class="invalid-feedback"><?= esc($errors['telefono_contacto']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="observaciones" class="form-label">Observaciones</label>
                    <textarea class="form-control <?= isset($errors['observaciones']) ? 'is-invalid' : '' ?>" id="observaciones" name="observaciones" rows="3" maxlength="500" placeholder="Información adicional sobre el proveedor (opcional)"><?= esc(old('observaciones')) ?></textarea>
                    <div class="form-text">Máximo 500 caracteres</div>
                    <?php if (!empty($errors['observaciones'])): ?><div class="invalid-feedback"><?= esc($errors['observaciones']) ?></div><?php endif; ?>
                </div>

                <hr class="my-4">

                <div class="alert alert-warning">
                    <i class="bi bi-person-badge"></i> <strong>Responsable de Auditoría</strong>
                    <p class="mb-0 small">Persona que diligenciará las auditorías y recibirá las notificaciones (Ej: Analista SST, Coordinador SST)</p>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="responsable_nombre" class="form-label">Nombre del Responsable</label>
                        <input type="text" class="form-control <?= isset($errors['responsable_nombre']) ? 'is-invalid' : '' ?>" id="responsable_nombre" name="responsable_nombre" value="<?= esc(old('responsable_nombre')) ?>" maxlength="150" placeholder="Ej: Juan Pérez">
                        <?php if (!empty($errors['responsable_nombre'])): ?><div class="invalid-feedback"><?= esc($errors['responsable_nombre']) ?></div><?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="responsable_cargo" class="form-label">Cargo</label>
                        <input type="text" class="form-control <?= isset($errors['responsable_cargo']) ? 'is-invalid' : '' ?>" id="responsable_cargo" name="responsable_cargo" value="<?= esc(old('responsable_cargo')) ?>" maxlength="100" placeholder="Ej: Coordinador SST">
                        <?php if (!empty($errors['responsable_cargo'])): ?><div class="invalid-feedback"><?= esc($errors['responsable_cargo']) ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="responsable_email" class="form-label">
                            Email del Responsable
                            <i class="bi bi-envelope-at text-primary" title="A este email llegarán las notificaciones de auditorías"></i>
                        </label>
                        <input type="email" class="form-control <?= isset($errors['responsable_email']) ? 'is-invalid' : '' ?>" id="responsable_email" name="responsable_email" value="<?= esc(old('responsable_email')) ?>" maxlength="150" placeholder="juan.perez@empresa.com">
                        <div class="form-text">A este correo llegarán las invitaciones de auditoría</div>
                        <?php if (!empty($errors['responsable_email'])): ?><div class="invalid-feedback"><?= esc($errors['responsable_email']) ?></div><?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="responsable_telefono" class="form-label">Teléfono del Responsable</label>
                        <input type="text" class="form-control <?= isset($errors['responsable_telefono']) ? 'is-invalid' : '' ?>" id="responsable_telefono" name="responsable_telefono" value="<?= esc(old('responsable_telefono')) ?>" maxlength="50" placeholder="(+57) 310 987 6543">
                        <?php if (!empty($errors['responsable_telefono'])): ?><div class="invalid-feedback"><?= esc($errors['responsable_telefono']) ?></div><?php endif; ?>
                    </div>
                </div>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

