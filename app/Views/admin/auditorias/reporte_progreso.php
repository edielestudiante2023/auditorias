<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-graph-up-arrow"></i> Reporte de Progreso de Auditorías</h2>
        <p class="text-muted mb-0">Monitoreo en tiempo real del avance de todas las auditorías</p>
    </div>
    <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<!-- Resumen Ejecutivo -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-primary text-white h-100">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['total'] ?></h2>
                <small>Total</small>
            </div>
        </div>
    </div>

    <?php if ($resumen['completas_sin_enviar'] > 0): ?>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-danger text-white h-100 border-3">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> <?= $resumen['completas_sin_enviar'] ?></h2>
                <small><strong>100% SIN ENVIAR</strong></small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($resumen['vencidas'] > 0): ?>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-danger text-white h-100">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><i class="bi bi-clock-fill"></i> <?= $resumen['vencidas'] ?></h2>
                <small>Vencidas</small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-info text-white h-100">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['enviadas_consultor'] ?></h2>
                <small>En Revisión</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-warning h-100">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['en_progreso_alto'] + $resumen['en_progreso_medio'] + $resumen['en_progreso_bajo'] ?></h2>
                <small>En Progreso</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['cerradas'] ?></h2>
                <small>Cerradas</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-dark text-white h-100">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['sin_iniciar'] ?></h2>
                <small>Sin Iniciar</small>
            </div>
        </div>
    </div>
</div>

<?php if ($resumen['completas_sin_enviar'] > 0): ?>
<div class="alert alert-danger mb-4">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>ATENCION:</strong> Hay <?= $resumen['completas_sin_enviar'] ?> auditoría(s) completadas al 100% que el proveedor NO ha enviado a revisión.
    Estas auditorías requieren que el proveedor haga clic en "Finalizar y Enviar a Revisión".
</div>
<?php endif; ?>

<!-- Tabla de Auditorías -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-0"><i class="bi bi-table"></i> Detalle de Auditorías</h5>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="tablaReporte" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Proveedor</th>
                        <th>Consultor</th>
                        <th class="text-center">Progreso</th>
                        <th class="text-center">Items</th>
                        <th class="text-center">Clientes</th>
                        <th>Estado</th>
                        <th>F. Vencimiento</th>
                        <th>Acciones</th>
                    </tr>
                    <tr class="filters bg-light">
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($auditorias as $aud): ?>
                    <tr class="<?= $aud['badge_class'] === 'danger' ? 'table-danger' : '' ?>">
                        <td>
                            <strong><?= esc($aud['codigo_formato']) ?></strong>
                        </td>
                        <td>
                            <?= esc($aud['proveedor']) ?>
                            <br><small class="text-muted">NIT: <?= esc($aud['proveedor_nit']) ?></small>
                        </td>
                        <td><?= esc($aud['consultor']) ?></td>
                        <td class="text-center" style="min-width: 150px;" data-order="<?= $aud['progreso'] ?>">
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-<?= $aud['progreso'] >= 100 ? 'success' : ($aud['progreso'] >= 70 ? 'primary' : ($aud['progreso'] >= 30 ? 'warning' : 'secondary')) ?>"
                                     role="progressbar"
                                     style="width: <?= $aud['progreso'] ?>%">
                                    <?= number_format($aud['progreso'], 0) ?>%
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">
                                <?= $aud['items_completados'] ?>/<?= $aud['items_total'] ?>
                            </span>
                            <br>
                            <small class="text-muted">
                                G: <?= $aud['globales_completos'] ?>/<?= $aud['globales_total'] ?>
                                | C: <?= $aud['por_cliente_completos'] ?>/<?= $aud['por_cliente_total'] ?>
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info"><?= $aud['total_clientes'] ?></span>
                        </td>
                        <td>
                            <span class="badge bg-<?= esc($aud['badge_class']) ?>">
                                <?= esc($aud['estado_descriptivo']) ?>
                            </span>
                        </td>
                        <td data-order="<?= $aud['fecha_programada'] ? strtotime($aud['fecha_programada']) : 0 ?>">
                            <?php if ($aud['fecha_programada']): ?>
                                <?= date('d/m/Y', strtotime($aud['fecha_programada'])) ?>
                                <?php if ($aud['vencida'] && $aud['estado'] === 'en_proveedor'): ?>
                                    <br><span class="badge bg-danger">VENCIDA</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= site_url('admin/auditorias/pendientes-proveedores') ?>"
                               class="btn btn-sm btn-outline-primary"
                               title="Ver en lista">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Leyenda -->
<div class="card mt-4">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Leyenda de Estados</h6>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <div class="col-auto">
                <span class="badge bg-danger">100% - PENDIENTE ENVIAR</span>
                <small class="text-muted ms-1">Proveedor completó pero no envió</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-primary">En progreso alto</span>
                <small class="text-muted ms-1">70-99%</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-warning">En progreso</span>
                <small class="text-muted ms-1">30-69%</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-secondary">Recién iniciada</span>
                <small class="text-muted ms-1">1-29%</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-dark">Sin iniciar</span>
                <small class="text-muted ms-1">0%</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-info">En revisión consultor</span>
                <small class="text-muted ms-1">Enviada por proveedor</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-success">Cerrada</span>
                <small class="text-muted ms-1">Finalizada</small>
            </div>
        </div>
        <hr>
        <small class="text-muted">
            <strong>G:</strong> Items Globales |
            <strong>C:</strong> Items por Cliente (items × clientes)
        </small>
    </div>
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
    var table = $('#tablaReporte').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        order: [[6, 'asc'], [3, 'desc'], [7, 'asc']], // 1) Estado (pendientes primero), 2) Progreso desc, 3) F. Vencimiento asc
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"B>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
                className: 'btn btn-success btn-sm',
                title: 'Reporte_Progreso_Auditorias_<?= date('Y-m-d') ?>',
                exportOptions: {
                    columns: ':visible:not(:last-child)' // Excluir columna de acciones
                }
            }
        ],
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [-1] } // Deshabilitar orden en columna de acciones
        ],
        orderCellsTop: true,
        fixedHeader: true,
        initComplete: function() {
            var api = this.api();

            // Configurar filtros en la segunda fila del thead
            api.columns().eq(0).each(function(colIdx) {
                // Obtener el input de la fila de filtros
                var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                var input = $('input', cell);

                // Evento de búsqueda
                input.on('keyup change', function() {
                    api.column(colIdx).search(this.value).draw();
                });
            });
        }
    });
});
</script>

<?= $this->endSection() ?>
