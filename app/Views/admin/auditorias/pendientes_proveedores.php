<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-check"></i> Auditorías Pendientes - Proveedores</h2>
    <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<?php if (empty($auditorias)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">No hay auditorías pendientes</h5>
            <p class="text-muted">No hay auditorías asignadas a proveedores en el sistema.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaPendientes" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Proveedor</th>
                            <th>Consultor</th>
                            <th>Clientes Asignados</th>
                            <th>Usuario Responsable</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tfoot class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Proveedor</th>
                            <th>Consultor</th>
                            <th>Clientes Asignados</th>
                            <th>Usuario Responsable</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($auditorias as $auditoria): ?>
                            <?php
                            $estadoBadge = [
                                'borrador' => ['class' => 'secondary', 'icon' => 'pencil-square', 'text' => 'Borrador'],
                                'asignada' => ['class' => 'warning', 'icon' => 'envelope-exclamation', 'text' => 'Asignada'],
                                'en_progreso' => ['class' => 'primary', 'icon' => 'hourglass-split', 'text' => 'En Progreso'],
                            ];
                            $estado = $estadoBadge[$auditoria['estado']] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => ucfirst($auditoria['estado'])];
                            ?>
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
                                    <?php
                                    $totalClientes = (int)$auditoria['total_clientes_proveedor'];
                                    $clientesAsignados = (int)$auditoria['clientes_asignados'];
                                    $clientesFaltantes = count($auditoria['clientes_faltantes'] ?? []);

                                    // Determinar el color del badge
                                    if ($clientesAsignados == 0) {
                                        $badgeClass = 'bg-secondary';
                                        $iconClass = 'exclamation-circle';
                                    } elseif ($clientesFaltantes > 0) {
                                        $badgeClass = 'bg-warning text-dark';
                                        $iconClass = 'exclamation-triangle';
                                    } else {
                                        $badgeClass = 'bg-success';
                                        $iconClass = 'check-circle';
                                    }
                                    ?>
                                    <div class="mb-1">
                                        <span class="badge <?= $badgeClass ?>">
                                            <i class="bi bi-<?= $iconClass ?>"></i>
                                            <?= $clientesAsignados ?> / <?= $totalClientes ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($auditoria['clientes'])): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalClientes<?= $auditoria['id_auditoria'] ?>">
                                            <i class="bi bi-eye"></i> Ver detalles
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= esc($auditoria['usuario_responsable_nombre'] ?? 'N/A') ?>
                                    <br>
                                    <small class="text-muted"><?= esc($auditoria['usuario_responsable_email'] ?? '') ?></small>
                                </td>
                                <td data-order="<?= !empty($auditoria['created_at']) ? strtotime($auditoria['created_at']) : 0 ?>">
                                    <?php
                                    if (!empty($auditoria['created_at'])) {
                                        $fecha = new DateTime($auditoria['created_at']);
                                        echo $fecha->format('d/m/Y H:i');
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $estado['class'] ?>">
                                        <i class="bi bi-<?= $estado['icon'] ?>"></i> <?= $estado['text'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-danger btn-sm btn-eliminar"
                                            data-id="<?= $auditoria['id_auditoria'] ?>"
                                            data-codigo="<?= esc($auditoria['codigo_formato']) ?>"
                                            title="Eliminar auditoría">
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

    <!-- Modales para mostrar clientes -->
    <?php foreach ($auditorias as $auditoria): ?>
        <div class="modal fade" id="modalClientes<?= $auditoria['id_auditoria'] ?>" tabindex="-1" aria-labelledby="modalClientesLabel<?= $auditoria['id_auditoria'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalClientesLabel<?= $auditoria['id_auditoria'] ?>">
                            <i class="bi bi-building"></i> Clientes - <?= esc($auditoria['codigo_formato']) ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-success">
                                    <i class="bi bi-check-circle"></i> Clientes Asignados (<?= count($auditoria['clientes']) ?>)
                                </h6>
                                <?php if (!empty($auditoria['clientes'])): ?>
                                    <ul class="list-group mb-3">
                                        <?php foreach ($auditoria['clientes'] as $cliente): ?>
                                            <li class="list-group-item">
                                                <strong><?= esc($cliente['razon_social']) ?></strong>
                                                <br>
                                                <small class="text-muted">NIT: <?= esc($cliente['nit']) ?></small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-muted fst-italic">No hay clientes asignados</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Clientes Faltantes (<?= count($auditoria['clientes_faltantes']) ?>)
                                </h6>
                                <?php if (!empty($auditoria['clientes_faltantes'])): ?>
                                    <ul class="list-group mb-3">
                                        <?php foreach ($auditoria['clientes_faltantes'] as $cliente): ?>
                                            <li class="list-group-item list-group-item-warning">
                                                <strong><?= esc($cliente['razon_social']) ?></strong>
                                                <br>
                                                <small class="text-muted">NIT: <?= esc($cliente['nit']) ?></small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="alert alert-success">
                                        <i class="bi bi-check-circle"></i> Todos los clientes del proveedor están asignados
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="mt-3 text-muted">
        <small>
            <i class="bi bi-info-circle"></i>
            Total: <?= count($auditorias) ?> auditoría(s) pendiente(s) de diligenciamiento por proveedores
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
        var table = $('#tablaPendientes').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            order: [[5, 'desc']], // Ordenar por fecha de creación descendente (columna 5)
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"B>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Auditorias_Pendientes_Proveedores',
                    exportOptions: {
                        columns: ':visible:not(:last-child)' // Excluir columna de acciones
                    }
                }
            ],
            responsive: true,
            columnDefs: [
                { orderable: false, targets: -1 } // Deshabilitar orden en columna de Acciones
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

        // Manejar eliminación de auditoría
        $(document).on('click', '.btn-eliminar', function() {
            const idAuditoria = $(this).data('id');
            const codigoAuditoria = $(this).data('codigo');

            if (!confirm(`¿Está seguro de eliminar la auditoría ${codigoAuditoria}?\n\nEsta acción eliminará:\n- La auditoría\n- Todos los items asociados\n- Todas las evidencias\n- Todos los clientes asignados\n- El historial de cambios\n\nEsta acción NO se puede deshacer.`)) {
                return;
            }

            // Deshabilitar botón mientras se procesa
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: `<?= site_url('admin/auditorias/') ?>${idAuditoria}/eliminar`,
                method: 'POST',
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        // Mostrar mensaje de éxito
                        alert(response.message);
                        // Recargar la página para refrescar la tabla
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    alert('Error al eliminar la auditoría. Por favor, intente nuevamente.');
                    btn.prop('disabled', false).html('<i class="bi bi-trash"></i>');
                }
            });
        });
    });
    </script>
<?php endif; ?>

<?= $this->endSection() ?>
