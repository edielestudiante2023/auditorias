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
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Proveedor</th>
                            <th>Consultor</th>
                            <th>Usuario Responsable</th>
                            <th>Fecha Cierre</th>
                            <th>% Cumplimiento</th>
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
                                <td><?= esc($auditoria['consultor_nombre']) ?></td>
                                <td>
                                    <?= esc($auditoria['usuario_responsable_nombre'] ?? 'N/A') ?>
                                    <br>
                                    <small class="text-muted"><?= esc($auditoria['usuario_responsable_email'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($auditoria['updated_at'])) {
                                        $fecha = new DateTime($auditoria['updated_at']);
                                        echo $fecha->format('d/m/Y H:i');
                                    } else {
                                        echo 'N/A';
                                    }
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
                                    <span class="badge bg-success">
                                        <i class="bi bi-lock-fill"></i> Cerrada
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= site_url('consultor/auditoria/' . $auditoria['id_auditoria']) ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       target="_blank"
                                       title="Ver detalles">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
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
            Total: <?= count($auditorias) ?> auditoría(s) completada(s) por proveedores
        </small>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
