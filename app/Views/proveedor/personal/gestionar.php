<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-people-fill"></i> Personal Asignado</h2>
        <p class="text-muted mb-0">
            <i class="bi bi-building"></i> <?= esc($cliente['razon_social']) ?> - NIT: <?= esc($cliente['nit']) ?>
        </p>
    </div>
    <div>
        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modalPersonal">
            <i class="bi bi-plus-circle"></i> Agregar Personal
        </button>
        <a href="<?= site_url('proveedor/personal') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?= view('partials/flash') ?>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (empty($personal)): ?>
            <div class="text-center py-5">
                <i class="bi bi-person-x text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-muted">No hay personal registrado</h5>
                <p class="text-muted">Agrega el personal que tienes asignado a este cliente.</p>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPersonal">
                    <i class="bi bi-plus-circle"></i> Agregar Personal
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th>Nombres y Apellidos</th>
                            <th>Cargo</th>
                            <th>Fecha Ingreso</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($personal as $persona): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($persona['tipo_documento']) ?></strong><br>
                                    <small class="text-muted"><?= esc($persona['numero_documento']) ?></small>
                                </td>
                                <td>
                                    <?= esc($persona['nombres'] . ' ' . $persona['apellidos']) ?>
                                </td>
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
                                    <button class="btn btn-sm btn-primary" onclick="editarPersonal(<?= $persona['id_personal'] ?>)" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="cambiarEstado(<?= $persona['id_personal'] ?>)" title="Cambiar estado">
                                        <i class="bi bi-toggle-on"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarPersonal(<?= $persona['id_personal'] ?>)" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Agregar/Editar Personal -->
<div class="modal fade" id="modalPersonal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Agregar Personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPersonal">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="id_personal" name="id_personal">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                                <option value="CC">Cédula de Ciudadanía</option>
                                <option value="CE">Cédula de Extranjería</option>
                                <option value="PA">Pasaporte</option>
                                <option value="TI">Tarjeta de Identidad</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="numero_documento" class="form-label">Número Documento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="numero_documento" name="numero_documento" required maxlength="20">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombres" name="nombres" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cargo" name="cargo" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="fecha_ingreso" class="form-label">Fecha de Ingreso <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" required>
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
const idCliente = <?= $cliente['id_cliente'] ?>;
let modal;

// Esperar a que el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar modal de Bootstrap
    modal = new bootstrap.Modal(document.getElementById('modalPersonal'));
});

// Agregar nuevo personal (limpiar formulario)
document.addEventListener('DOMContentLoaded', function() {
    const btnAgregar = document.querySelector('[data-bs-target="#modalPersonal"]');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function() {
            document.getElementById('modalTitle').textContent = 'Agregar Personal';
            document.getElementById('formPersonal').reset();
            document.getElementById('id_personal').value = '';
        });
    }
});

// Guardar personal (crear o actualizar)
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('formPersonal').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const url = `<?= site_url('proveedor/personal/guardar/') ?>${idCliente}`;

        console.log('Enviando datos a:', url);
        console.log('FormData:', Object.fromEntries(formData));

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            console.log('Response status:', response.status);

            // Obtener el texto de la respuesta primero
            const responseText = await response.text();
            console.log('Response text:', responseText);

            // Intentar parsear como JSON
            let result;
            try {
                result = JSON.parse(responseText);
                console.log('Result:', result);
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

// Editar personal
async function editarPersonal(idPersonal) {
    try {
        const response = await fetch(`<?= site_url('proveedor/personal/obtener/') ?>${idPersonal}`);
        const result = await response.json();

        if (result.success) {
            const data = result.data;
            document.getElementById('modalTitle').textContent = 'Editar Personal';
            document.getElementById('id_personal').value = data.id_personal;
            document.getElementById('tipo_documento').value = data.tipo_documento;
            document.getElementById('numero_documento').value = data.numero_documento;
            document.getElementById('nombres').value = data.nombres;
            document.getElementById('apellidos').value = data.apellidos;
            document.getElementById('cargo').value = data.cargo;
            document.getElementById('fecha_ingreso').value = data.fecha_ingreso;

            modal.show();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message,
                confirmButtonColor: '#dc3545'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al cargar los datos',
            confirmButtonColor: '#dc3545'
        });
        console.error(error);
    }
}

// Cambiar estado
async function cambiarEstado(idPersonal) {
    const result = await Swal.fire({
        icon: 'question',
        title: '¿Cambiar estado?',
        text: '¿Deseas cambiar el estado de este personal?',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d'
    });

    if (!result.isConfirmed) return;

    try {
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const response = await fetch(`<?= site_url('proveedor/personal/cambiar-estado/') ?>${idPersonal}`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
                confirmButtonColor: '#198754'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#dc3545'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al procesar la solicitud',
            confirmButtonColor: '#dc3545'
        });
        console.error(error);
    }
}

// Eliminar personal
async function eliminarPersonal(idPersonal) {
    const result = await Swal.fire({
        icon: 'warning',
        title: '¿Eliminar personal?',
        text: 'Esta acción no se puede deshacer.',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    });

    if (!result.isConfirmed) return;

    try {
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const response = await fetch(`<?= site_url('proveedor/personal/eliminar/') ?>${idPersonal}`, {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: data.message,
                confirmButtonColor: '#198754'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message,
                confirmButtonColor: '#dc3545'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error al procesar la solicitud',
            confirmButtonColor: '#dc3545'
        });
        console.error(error);
    }
}
</script>

<?= $this->endSection() ?>
