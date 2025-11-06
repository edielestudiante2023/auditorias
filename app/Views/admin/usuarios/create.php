<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Crear Usuario') ?> | Auditorías</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container py-4">
    <?= view('partials/flash') ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="bi bi-person-plus"></i> <?= esc($title ?? 'Crear Usuario') ?></h4>
        <a href="<?= site_url('admin/usuarios') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?= esc($form_action) ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre" value="<?= esc(old('nombre')) ?>" required minlength="3">
                    <?php if (!empty($errors['nombre'])): ?><div class="invalid-feedback"><?= esc($errors['nombre']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= esc(old('email')) ?>" required>
                    <?php if (!empty($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="id_roles" class="form-label">Rol</label>
                    <select class="form-select <?= isset($errors['id_roles']) ? 'is-invalid' : '' ?>" id="id_roles" name="id_roles" required onchange="toggleProveedorField()">
                        <option value="">Selecciona rol...</option>
                        <option value="1" <?= old('id_roles')==='1'?'selected':'' ?>>Super Admin</option>
                        <option value="2" <?= old('id_roles')==='2'?'selected':'' ?>>Consultor</option>
                        <option value="3" <?= old('id_roles')==='3'?'selected':'' ?>>Proveedor</option>
                    </select>
                    <?php if (!empty($errors['id_roles'])): ?><div class="invalid-feedback"><?= esc($errors['id_roles']) ?></div><?php endif; ?>
                </div>

                <!-- Campo condicional para vincular Proveedores (selección múltiple con Select2) -->
                <div class="mb-3" id="proveedorField" style="display: none;">
                    <label for="proveedores" class="form-label">
                        <!-- Cambio de ícono: usando bi-shop para representar proveedor comercial -->
                        <i class="bi bi-shop"></i> Vincular a Proveedores
                    </label>
                    <select class="form-select"
                            id="proveedores"
                            name="proveedores[]"
                            multiple="multiple"
                            style="width: 100%">
                        <?php if (!empty($proveedores)): ?>
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?= $prov['id_proveedor'] ?>">
                                    <?= esc($prov['razon_social']) ?> - NIT: <?= esc($prov['nit']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="form-text">
                        <i class="bi bi-info-circle"></i> Puedes seleccionar múltiples proveedores. Usa el buscador para filtrar.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select <?= isset($errors['estado']) ? 'is-invalid' : '' ?>" id="estado" name="estado" required>
                        <option value="activo" <?= old('estado','activo')==='activo'?'selected':'' ?>>Activo</option>
                        <option value="inactivo" <?= old('estado')==='inactivo'?'selected':'' ?>>Inactivo</option>
                    </select>
                    <?php if (!empty($errors['estado'])): ?><div class="invalid-feedback"><?= esc($errors['estado']) ?></div><?php endif; ?>
                </div>

                <div class="alert alert-info">
                    <h6><i class="bi bi-info-circle"></i> Información sobre Contraseñas</h6>
                    <ul class="mb-0" style="padding-left: 20px;">
                        <li><strong>Se generará automáticamente</strong> una contraseña segura y aleatoria</li>
                        <li><strong>Se enviará por email</strong> al usuario con sus credenciales de acceso</li>
                        <li>La contraseña incluye letras mayúsculas, minúsculas, números y símbolos</li>
                        <li>El usuario deberá cambiarla en su primer ingreso por seguridad</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Crear Usuario y Enviar Credenciales</button>
            </form>
        </div>
    </div>
</div>

<!-- jQuery (requerido por Select2) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function toggleProveedorField() {
    const rolSelect = document.getElementById('id_roles');
    const proveedorField = document.getElementById('proveedorField');

    if (rolSelect.value === '3') { // Rol Proveedor
        proveedorField.style.display = 'block';
        // Inicializar Select2 si no está inicializado
        if (!$('#proveedores').hasClass('select2-hidden-accessible')) {
            $('#proveedores').select2({
                theme: 'bootstrap-5',
                placeholder: 'Selecciona uno o más proveedores...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "No se encontraron proveedores";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
        }
    } else {
        proveedorField.style.display = 'none';
        // Limpiar selección
        $('#proveedores').val(null).trigger('change');
    }
}

// Ejecutar al cargar la página
$(document).ready(function() {
    toggleProveedorField();
});
</script>
</body>
</html>

