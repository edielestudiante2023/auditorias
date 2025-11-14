<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-check-circle"></i> Auditorías Completadas por Proveedores</h2>
    <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<?php if (empty($auditorias)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">No hay auditorías completadas</h5>
            <p class="text-muted">No hay auditorías cerradas en el sistema.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaCompletadas" class="table table-hover align-middle" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Proveedor</th>
                            <th>Consultor</th>
                            <th>Usuario Responsable</th>
                            <th>Fecha Cierre</th>
                            <th>% Cumplimiento</th>
                            <th>Estado</th>
                            <th class="text-center">Clientes</th>
                        </tr>
                    </thead>
                    <tfoot class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Proveedor</th>
                            <th>Consultor</th>
                            <th>Usuario Responsable</th>
                            <th>Fecha Cierre</th>
                            <th>% Cumplimiento</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($auditorias as $auditoria): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($auditoria['codigo_formato'] ?? 'N/A') ?></strong>
                                    <br>
                                    <small class="text-muted">v<?= esc($auditoria['version_formato'] ?? '1.0') ?></small>
                                </td>
                                <td>
                                    <?= esc($auditoria['proveedor_nombre']) ?>
                                    <br>
                                    <small class="text-muted">NIT: <?= esc($auditoria['proveedor_nit']) ?></small>
                                </td>
                                <td><?= esc($auditoria['consultor_nombre']) ?></td>
                                <td>
                                    <?= esc($auditoria['usuario_responsable_nombre'] ?? 'N/A') ?>
                                    <br>
                                    <small class="text-muted"><?= esc($auditoria['usuario_responsable_email'] ?? '') ?></small>
                                </td>
                                <td data-order="<?= !empty($auditoria['updated_at']) ? strtotime($auditoria['updated_at']) : 0 ?>">
                                    <?php
                                    if (!empty($auditoria['updated_at'])) {
                                        $fecha = new DateTime($auditoria['updated_at']);
                                        echo $fecha->format('d/m/Y H:i');
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td data-order="<?= $auditoria['porcentaje_cumplimiento'] ?? 0 ?>">
                                    <?php
                                    $porcentaje = $auditoria['porcentaje_cumplimiento'] ?? 0;
                                    $badgeClass = 'secondary';
                                    if ($porcentaje >= 90) {
                                        $badgeClass = 'success';
                                    } elseif ($porcentaje >= 70) {
                                        $badgeClass = 'info';
                                    } elseif ($porcentaje >= 50) {
                                        $badgeClass = 'warning';
                                    } else {
                                        $badgeClass = 'danger';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= number_format($porcentaje, 1) ?>%</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="bi bi-lock-fill"></i> Cerrada
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-info btn-sm btn-ver-clientes"
                                            data-id="<?= $auditoria['id_auditoria'] ?>"
                                            title="Ver clientes asignados">
                                        <i class="bi bi-building"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para ver clientes -->
    <div class="modal fade" id="modalClientes" tabindex="-1" aria-labelledby="modalClientesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalClientesLabel">
                        <i class="bi bi-building"></i> Clientes Asignados
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalClientesBody">
                    <div class="text-center py-3">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 text-muted">
        <small>
            <i class="bi bi-info-circle"></i>
            Total: <?= count($auditorias) ?> auditoría(s) completada(s) por proveedores
        </small>
    </div>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

    <!-- jQuery (requerido por DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <script>
    $(document).ready(function() {
        var table = $('#tablaCompletadas').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            order: [[4, 'desc']], // Ordenar por fecha de cierre descendente
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"B>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Auditorias_Completadas_Proveedores',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
            responsive: true,
            columnDefs: [
                { orderable: false, targets: -1 } // Deshabilitar orden en columna de Clientes
            ],
            initComplete: function() {
                // Agregar filtros en cada columna del footer excepto la última
                this.api().columns().every(function(index) {
                    var column = this;
                    var title = $(column.header()).text();

                    if (index < this.api().columns().count() - 1) {
                        $('<input type="text" class="form-control form-control-sm" placeholder="Filtrar '+title+'" />')
                            .appendTo($(column.footer()).empty())
                            .on('keyup change clear', function() {
                                if (column.search() !== this.value) {
                                    column.search(this.value).draw();
                                }
                            });
                    }
                });
            }
        });

        // Manejar visualización de clientes
        $(document).on('click', '.btn-ver-clientes', function() {
            const idAuditoria = $(this).data('id');
            const modalBody = $('#modalClientesBody');
            const modal = new bootstrap.Modal(document.getElementById('modalClientes'));

            // Mostrar loading
            modalBody.html('<div class="text-center py-3"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
            modal.show();

            // Cargar clientes vía AJAX
            $.ajax({
                url: `<?= site_url('admin/auditorias/') ?>${idAuditoria}/clientes`,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        let html = '<div class="row"><div class="col-md-12">';
                        html += `<h6 class="text-primary mb-3"><i class="bi bi-check-circle"></i> Clientes Asignados (${data.clientes.length} / ${data.total_proveedor})</h6>`;

                        if (data.clientes.length > 0) {
                            html += '<ul class="list-group mb-3">';
                            data.clientes.forEach(function(cliente) {
                                html += '<li class="list-group-item">';
                                html += `<strong>${cliente.razon_social}</strong><br>`;
                                html += `<small class="text-muted">NIT: ${cliente.nit}</small>`;
                                html += '</li>';
                            });
                            html += '</ul>';
                        } else {
                            html += '<p class="text-muted fst-italic">No hay clientes asignados a esta auditoría</p>';
                        }

                        html += '</div></div>';
                        modalBody.html(html);
                    } else {
                        modalBody.html('<div class="alert alert-danger">Error al cargar clientes</div>');
                    }
                },
                error: function() {
                    modalBody.html('<div class="alert alert-danger">Error de conexión</div>');
                }
            });
        });
    });
    </script>
<?php endif; ?>

<?= $this->endSection() ?>
