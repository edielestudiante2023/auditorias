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

                        <!-- Sección de Usuario (solo al crear) -->
                        <?php if (!$proveedor): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Selección de Usuario</strong>
                        </div>

                        <!-- Selector de modo -->
                        <div class="mb-4">
                            <label class="form-label">¿Cómo deseas crear el proveedor? <span class="text-danger">*</span></label>
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
                                       class="form-control"
                                       id="email"
                                       name="email"
                                       value="<?= old('email') ?>">
                                <div id="email-loading" class="form-text text-muted" style="display: none;">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                    Verificando email...
                                </div>
                                <div id="email-feedback" class="form-text"></div>
                                <div class="form-text">Este email se usará para iniciar sesión</div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="password"
                                       name="password"
                                       minlength="8">
                                <div class="form-text">Mínimo 8 caracteres</div>
                            </div>

                            <div class="mb-3">
                                <label for="nombre_usuario" class="form-label">
                                    Nombre del Usuario <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="nombre_usuario"
                                       name="nombre_usuario"
                                       value="<?= old('nombre_usuario') ?>"
                                       maxlength="150"
                                       placeholder="Nombre completo del usuario que accederá al sistema">
                                <div class="form-text">Este será el nombre de login del usuario</div>
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
                                    <i class="bi bi-info-circle"></i> Solo se muestran usuarios con rol "Proveedor" que no tengan registro de proveedor aún.
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
                            <select class="form-select" id="id_users" name="id_users">
                                <option value="">Sin vincular</option>
                                <?php if (!empty($usuarios)): ?>
                                    <?php foreach ($usuarios as $user): ?>
                                        <option value="<?= $user['id_users'] ?>"
                                                <?= old('id_users', $proveedor['id_users'] ?? '') == $user['id_users'] ? 'selected' : '' ?>>
                                            <?= esc($user['nombre']) ?> (<?= esc($user['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i> Selecciona el usuario que tendrá acceso como proveedor. Debe tener rol "Proveedor".
                            </div>
                        </div>
                        <hr class="my-4">
                        <?php endif; ?>

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
<script>
<?php if (!$proveedor): ?>
// Manejo del cambio de modo (Crear Nuevo vs Usar Existente)
const modoNuevo = document.getElementById('modo_nuevo');
const modoExistente = document.getElementById('modo_existente');
const camposNuevoUsuario = document.getElementById('campos_nuevo_usuario');
const camposUsuarioExistente = document.getElementById('campos_usuario_existente');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const nombreUsuarioInput = document.getElementById('nombre_usuario');
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
        nombreUsuarioInput.required = true;
        idUsersExistente.required = false;
        idUsersExistente.value = ''; // Limpiar selección
    } else {
        // Mostrar dropdown de usuarios existentes
        camposNuevoUsuario.style.display = 'none';
        camposUsuarioExistente.style.display = 'block';

        // Hacer obligatorio el dropdown, opcional los campos de nuevo usuario
        emailInput.required = false;
        passwordInput.required = false;
        nombreUsuarioInput.required = false;
        idUsersExistente.required = true;

        // Limpiar campos de nuevo usuario
        emailInput.value = '';
        passwordInput.value = '';
        nombreUsuarioInput.value = '';
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
</script>
</body>
</html>
