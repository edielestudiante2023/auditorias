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
                    <a class="nav-link active" href="<?= site_url('admin/consultores') ?>">
                        <i class="bi bi-person-badge"></i> Consultores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= site_url('admin/clientes') ?>">
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
                        <i class="bi bi-person-badge"></i> <?= esc($title) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= $consultor ? site_url('admin/consultores/update/' . $consultor['id_consultor']) : site_url('admin/consultores/store') ?>"
                          method="POST"
                          enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <!-- Sección de Usuario (solo al crear) -->
                        <?php if (!$consultor): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Selección de Usuario</strong>
                        </div>

                        <!-- Selector de modo -->
                        <div class="mb-4">
                            <label class="form-label">¿Cómo deseas crear el consultor? <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="modo_usuario" id="modo_nuevo" value="nuevo" checked>
                                <label class="btn btn-outline-primary" for="modo_nuevo">
                                    <i class="bi bi-person-plus"></i> Crear Nuevo Usuario
                                </label>

                                <input type="radio" class="btn-check" name="modo_usuario" id="modo_existente" value="existente">
                                <label class="btn btn-outline-primary" for="modo_existente">
                                    <i class="bi bi-person-check"></i> Usar Usuario Existente
                                </label>
                            </div>
                        </div>

                        <!-- Campos para CREAR NUEVO usuario -->
                        <div id="campos_nuevo_usuario">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email (para login) <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       class="form-control <?= $validation->hasError('email') ? 'is-invalid' : '' ?>"
                                       id="email"
                                       name="email"
                                       value="<?= old('email') ?>">
                                <div id="email-loading" class="form-text text-muted" style="display: none;">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    Verificando email...
                                </div>
                                <div id="email-feedback" class="form-text"></div>
                                <div class="form-text">Este email se usará para iniciar sesión</div>
                                <?php if ($validation->hasError('email')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('email') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password"
                                       class="form-control <?= $validation->hasError('password') ? 'is-invalid' : '' ?>"
                                       id="password"
                                       name="password"
                                       minlength="8">
                                <div class="form-text">Mínimo 8 caracteres</div>
                                <?php if ($validation->hasError('password')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('password') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Campos para USAR USUARIO EXISTENTE -->
                        <div id="campos_usuario_existente" style="display: none;">
                            <div class="mb-3">
                                <label for="id_users_existente" class="form-label">
                                    <i class="bi bi-person-circle"></i> Seleccionar Usuario Existente <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="id_users_existente" name="id_users">
                                    <option value="">-- Seleccionar usuario --</option>
                                    <?php if (!empty($usuarios)): ?>
                                        <?php foreach ($usuarios as $user): ?>
                                            <option value="<?= $user['id_users'] ?>">
                                                <?= esc($user['nombre']) ?> (<?= esc($user['email']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> Solo se muestran usuarios con rol "Consultor" que no tengan registro de consultor aún.
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <?php else: ?>
                        <!-- Usuario Vinculado (editable) -->
                        <div class="mb-3">
                            <label for="id_users" class="form-label">
                                <i class="bi bi-person-circle"></i> Usuario Vinculado
                            </label>
                            <select class="form-select <?= $validation->hasError('id_users') ? 'is-invalid' : '' ?>"
                                    id="id_users"
                                    name="id_users">
                                <option value="">Sin vincular</option>
                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $user): ?>
                                        <option value="<?= $user['id_users'] ?>"
                                                <?= old('id_users', $consultor['id_users'] ?? '') == $user['id_users'] ? 'selected' : '' ?>>
                                            <?= esc($user['nombre']) ?> (<?= esc($user['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Selecciona el usuario que tendrá acceso como consultor. Debe tener rol "Consultor".
                            </div>
                            <?php if ($validation->hasError('id_users')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('id_users') ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Nombre Completo -->
                        <div class="mb-3">
                            <label for="nombre_completo" class="form-label">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control <?= $validation->hasError('nombre_completo') ? 'is-invalid' : '' ?>"
                                   id="nombre_completo"
                                   name="nombre_completo"
                                   value="<?= old('nombre_completo', $consultor['nombre_completo'] ?? '') ?>"
                                   maxlength="150"
                                   required>
                            <?php if ($validation->hasError('nombre_completo')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('nombre_completo') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Tipo y Número de Documento -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tipo_documento" class="form-label">
                                    Tipo Documento <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?= $validation->hasError('tipo_documento') ? 'is-invalid' : '' ?>"
                                        id="tipo_documento"
                                        name="tipo_documento"
                                        required>
                                    <option value="">Seleccionar...</option>
                                    <option value="CC" <?= old('tipo_documento', $consultor['tipo_documento'] ?? '') == 'CC' ? 'selected' : '' ?>>CC</option>
                                    <option value="CE" <?= old('tipo_documento', $consultor['tipo_documento'] ?? '') == 'CE' ? 'selected' : '' ?>>CE</option>
                                    <option value="PAS" <?= old('tipo_documento', $consultor['tipo_documento'] ?? '') == 'PAS' ? 'selected' : '' ?>>PAS</option>
                                    <option value="OTRO" <?= old('tipo_documento', $consultor['tipo_documento'] ?? '') == 'OTRO' ? 'selected' : '' ?>>OTRO</option>
                                </select>
                                <?php if ($validation->hasError('tipo_documento')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('tipo_documento') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-8 mb-3">
                                <label for="numero_documento" class="form-label">
                                    Número de Documento <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control <?= $validation->hasError('numero_documento') ? 'is-invalid' : '' ?>"
                                       id="numero_documento"
                                       name="numero_documento"
                                       value="<?= old('numero_documento', $consultor['numero_documento'] ?? '') ?>"
                                       maxlength="30"
                                       required>
                                <?php if ($validation->hasError('numero_documento')): ?>
                                    <div class="invalid-feedback"><?= $validation->getError('numero_documento') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Licencia SST -->
                        <div class="mb-3">
                            <label for="licencia_sst" class="form-label">Licencia SST</label>
                            <input type="text"
                                   class="form-control <?= $validation->hasError('licencia_sst') ? 'is-invalid' : '' ?>"
                                   id="licencia_sst"
                                   name="licencia_sst"
                                   value="<?= old('licencia_sst', $consultor['licencia_sst'] ?? '') ?>"
                                   maxlength="100"
                                   placeholder="Número de licencia (opcional)">
                            <?php if ($validation->hasError('licencia_sst')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('licencia_sst') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Email de Contacto -->
                        <div class="mb-3">
                            <label for="email_consultor" class="form-label">
                                <i class="bi bi-envelope"></i> Email de Contacto
                            </label>
                            <input type="email"
                                   class="form-control <?= $validation->hasError('email') ? 'is-invalid' : '' ?>"
                                   id="email_consultor"
                                   name="email"
                                   value="<?= old('email', $consultor['email'] ?? '') ?>"
                                   maxlength="150"
                                   placeholder="email@ejemplo.com">
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Este email se incluirá en las notificaciones enviadas al proveedor para que pueda contactar al consultor.
                            </div>
                            <?php if ($validation->hasError('email')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('email') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Teléfono de Contacto -->
                        <div class="mb-3">
                            <label for="telefono" class="form-label">
                                <i class="bi bi-telephone"></i> Teléfono de Contacto
                            </label>
                            <input type="text"
                                   class="form-control <?= $validation->hasError('telefono') ? 'is-invalid' : '' ?>"
                                   id="telefono"
                                   name="telefono"
                                   value="<?= old('telefono', $consultor['telefono'] ?? '') ?>"
                                   maxlength="20"
                                   placeholder="+57 300 1234567">
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Este teléfono se incluirá en las notificaciones enviadas al proveedor para facilitar el contacto.
                            </div>
                            <?php if ($validation->hasError('telefono')): ?>
                                <div class="invalid-feedback"><?= $validation->getError('telefono') ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Firma Digital -->
                        <div class="mb-3">
                            <label for="firma" class="form-label">
                                <i class="bi bi-pen"></i> Firma Digital
                            </label>

                            <?php if ($consultor && !empty($consultor['firma_path']) && uploadExists($consultor['firma_path'])):
                                $filename = basename($consultor['firma_path']);
                                $firmaUrl = site_url('files/firma/' . $filename);
                            ?>
                                <div class="mb-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= $firmaUrl ?>"
                                             alt="Firma actual"
                                             class="img-thumbnail"
                                             style="max-width: 200px; max-height: 100px; object-fit: contain;">
                                        <div>
                                            <p class="mb-1 text-muted small">Firma actual</p>
                                            <button type="button"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="confirmarEliminarFirma(<?= $consultor['id_consultor'] ?>)">
                                                <i class="bi bi-trash"></i> Eliminar Firma
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file"
                                   class="form-control"
                                   id="firma"
                                   name="firma"
                                   accept="image/png, image/jpeg, image/jpg">
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i>
                                Formatos permitidos: PNG, JPG. Tamaño máximo: 20 MB.
                                <?php if ($consultor && !empty($consultor['firma_path'])): ?>
                                    Subir un nuevo archivo reemplazará la firma actual.
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= site_url('admin/consultores') ?>" class="btn btn-secondary">
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

<!-- Formulario oculto para eliminar firma -->
<?php if ($consultor): ?>
<form id="formEliminarFirma" method="post" style="display: none;">
    <?= csrf_field() ?>
</form>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if ($consultor): ?>
function confirmarEliminarFirma(id) {
    if (confirm('¿Está seguro de eliminar la firma actual?\n\nEsta acción no se puede deshacer.')) {
        const form = document.getElementById('formEliminarFirma');
        form.action = '<?= site_url('admin/consultores/eliminarFirma/') ?>' + id;
        form.submit();
    }
}
<?php else: ?>
// Manejo del cambio de modo (Crear Nuevo vs Usar Existente)
const modoNuevo = document.getElementById('modo_nuevo');
const modoExistente = document.getElementById('modo_existente');
const camposNuevoUsuario = document.getElementById('campos_nuevo_usuario');
const camposUsuarioExistente = document.getElementById('campos_usuario_existente');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const idUsersExistente = document.getElementById('id_users_existente');
const submitButton = document.querySelector('button[type="submit"]');

// Validación de email en tiempo real
const emailFeedback = document.getElementById('email-feedback');
const emailLoading = document.getElementById('email-loading');
let emailCheckTimeout;
let emailIsValid = false;

function toggleCampos() {
    if (modoNuevo.checked) {
        // Mostrar campos para crear nuevo usuario
        camposNuevoUsuario.style.display = 'block';
        camposUsuarioExistente.style.display = 'none';

        // Hacer obligatorios los campos de nuevo usuario
        emailInput.required = true;
        passwordInput.required = true;
        idUsersExistente.required = false;
        idUsersExistente.value = ''; // Limpiar selección
    } else {
        // Mostrar dropdown de usuarios existentes
        camposNuevoUsuario.style.display = 'none';
        camposUsuarioExistente.style.display = 'block';

        // Hacer obligatorio el dropdown, opcional los campos de nuevo usuario
        emailInput.required = false;
        passwordInput.required = false;
        idUsersExistente.required = true;

        // Limpiar campos de nuevo usuario
        emailInput.value = '';
        passwordInput.value = '';
        emailFeedback.innerHTML = '';
        emailFeedback.className = 'form-text';
        emailIsValid = true; // No validar email en modo existente
    }
    updateSubmitButton();
}

function updateSubmitButton() {
    if (modoNuevo.checked) {
        // En modo nuevo: validar email
        submitButton.disabled = !emailIsValid;
    } else {
        // En modo existente: siempre habilitado (el form validation de HTML validará el dropdown)
        submitButton.disabled = false;
    }
}

// Escuchar cambios en los radio buttons
modoNuevo.addEventListener('change', toggleCampos);
modoExistente.addEventListener('change', toggleCampos);

// Validación en tiempo real del email (solo en modo nuevo usuario)
if (emailInput) {
    emailInput.addEventListener('input', function() {
        const email = this.value.trim();

        // Limpiar feedback anterior
        clearTimeout(emailCheckTimeout);
        emailFeedback.innerHTML = '';
        emailFeedback.className = 'form-text';
        emailLoading.style.display = 'none';

        // Si está vacío, no validar
        if (!email || !modoNuevo.checked) {
            emailIsValid = false;
            updateSubmitButton();
            return;
        }

        // Validar formato básico primero
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            emailIsValid = false;
            updateSubmitButton();
            return;
        }

        // Mostrar loading
        emailLoading.style.display = 'block';

        // Debounce: esperar 500ms antes de hacer la petición
        emailCheckTimeout = setTimeout(() => {
            fetch('<?= site_url('admin/api/check-email') ?>?email=' + encodeURIComponent(email))
                .then(response => response.json())
                .then(data => {
                    emailLoading.style.display = 'none';

                    if (data.exists) {
                        // Email ya existe - mostrar error
                        emailFeedback.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i>' +
                                                  '<span class="text-danger">' + data.message + '</span>';
                        emailFeedback.className = 'form-text text-danger';
                        emailInput.classList.add('is-invalid');
                        emailIsValid = false;
                    } else {
                        // Email disponible - mostrar éxito
                        emailFeedback.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i>' +
                                                  '<span class="text-success">' + data.message + '</span>';
                        emailFeedback.className = 'form-text text-success';
                        emailInput.classList.remove('is-invalid');
                        emailIsValid = true;
                    }

                    updateSubmitButton();
                })
                .catch(error => {
                    emailLoading.style.display = 'none';
                    console.error('Error validating email:', error);
                    emailIsValid = false;
                    updateSubmitButton();
                });
        }, 500);
    });
}

// Inicializar estado por defecto
toggleCampos();
<?php endif; ?>

// Preview firma al seleccionar archivo
document.getElementById('firma')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            let preview = document.getElementById('firma-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'firma-preview';
                preview.className = 'mt-2';
                e.target.parentElement.appendChild(preview);
            }
            preview.innerHTML = `
                <p class="text-muted small mb-1">Vista previa:</p>
                <img src="${event.target.result}"
                     alt="Preview"
                     class="img-thumbnail"
                     style="max-width: 200px; max-height: 100px; object-fit: contain;">
            `;
        };
        reader.readAsDataURL(file);
    }
});
</script>
</body>
</html>
