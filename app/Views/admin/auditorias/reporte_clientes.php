<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-building"></i> Reporte por Clientes</h2>
        <p class="text-muted mb-0">Estado de auditorías agrupadas por cliente</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?= view('partials/filtro_anio', ['anio_actual' => $anio ?? date('Y'), 'url_base' => site_url('admin/auditorias/reporte-clientes')]) ?>
        <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Resumen Ejecutivo -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-primary text-white h-100 card-filtro" data-filtro="total" style="cursor: pointer;" title="Mostrar todos">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['total_clientes'] ?></h2>
                <small>Total Clientes</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-info text-white h-100 card-filtro" data-filtro="con_auditorias" style="cursor: pointer;" title="Filtrar clientes con auditorías">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['con_auditorias'] ?></h2>
                <small>Con Auditorías</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-secondary text-white h-100 card-filtro" data-filtro="sin_auditorias" style="cursor: pointer;" title="Filtrar clientes sin auditorías">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['sin_auditorias'] ?></h2>
                <small>Sin Auditorías</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['total_cerradas'] ?></h2>
                <small>Aud. Cerradas</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-warning h-100">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['total_en_revision'] ?></h2>
                <small>En Revisión</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-dark text-white h-100">
            <div class="card-body text-center py-3">
                <h2 class="mb-0"><?= $resumen['total_en_proveedor'] ?></h2>
                <small>En Proveedor</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Clientes -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-table"></i> Clientes y sus Auditorías</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="tablaClientes" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th>Cliente</th>
                        <th class="text-center">Total Aud.</th>
                        <th class="text-center">Cerradas</th>
                        <th class="text-center">En Revisión</th>
                        <th class="text-center">En Proveedor</th>
                        <th class="text-center">% Cumplimiento</th>
                        <th>Proveedores</th>
                        <th>Estado General</th>
                    </tr>
                    <tr class="filters bg-light">
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar cliente..."></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th><input type="text" class="form-control form-control-sm" placeholder="Buscar proveedor..."></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                    <?php
                        // Determinar estado general del cliente
                        $estadoGeneral = 'sin_auditorias';
                        $badgeClass = 'secondary';
                        $estadoTexto = 'Sin auditorías';

                        if ($cliente['total_auditorias'] > 0) {
                            if ($cliente['cerradas'] === $cliente['total_auditorias']) {
                                $estadoGeneral = 'todas_cerradas';
                                $badgeClass = 'success';
                                $estadoTexto = 'Todas cerradas';
                            } elseif ($cliente['en_revision'] > 0) {
                                $estadoGeneral = 'en_revision';
                                $badgeClass = 'warning';
                                $estadoTexto = 'En revisión';
                            } elseif ($cliente['en_proveedor'] > 0) {
                                $estadoGeneral = 'en_proveedor';
                                $badgeClass = 'info';
                                $estadoTexto = 'En proveedor';
                            } else {
                                $estadoGeneral = 'mixto';
                                $badgeClass = 'primary';
                                $estadoTexto = 'En proceso';
                            }
                        }

                        // Obtener TODAS las auditorías con su estado (cada una por separado)
                        // Colores consistentes con reporte-progreso:
                        // success=cerrada, info=en_revision_consultor, warning=en_proveedor, dark=sin_iniciar
                        $auditoriasConEstado = [];
                        foreach ($cliente['auditorias'] as $aud) {
                            if ($aud['proveedor']) {
                                // Determinar color según estado (mismo esquema que reporte-progreso)
                                $colorEstado = 'dark'; // default: sin iniciar
                                $estadoTextoP = 'Sin iniciar';
                                if ($aud['estado'] === 'cerrada') {
                                    $colorEstado = 'success';
                                    $estadoTextoP = 'Cerrada';
                                } elseif ($aud['estado'] === 'en_revision_consultor') {
                                    $colorEstado = 'info';
                                    $estadoTextoP = 'En revisión consultor';
                                } elseif ($aud['estado'] === 'en_proveedor') {
                                    $colorEstado = 'warning';
                                    $estadoTextoP = 'En proveedor';
                                }

                                // Guardar cada auditoría por separado
                                $auditoriasConEstado[] = [
                                    'id_auditoria' => $aud['id_auditoria'],
                                    'nombre' => $aud['proveedor'],
                                    'estado' => $aud['estado'],
                                    'estadoTexto' => $estadoTextoP,
                                    'color' => $colorEstado,
                                ];
                            }
                        }
                    ?>
                    <tr data-tiene-auditorias="<?= $cliente['total_auditorias'] > 0 ? '1' : '0' ?>">
                        <td>
                            <strong><?= esc($cliente['cliente']) ?></strong>
                            <br><small class="text-muted">NIT: <?= esc($cliente['cliente_nit']) ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary"><?= $cliente['total_auditorias'] ?></span>
                        </td>
                        <td class="text-center">
                            <?php if ($cliente['cerradas'] > 0): ?>
                                <span class="badge bg-success"><?= $cliente['cerradas'] ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($cliente['en_revision'] > 0): ?>
                                <span class="badge bg-warning"><?= $cliente['en_revision'] ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($cliente['en_proveedor'] > 0): ?>
                                <span class="badge bg-info"><?= $cliente['en_proveedor'] ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center" data-order="<?= $cliente['promedio_cumplimiento'] ?? 0 ?>">
                            <?php if ($cliente['promedio_cumplimiento'] !== null): ?>
                                <div class="progress" style="height: 20px; min-width: 80px;">
                                    <div class="progress-bar bg-<?= $cliente['promedio_cumplimiento'] >= 80 ? 'success' : ($cliente['promedio_cumplimiento'] >= 50 ? 'warning' : 'danger') ?>"
                                         role="progressbar"
                                         style="width: <?= $cliente['promedio_cumplimiento'] ?>%">
                                        <?= $cliente['promedio_cumplimiento'] ?>%
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($auditoriasConEstado)): ?>
                                <?php foreach ($auditoriasConEstado as $aud): ?>
                                    <span class="badge bg-<?= $aud['color'] ?> mb-1" title="<?= $aud['estadoTexto'] ?>">
                                        <?= esc($aud['nombre']) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $badgeClass ?>"><?= $estadoTexto ?></span>
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
        <h6 class="mb-0"><i class="bi bi-info-circle"></i> Leyenda de Colores - Proveedores</h6>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-auto">
                <span class="badge bg-success">Proveedor</span>
                <small class="text-muted ms-1">Cerrada</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-info">Proveedor</span>
                <small class="text-muted ms-1">En revisión consultor</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-warning">Proveedor</span>
                <small class="text-muted ms-1">En proveedor</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-dark">Proveedor</span>
                <small class="text-muted ms-1">Sin iniciar</small>
            </div>
        </div>
        <hr>
        <h6 class="text-muted mb-2">Estado General del Cliente</h6>
        <div class="row g-2">
            <div class="col-auto">
                <span class="badge bg-success">Todas cerradas</span>
                <small class="text-muted ms-1">Todas las auditorías finalizadas</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-warning">En revisión</span>
                <small class="text-muted ms-1">Tiene auditorías pendientes de calificar</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-info">En proveedor</span>
                <small class="text-muted ms-1">Proveedor está diligenciando</small>
            </div>
            <div class="col-auto">
                <span class="badge bg-secondary">Sin auditorías</span>
                <small class="text-muted ms-1">Cliente sin auditorías asignadas</small>
            </div>
        </div>
    </div>
