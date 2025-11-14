<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-gear"></i> Gestión de Servicios</h2>
    <div>
        <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <a href="<?= site_url('admin/servicios/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Servicio
        </a>
    </div>
</div>

<?= view('partials/flash') ?>

<?php if (empty($servicios)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">No hay servicios registrados</h5>
            <p class="text-muted">Crea el primer servicio usando el botón "Nuevo Servicio"</p>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaServicios" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicios as $servicio): ?>
                            <tr>
                                <td><?= esc($servicio['id_servicio']) ?></td>
                                <td><strong><?= esc($servicio['nombre']) ?></strong></td>
                                <td>
                                    <?php if ($servicio['activo'] == 1): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= site_url('admin/servicios/edit/' . $servicio['id_servicio']) ?>"
                                       class="btn btn-sm btn-warning"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-danger btn-eliminar"
                                            data-id="<?= $servicio['id_servicio'] ?>"
                                            data-nombre="<?= esc($servicio['nombre']) ?>"
                                            title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3 text-muted">
        <small>
            <i class="bi bi-info-circle"></i>
            Total: <?= count($servicios) ?> servicio(s)
        </small>
    </div>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- jQuery (requerido por DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#tablaServicios').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 10,
            order: [[1, 'asc']], // Ordenar por nombre
            columnDefs: [
                { orderable: false, targets: -1 } // Deshabilitar orden en columna de Acciones
            ]
        });

        // Manejar eliminación
        $(document).on('click', '.btn-eliminar', function() {
            const idServicio = $(this).data('id');
            const nombreServicio = $(this).data('nombre');

            if (!confirm(`¿Está seguro de eliminar el servicio "${nombreServicio}"?\n\nEsta acción NO se puede deshacer.`)) {
                return;
            }

            // Deshabilitar botón mientras se procesa
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: `<?= site_url('admin/servicios/delete/') ?>${idServicio}`,
                method: 'POST',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    alert('Error al eliminar el servicio. Por favor, intente nuevamente.');
                    btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                }
            });
        });
    });
    </script>
<?php endif; ?>

<?= $this->endSection() ?>
