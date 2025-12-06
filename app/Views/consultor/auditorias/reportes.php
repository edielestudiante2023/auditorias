<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-pdf"></i> Reportes de Auditorías</h2>
    <div class="d-flex align-items-center gap-2">
        <?= view('partials/filtro_anio', ['anio_actual' => $anio ?? date('Y'), 'url_base' => site_url('consultor/auditorias/reportes')]) ?>
        <a href="<?= site_url('consultor/dashboard') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?= view('partials/flash') ?>

<?php if (empty($auditorias)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">No hay auditorías cerradas</h5>
            <p class="text-muted">Aún no has cerrado ninguna auditoría.</p>
            <a href="<?= site_url('consultor/auditorias') ?>" class="btn btn-success mt-3">
                <i class="bi bi-file-earmark-plus"></i> Ver Auditorías Activas
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Proveedor</th>
                            <th>Fecha Cierre</th>
                            <th>% Cumplimiento</th>
                            <th>Clientes</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
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
                                <td>
                                    <?php
                                    $fecha = new DateTime($auditoria['updated_at']);
                                    echo $fecha->format('d/m/Y H:i');
                                    ?>
                                </td>
                                <td>
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
                                    <span class="badge bg-primary"><?= $auditoria['total_clientes'] ?> cliente(s)</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="bi bi-lock-fill"></i> Cerrada
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?= site_url('consultor/auditoria/' . $auditoria['id_auditoria']) ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           target="_blank"
                                           title="Ver detalles">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                        <?php if (!empty($auditoria['clientes'])): ?>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-danger dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="bi bi-file-pdf"></i> PDFs
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php foreach ($auditoria['clientes'] as $cliente): ?>
                                                        <li>
                                                            <a class="dropdown-item"
                                                               href="<?= site_url('files/pdf/' . $auditoria['id_auditoria'] . '/' . $cliente['id_cliente']) ?>"
                                                               target="_blank">
                                                                <i class="bi bi-file-pdf-fill text-danger"></i> <?= esc($cliente['razon_social']) ?>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
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
            Total: <?= count($auditorias) ?> auditoría(s) cerrada(s)
        </small>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
