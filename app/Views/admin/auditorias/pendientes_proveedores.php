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
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Proveedor</th>
                            <th>Consultor</th>
                            <th>Usuario Responsable</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
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
                                    <?= esc($auditoria['usuario_responsable_nombre'] ?? 'N/A') ?>
                                    <br>
                                    <small class="text-muted"><?= esc($auditoria['usuario_responsable_email'] ?? '') ?></small>
                                </td>
                                <td>
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
                                <td>
                                    <a href="<?= site_url('proveedor/auditoria/' . $auditoria['id_auditoria']) ?>"
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
            Total: <?= count($auditorias) ?> auditoría(s) pendiente(s) de diligenciamiento por proveedores
        </small>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
