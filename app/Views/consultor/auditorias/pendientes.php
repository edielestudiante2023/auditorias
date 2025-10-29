<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clock-history"></i> Pendientes de Revisión</h2>
    <a href="<?= site_url('consultor/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<?php if (empty($auditorias)): ?>
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
            <h5 class="mt-3 text-muted">No hay auditorías pendientes de revisión</h5>
            <p class="text-muted">Todas las auditorías han sido revisadas o aún están con los proveedores.</p>
            <a href="<?= site_url('consultor/auditorias') ?>" class="btn btn-success mt-3">
                <i class="bi bi-file-earmark-plus"></i> Ver Todas las Auditorías
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Estas auditorías han sido finalizadas por los proveedores y están esperando tu calificación.
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Proveedor</th>
                            <th>Fecha Finalización</th>
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
                                    <br>
                                    <small class="text-muted">
                                        <?php
                                        $now = new DateTime();
                                        $diff = $now->diff($fecha);
                                        if ($diff->days == 0) {
                                            echo 'Hoy';
                                        } elseif ($diff->days == 1) {
                                            echo 'Ayer';
                                        } else {
                                            echo 'Hace ' . $diff->days . ' días';
                                        }
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-warning">
                                        <i class="bi bi-clock-fill"></i> En revisión
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= site_url('consultor/auditoria/' . $auditoria['id_auditoria']) ?>"
                                       class="btn btn-sm btn-warning"
                                       target="_blank">
                                        <i class="bi bi-pencil-square"></i> Calificar
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
            Total: <?= count($auditorias) ?> auditoría(s) pendiente(s) de revisión
        </small>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
