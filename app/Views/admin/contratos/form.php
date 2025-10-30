<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> | Auditorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
                    <a class="nav-link" href="<?= site_url('admin/proveedores') ?>">
                        <!-- Cambio de ícono: reemplazado bi-truck por bi-shop para representar alianza con proveedores -->
                        <i class="bi bi-shop"></i> Proveedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="<?= site_url('admin/contratos') ?>">
                        <i class="bi bi-diagram-3"></i> Relaciones
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
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-diagram-3"></i> <?= esc($title) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= $contrato ? site_url('admin/contratos/update/' . $contrato['id_contrato']) : site_url('admin/contratos/store') ?>"
                          method="POST">
                        <?= csrf_field() ?>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Relación Cliente-Proveedor</strong>
                            <p class="mb-0 small">Asigna qué proveedores de aseo/vigilancia tiene cada cliente para realizar auditorías SG-SST</p>
                        </div>

                        <div class="row">
                            <!-- Cliente -->
                            <div class="col-md-6 mb-3">
                                <label for="id_cliente" class="form-label">
                                    Cliente <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?= isset($errors['id_cliente']) ? 'is-invalid' : '' ?>"
                                        id="id_cliente"
                                        name="id_cliente"
                                        required
                                        onchange="cargarEmailCliente()">
                                    <option value="">Seleccionar cliente...</option>
                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= $cliente['id_cliente'] ?>"
                                                data-email="<?= esc($cliente['email_contacto'] ?? '') ?>"
                                                <?= old('id_cliente', $contrato['id_cliente'] ?? '') == $cliente['id_cliente'] ? 'selected' : '' ?>>
                                            <?= esc($cliente['razon_social']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Tu cliente que tiene proveedores</div>
                                <?php if (isset($errors['id_cliente'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['id_cliente']) ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Proveedor -->
                            <div class="col-md-6 mb-3">
                                <label for="id_proveedor" class="form-label">
                                    Proveedor <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?= isset($errors['id_proveedor']) ? 'is-invalid' : '' ?>"
                                        id="id_proveedor"
                                        name="id_proveedor"
                                        required>
                                    <option value="">Seleccionar proveedor...</option>
                                    <?php foreach ($proveedores as $proveedor): ?>
                                        <option value="<?= $proveedor['id_proveedor'] ?>"
                                                <?= old('id_proveedor', $contrato['id_proveedor'] ?? '') == $proveedor['id_proveedor'] ? 'selected' : '' ?>>
                                            <?= esc($proveedor['razon_social']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Proveedor de aseo/vigilancia del cliente</div>
                                <?php if (isset($errors['id_proveedor'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['id_proveedor']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Consultor -->
                            <div class="col-md-6 mb-3">
                                <label for="id_consultor" class="form-label">
                                    Consultor Asignado <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?= isset($errors['id_consultor']) ? 'is-invalid' : '' ?>"
                                        id="id_consultor"
                                        name="id_consultor"
                                        required>
                                    <option value="">Seleccionar consultor...</option>
                                    <?php foreach ($consultores as $consultor): ?>
                                        <option value="<?= $consultor['id_consultor'] ?>"
                                                <?= old('id_consultor', $contrato['id_consultor'] ?? '') == $consultor['id_consultor'] ? 'selected' : '' ?>>
                                            <?= esc($consultor['nombre_completo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Tu consultor que auditará al proveedor</div>
                                <?php if (isset($errors['id_consultor'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['id_consultor']) ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Usuario Responsable del Proveedor -->
                            <div class="col-md-6 mb-3">
                                <label for="id_usuario_responsable" class="form-label">
                                    Usuario Responsable del Proveedor <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?= isset($errors['id_usuario_responsable']) ? 'is-invalid' : '' ?>"
                                        id="id_usuario_responsable"
                                        name="id_usuario_responsable"
                                        required>
                                    <option value="">Seleccionar usuario responsable...</option>
                                    <?php foreach ($usuarios_proveedores as $usuario): ?>
                                        <option value="<?= $usuario['id_users'] ?>"
                                                <?= old('id_usuario_responsable', $contrato['id_usuario_responsable'] ?? '') == $usuario['id_users'] ? 'selected' : '' ?>>
                                            <?= esc($usuario['nombre']) ?> (<?= esc($usuario['email']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    <i class="bi bi-exclamation-triangle text-warning"></i>
                                    <strong>Coordinador SST o Analista SST del proveedor</strong> que recibirá emails y cargará soportes
                                </div>
                                <?php if (isset($errors['id_usuario_responsable'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['id_usuario_responsable']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Servicio -->
                            <div class="col-md-6 mb-3">
                                <label for="tipo_auditoria" class="form-label">
                                    Tipo de Auditoría <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?= isset($errors['tipo_auditoria']) ? 'is-invalid' : '' ?>"
                                        id="tipo_auditoria"
                                        name="tipo_auditoria"
                                        required>
                                    <option value="">Seleccionar tipo de auditoría...</option>
                                    <option value="basica" <?= old('tipo_auditoria', $contrato['tipo_auditoria'] ?? '') == 'basica' ? 'selected' : '' ?>>
                                        Auditoría Básica de Cumplimiento en Seguridad y Salud en el Trabajo (SST)
                                    </option>
                                    <option value="alto_riesgo" <?= old('tipo_auditoria', $contrato['tipo_auditoria'] ?? '') == 'alto_riesgo' ? 'selected' : '' ?>>
                                        Auditoría Integral de Seguridad y Salud en el Trabajo (SST) – Modalidad Alto Riesgo
                                    </option>
                                </select>
                                <div class="form-text">Tipo de auditoría SG-SST a realizar según normativa colombiana</div>
                                <?php if (isset($errors['tipo_auditoria'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['tipo_auditoria']) ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Email Cliente (solo lectura) -->
                            <div class="col-md-6 mb-3">
                                <label for="email_cliente" class="form-label">Email del Cliente</label>
                                <input type="email"
                                       class="form-control bg-light"
                                       id="email_cliente"
                                       readonly
                                       value="<?= !empty($contrato) && isset($clientes) ? (array_values(array_filter($clientes, fn($c) => $c['id_cliente'] == $contrato['id_cliente']))[0]['email_contacto'] ?? '') : '' ?>"
                                       placeholder="Se carga automáticamente del cliente">
                                <div class="form-text">Email de contacto registrado en el cliente</div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select <?= isset($errors['estado']) ? 'is-invalid' : '' ?>"
                                    id="estado"
                                    name="estado">
                                <option value="activo" <?= old('estado', $contrato['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>
                                    Activo
                                </option>
                                <option value="inactivo" <?= old('estado', $contrato['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>
                                    Inactivo
                                </option>
                            </select>
                            <?php if (isset($errors['estado'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['estado']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Observaciones -->
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control <?= isset($errors['observaciones']) ? 'is-invalid' : '' ?>"
                                      id="observaciones"
                                      name="observaciones"
                                      rows="3"
                                      placeholder="Información adicional sobre el contrato (opcional)"><?= old('observaciones', $contrato['observaciones'] ?? '') ?></textarea>
                            <?php if (isset($errors['observaciones'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['observaciones']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= site_url('admin/contratos') ?>" class="btn btn-secondary">
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

<!-- jQuery (requerido por Select2) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function cargarEmailCliente() {
    const selectCliente = document.getElementById('id_cliente');
    const emailCliente = document.getElementById('email_cliente');

    const selectedOption = selectCliente.options[selectCliente.selectedIndex];
    const email = selectedOption.getAttribute('data-email');

    emailCliente.value = email || '';
}

// Inicializar Select2 en los campos principales
$(document).ready(function() {
    // Select2 para Cliente
    $('#id_cliente').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccionar cliente...',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "No se encontraron clientes";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });

    // Select2 para Proveedor
    $('#id_proveedor').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccionar proveedor...',
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

    // Select2 para Usuario Responsable del Proveedor
    $('#id_usuario_responsable').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccionar usuario responsable...',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "No se encontraron usuarios";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });

    // Evento para cargar email cuando cambia el cliente
    $('#id_cliente').on('select2:select', function(e) {
        cargarEmailCliente();
    });

    // Cargar email al cargar la página si hay un cliente seleccionado
    cargarEmailCliente();
});
</script>
</body>
</html>
