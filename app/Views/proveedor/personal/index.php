<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people-fill"></i> Gestión de Personal Asignado</h2>
    <a href="<?= site_url('proveedor/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <p class="text-muted mb-0">
            <i class="bi bi-info-circle"></i>
            Registra el personal que tienes asignado a cada cliente. Esta información es requerida antes de diligenciar las auditorías.
        </p>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="personalTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="clientes-tab" data-bs-toggle="tab" data-bs-target="#clientes" type="button">
            <i class="bi bi-building"></i> Por Cliente
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button">
            <i class="bi bi-people"></i> Todo el Personal
        </button>
    </li>
</ul>

<div class="tab-content" id="personalTabContent">
    <!-- Tab: Por Cliente -->
    <div class="tab-pane fade show active" id="clientes" role="tabpanel">
        <?php if (empty($clientes)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">No hay clientes asignados</h5>
                    <p class="text-muted">No tienes clientes con contratos activos.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($clientes as $cliente): ?>
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm <?= $cliente['total_personal'] > 0 ? 'border-success' : 'border-warning' ?>">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?= esc($cliente['razon_social']) ?>
                                </h5>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="bi bi-building"></i> NIT: <?= esc($cliente['nit']) ?><br>
                                        <i class="bi bi-people"></i> Personal asignado:
                                        <strong class="<?= $cliente['total_personal'] > 0 ? 'text-success' : 'text-warning' ?>">
                                            <?= $cliente['total_personal'] ?>
                                        </strong>
                                    </small>
                                </p>

                                <?php if ($cliente['total_personal'] == 0): ?>
                                    <div class="alert alert-warning alert-sm py-2 mb-3">
                                        <small><i class="bi bi-exclamation-triangle"></i> Sin personal registrado</small>
                                    </div>
                                <?php endif; ?>

                                <a href="<?= site_url('proveedor/personal/cliente/' . $cliente['id_cliente']) ?>"
                                   class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-pencil-square"></i> Gestionar Personal
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab: Todo el Personal -->
    <div class="tab-pane fade" id="personal" role="tabpanel">
        <div class="mb-3">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPersonalGlobal">
                <i class="bi bi-plus-circle"></i> Agregar Personal
            </button>
        </div>

        <?php if (empty($todoPersonal)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-person-x text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">No hay personal registrado</h5>
                    <p class="text-muted">Haz clic en "Agregar Personal" para comenzar.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaPersonal">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <th>Nombres y Apellidos</th>
                                    <th>Cargo</th>
                                    <th>Fecha Ingreso</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todoPersonal as $persona): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($persona['razon_social']) ?></strong><br>
                                            <small class="text-muted">NIT: <?= esc($persona['nit']) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= esc($persona['tipo_documento']) ?></strong><br>
                                            <small class="text-muted"><?= esc($persona['numero_documento']) ?></small>
                                        </td>
                                        <td><?= esc($persona['nombres'] . ' ' . $persona['apellidos']) ?></td>
                                        <td><?= esc($persona['cargo']) ?></td>
                                        <td>
                                            <?php
                                            if (!empty($persona['fecha_ingreso'])) {
                                                $fecha = new DateTime($persona['fecha_ingreso']);
                                                echo $fecha->format('d/m/Y');
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($persona['estado'] === 'activo'): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= site_url('proveedor/personal/cliente/' . $persona['id_cliente']) ?>"
                                               class="btn btn-sm btn-primary"
                                               title="Gestionar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
            <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
            <script>
            $(document).ready(function() {
                $('#tablaPersonal').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    pageLength: 25,
                    order: [[0, 'asc'], [2, 'asc']]
                });
            });
            </script>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Agregar Personal Global (con selector de cliente) -->
<div class="modal fade" id="modalPersonalGlobal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPersonalGlobal">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_cliente_global" class="form-label">Cliente <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_cliente_global" name="id_cliente" required>
                            <option value="">Seleccione un cliente...</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id_cliente'] ?>">
                                    <?= esc($cliente['razon_social']) ?> - NIT: <?= esc($cliente['nit']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tipo_documento_global" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_documento_global" name="tipo_documento" required>
                                <option value="CC">Cédula de Ciudadanía</option>
                                <option value="CE">Cédula de Extranjería</option>
                                <option value="PA">Pasaporte</option>
                                <option value="TI">Tarjeta de Identidad</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="numero_documento_global" class="form-label">Número Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="numero_documento_global" name="numero_documento" required maxlength="20">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nombres_global" class="form-label">Nombres <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombres_global" name="nombres" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="apellidos_global" class="form-label">Apellidos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="apellidos_global" name="apellidos" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="cargo_global" class="form-label">Cargo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cargo_global" name="cargo" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="fecha_ingreso_global" class="form-label">Fecha de Ingreso <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_ingreso_global" name="fecha_ingreso" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let modalGlobal;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar modal de Bootstrap
    modalGlobal = new bootstrap.Modal(document.getElementById('modalPersonalGlobal'));

    // Verificar si hay advertencia de personal faltante
    <?php if (session()->has('warning_personal')): ?>
        const clientesSinPersonal = <?= session('warning_personal') ?>;

        Swal.fire({
            icon: 'warning',
            title: '¡Atención!',
            html: '<p class="mb-3">Debes registrar el personal asignado antes de acceder al módulo de auditorías.</p>' +
                  '<p class="mb-2"><strong>Clientes sin personal registrado:</strong></p>' +
                  '<ul class="text-start">' +
                  clientesSinPersonal.map(cliente => `<li>${cliente}</li>`).join('') +
                  '</ul>',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc3545',
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    <?php endif; ?>

    // Guardar personal global
    document.getElementById('formPersonalGlobal').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const idCliente = document.getElementById('id_cliente_global').value;

        if (!idCliente) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debes seleccionar un cliente',
                confirmButtonColor: '#dc3545'
            });
            return;
        }

        const url = `<?= site_url('proveedor/personal/guardar/') ?>${idCliente}`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            const responseText = await response.text();
            let result;

            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error('Error al parsear JSON:', e);
                console.error('Respuesta del servidor:', responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'El servidor no respondió correctamente. Revisa la consola.',
                    confirmButtonColor: '#dc3545'
                });
                return;
            }

            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: result.message,
                    confirmButtonColor: '#198754'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Error al guardar',
                    confirmButtonColor: '#dc3545'
                });
                if (result.errors) {
                    console.error('Errores de validación:', result.errors);
                }
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al procesar la solicitud',
                confirmButtonColor: '#dc3545'
            });
            console.error('Error completo:', error);
        }
    });
});
</script>

<?= $this->endSection() ?>
