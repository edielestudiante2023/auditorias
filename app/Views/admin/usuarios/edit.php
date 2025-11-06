<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Editar Usuario') ?> | Auditorías</title>
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
        <h4 class="mb-0"><i class="bi bi-person"></i> <?= esc($title ?? 'Editar Usuario') ?></h4>
        <a href="<?= site_url('admin/usuarios') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?= esc($form_action) ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control <?= isset($errors['nombre']) ? 'is-invalid' : '' ?>" id="nombre" name="nombre" value="<?= esc(old('nombre', $usuario['nombre'] ?? '')) ?>" required minlength="3">
                    <?php if (!empty($errors['nombre'])): ?><div class="invalid-feedback"><?= esc($errors['nombre']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= esc(old('email', $usuario['email'] ?? '')) ?>" required>
                    <?php if (!empty($errors['email'])): ?><div class="invalid-feedback"><?= esc($errors['email']) ?></div><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="id_roles" class="form-label">Rol</label>
                    <?php if (!empty($esActual) && $esActual): ?>
                        <select class="form-select" id="id_roles" name="id_roles" disabled>
                            <?php $rid = (string)($usuario['id_roles'] ?? ''); ?>
                            <option value="1" <?= $rid==='1'?'selected':'' ?>>Super Admin</option>
                            <option value="2" <?= $rid==='2'?'selected':'' ?>>Consultor</option>
                            <option value="3" <?= $rid==='3'?'selected':'' ?>>Proveedor</option>
                        </select>
                        <div class="form-text text-muted">No puedes cambiar tu propio rol aquí.</div>
                    <?php else: ?>
                        <select class="form-select <?= isset($errors['id_roles']) ? 'is-invalid' : '' ?>" id="id_roles" name="id_roles" required onchange="toggleProveedorField()">
                            <?php $rid = (string)old('id_roles', $usuario['id_roles'] ?? ''); ?>
                            <option value="1" <?= $rid==='1'?'selected':'' ?>>Super Admin</option>
                            <option value="2" <?= $rid==='2'?'selected':'' ?>>Consultor</option>
                            <option value="3" <?= $rid==='3'?'selected':'' ?>>Proveedor</option>
                        </select>
                        <?php if (!empty($errors['id_roles'])): ?><div class="invalid-feedback"><?= esc($errors['id_roles']) ?></div><?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Campo condicional para vincular Proveedores (selección múltiple con Select2) -->
                <div class="mb-3" id="proveedorField" style="display: none;">
                    <label for="proveedores" class="form-label">
                        <!-- Cambio de ícono: usando bi-shop para representar proveedor comercial -->
                        <i class="bi bi-shop"></i> Vincular a Proveedores
                    </label>
                    <?php if (!empty($proveedoresVinculados)): ?>
                        <div class="alert alert-info alert-sm mb-2">
                            <i class="bi bi-link-45deg"></i> Actualmente vinculado a:
                            <?php foreach ($proveedoresVinculados as $pv): ?>
                                <span class="badge bg-warning text-dark"><?= esc($pv['razon_social']) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php
                    // Crear array de IDs vinculados para pre-seleccionar
                    $idsVinculados = !empty($proveedoresVinculados) ? array_column($proveedoresVinculados, 'id_proveedor') : [];
                    ?>
                    <select class="form-select"
                            id="proveedores"
                            name="proveedores[]"
                            multiple="multiple"
                            style="width: 100%">
                        <?php if (!empty($proveedores)): ?>
                            <?php foreach ($proveedores as $prov): ?>
                                <option value="<?= $prov['id_proveedor'] ?>"
                                        <?= in_array($prov['id_proveedor'], $idsVinculados) ? 'selected' : '' ?>>
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
                        <?php $est = (string)old('estado', $usuario['estado'] ?? 'activo'); ?>
                        <option value="activo" <?= $est==='activo'?'selected':'' ?>>Activo</option>
                        <option value="inactivo" <?= $est==='inactivo'?'selected':'' ?>>Inactivo</option>
                    </select>
                    <?php if (!empty($errors['estado'])): ?><div class="invalid-feedback"><?= esc($errors['estado']) ?></div><?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar</button>
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

    if (rolSelect && rolSelect.value === '3') { // Rol Proveedor
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
    } else if (proveedorField) {
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