</div>

<style>
.card-filtro {
    transition: all 0.2s ease;
}
.card-filtro:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.card-filtro.active {
    box-shadow: 0 0 0 4px rgba(255,255,255,0.5), 0 4px 15px rgba(0,0,0,0.3);
    transform: translateY(-3px);
}
</style>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function() {
    var filtroActual = null;

    var table = $('#tablaClientes').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        order: [[0, 'asc']],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"B>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
                className: 'btn btn-success btn-sm',
                title: 'Reporte_Clientes_<?= date('Y-m-d') ?>',
                exportOptions: {
                    columns: ':visible:not(:last-child)'
                }
            }
        ],
        orderCellsTop: true,
        fixedHeader: true,
        initComplete: function() {
            var api = this.api();
            api.columns().eq(0).each(function(colIdx) {
                var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                var input = $('input', cell);
                input.on('keyup change', function() {
                    api.column(colIdx).search(this.value).draw();
                });
            });
        }
    });

    // Filtro por cards
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (filtroActual === null || filtroActual === 'total') {
            return true;
        }

        var row = table.row(dataIndex).node();
        var tieneAuditorias = $(row).data('tiene-auditorias');

        if (filtroActual === 'con_auditorias') {
            return tieneAuditorias == 1;
        } else if (filtroActual === 'sin_auditorias') {
            return tieneAuditorias == 0;
        }

        return true;
    });

    $('.card-filtro').on('click', function() {
        var filtro = $(this).data('filtro');
        $('.card-filtro').removeClass('active');

        if (filtroActual === filtro) {
            filtroActual = null;
        } else {
            filtroActual = filtro;
            $(this).addClass('active');
        }

        table.draw();
    });
});
</script>

<?= $this->endSection() ?>
