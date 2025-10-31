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
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Proveedor</th>
                            <th>Consultor</th>
                            <th>Usuario Responsable</th>
                            <th>Fecha Creación</th>
                            <th>Estado</th>
                            <th>Calificación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
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

                            // Calcular porcentaje de progreso de calificación
                            $totalItems = $aud['total_items'] ?? 0;
                            $itemsCalificados = $aud['items_calificados'] ?? 0;
                            $porcentajeCalificacion = $totalItems > 0 ? round(($itemsCalificados / $totalItems) * 100) : 0;
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
                                <td>
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
                                <td>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-<?= $porcentajeCalificacion === 100 ? 'success' : 'primary' ?>"
                                             role="progressbar"
                                             style="width: <?= $porcentajeCalificacion ?>%;"
                                             aria-valuenow="<?= $porcentajeCalificacion ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            <strong><?= $porcentajeCalificacion ?>%</strong>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= $itemsCalificados ?>/<?= $totalItems ?> items</small>
                                </td>
                                <td>
                                    <a href="<?= site_url('consultor/auditoria/' . $aud['id_auditoria']) ?>"
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
            Total: <?= count($auditorias) ?> auditoría(s) en el sistema
        </small>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
