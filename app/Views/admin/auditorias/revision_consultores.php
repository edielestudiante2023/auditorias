<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-list-ul"></i> Auditorías - Revisión Consultores</h2>
    <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<?php if (empty($auditorias)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">No hay auditorías</h5>
            <p class="text-muted">No hay auditorías en el sistema.</p>
        </div>
    </div>
<?php else: ?>
    <!-- Estadísticas rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <?php
                    $total = count($auditorias);
                    $enRevision = count(array_filter($auditorias, fn($a) => $a['estado'] === 'en_revision_consultor'));
                    $cerradas = count(array_filter($auditorias, fn($a) => $a['estado'] === 'cerrada'));
                    $pendientes = count(array_filter($auditorias, fn($a) => in_array($a['estado'], ['borrador', 'asignada', 'en_progreso'])));
                    ?>
                    <h3 class="text-primary mb-0"><?= $total ?></h3>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-warning mb-0"><?= $pendientes ?></h3>
                    <small class="text-muted">Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-info mb-0"><?= $enRevision ?></h3>
                    <small class="text-muted">En Revisión</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-success mb-0"><?= $cerradas ?></h3>
                    <small class="text-muted">Cerradas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaRevision" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Proveedor</th>
                            <th>Consultor</th>
                            <th>Usuario Responsable</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th>Calificación</th>
                        </tr>
                    </thead>
                    <tfoot class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Proveedor</th>
                            <th>Consultor</th>
                            <th>Usuario Responsable</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th>Calificación</th>
                        </tr>
                    </tfoot>
                    <tbody>
                        <?php foreach ($auditorias as $aud): ?>
                            <?php
                            $estadoBadge = [
                                'borrador' => ['class' => 'secondary', 'icon' => 'pencil-square', 'text' => 'Borrador'],
                                'asignada' => ['class' => 'warning', 'icon' => 'envelope-exclamation', 'text' => 'Asignada'],
                                'en_progreso' => ['class' => 'primary', 'icon' => 'hourglass-split', 'text' => 'En Progreso'],
                                'en_revision_consultor' => ['class' => 'info', 'icon' => 'eye', 'text' => 'En Revisión'],
                                'cerrada' => ['class' => 'success', 'icon' => 'lock-fill', 'text' => 'Cerrada'],
                            ];
                            $estado = $estadoBadge[$aud['estado']] ?? ['class' => 'secondary', 'icon' => 'question-circle', 'text' => ucfirst($aud['estado'])];

                            // Porcentaje de cumplimiento (calidad de la auditoría)
                            $porcentajeCumplimiento = $aud['porcentaje_cumplimiento'] ?? 0;

                            // Determinar color del badge según el porcentaje
                            $badgeClass = 'secondary';
                            if ($porcentajeCumplimiento >= 90) {
                                $badgeClass = 'success';
                            } elseif ($porcentajeCumplimiento >= 70) {
                                $badgeClass = 'info';
                            } elseif ($porcentajeCumplimiento >= 50) {
                                $badgeClass = 'warning';
                            } else {
                                $badgeClass = 'danger';
                            }

                            // Progreso de revisión
                            $totalItems = $aud['total_items'] ?? 0;
                            $itemsCalificados = $aud['items_calificados'] ?? 0;
                            $porcentajeProgreso = $totalItems > 0 ? round(($itemsCalificados / $totalItems) * 100) : 0;
                            ?>
                            <tr>
                                <td><strong>#<?= esc($aud['id_auditoria']) ?></strong></td>
                                <td>
                                    <strong><?= esc($aud['proveedor_nombre']) ?></strong>
                                    <br>
                                    <small class="text-muted">NIT: <?= esc($aud['proveedor_nit']) ?></small>
                                </td>
                                <td><?= esc($aud['consultor_nombre']) ?></td>
                                <td>
                                    <?= esc($aud['usuario_responsable_nombre'] ?? 'N/A') ?>
                                    <br>
                                    <small class="text-muted"><?= esc($aud['usuario_responsable_email'] ?? '') ?></small>
                                </td>
                                <td data-order="<?= !empty($aud['created_at']) ? strtotime($aud['created_at']) : 0 ?>">
                                    <?php
                                    if (!empty($aud['created_at'])) {
                                        $fecha = new DateTime($aud['created_at']);
                                        echo $fecha->format('d/m/Y');
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
                                <td data-order="<?= $porcentajeCumplimiento ?>">
                                    <?php if ($aud['estado'] === 'cerrada'): ?>
                                        <!-- Auditoría cerrada: mostrar porcentaje de cumplimiento -->
                                        <span class="badge bg-<?= $badgeClass ?>" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                            <?= number_format($porcentajeCumplimiento, 1) ?>%
                                        </span>
                                        <br>
                                        <small class="text-muted"><?= $itemsCalificados ?>/<?= $totalItems ?> items</small>
                                    <?php else: ?>
                                        <!-- Auditoría en progreso: mostrar barra de progreso -->
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar bg-<?= $porcentajeProgreso === 100 ? 'success' : 'primary' ?>"
                                                 role="progressbar"
                                                 style="width: <?= $porcentajeProgreso ?>%;"
                                                 aria-valuenow="<?= $porcentajeProgreso ?>"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                                <strong><?= $porcentajeProgreso ?>%</strong>
                                            </div>
                                        </div>
                                        <small class="text-muted"><?= $itemsCalificados ?>/<?= $totalItems ?> items revisados</small>
                                    <?php endif; ?>
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
            Total: <?= count($auditorias) ?> auditoría(s) en el sistema
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
        var table = $('#tablaRevision').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
            order: [[4, 'desc']], // Ordenar por fecha de creación descendente
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"B>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Auditorias_Revision_Consultores',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
            responsive: true,
            initComplete: function() {
                // Agregar filtros en cada columna del footer
                this.api().columns().every(function() {
                    var column = this;
                    var title = $(column.header()).text();

                    $('<input type="text" class="form-control form-control-sm" placeholder="Filtrar '+title+'" />')
                        .appendTo($(column.footer()).empty())
                        .on('keyup change clear', function() {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                });
            }
        });
    });
    </script>
<?php endif; ?>

<?= $this->endSection() ?>
