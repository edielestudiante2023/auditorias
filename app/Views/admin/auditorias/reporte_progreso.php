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
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-success" onclick="exportarExcel()">
                    <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="tablaReporte">
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
                        <td class="text-center" style="min-width: 150px;">
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
                        <td>
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

<script>
function exportarExcel() {
    // Exportar tabla a Excel
    const table = document.getElementById('tablaReporte');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Reporte Progreso"});
    XLSX.writeFile(wb, 'reporte_progreso_auditorias_<?= date('Y-m-d') ?>.xlsx');
}
</script>

<!-- SheetJS para exportar Excel -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

<?= $this->endSection() ?>
