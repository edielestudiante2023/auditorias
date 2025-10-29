<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-check"></i> Mis Auditorías</h2>
</div>

<?= view('partials/flash') ?>

<?php if (empty($auditorias)): ?>
    <div class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
        <p class="text-muted mt-3 fs-5">No tienes auditorías asignadas</p>
        <p class="text-muted">Cuando un consultor te asigne una auditoría, aparecerá aquí</p>
    </div>
<?php else: ?>
    <div class="row g-4 mb-4">
        <?php foreach ($auditorias as $auditoria): ?>
            <?php
            $progreso = $auditoria['progreso'] ?? 0;
            $isCompleted = $progreso >= 100;
            $estadoActual = $auditoria['estado'] ?? 'en_proveedor';

            $estadoBadge = [
                'en_proveedor' => 'warning',
                'en_revision_consultor' => 'info',
                'cerrada' => 'success',
            ];
            $estadoTexto = [
                'en_proveedor' => 'Pendiente',
                'en_revision_consultor' => 'En revisión',
                'cerrada' => 'Cerrada',
            ];
            $badge = $estadoBadge[$estadoActual] ?? 'secondary';
            $texto = $estadoTexto[$estadoActual] ?? $estadoActual;
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 <?= $isCompleted ? 'border-success' : '' ?>">
                    <div class="card-header <?= $isCompleted ? 'bg-success text-white' : 'bg-warning' ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-building"></i>
                                <?= esc($auditoria['codigo_formato'] ?? 'Auditoría #' . $auditoria['id_auditoria']) ?>
                            </h6>
                            <span class="badge bg-<?= $badge ?>"><?= $texto ?></span>
                        </div>
                    </div>

                    <div class="card-body">
                        <?php if (!empty($auditoria['cliente_nombre'])): ?>
                            <div class="mb-2">
                                <small class="text-muted">Cliente:</small>
                                <div><strong><?= esc($auditoria['cliente_nombre']) ?></strong></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($auditoria['servicio_nombre'])): ?>
                            <div class="mb-2">
                                <small class="text-muted">Servicio:</small>
                                <div><?= esc($auditoria['servicio_nombre']) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($auditoria['fecha_programada'])): ?>
                            <div class="mb-3">
                                <small class="text-muted">Fecha programada:</small>
                                <div><i class="bi bi-calendar"></i> <?= date('d/m/Y', strtotime($auditoria['fecha_programada'])) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Progreso:</small>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar <?= $isCompleted ? 'bg-success' : 'bg-primary' ?>"
                                     role="progressbar"
                                     style="width: <?= $progreso ?>%;"
                                     aria-valuenow="<?= $progreso ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <?= number_format($progreso, 0) ?>%
                                </div>
                            </div>
                            <?php if (!$isCompleted && $estadoActual == 'en_proveedor'): ?>
                                <small class="text-muted mt-1 d-block">
                                    <i class="bi bi-info-circle"></i> Complete todos los ítems para enviar a revisión
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent">
                        <?php if ($estadoActual === 'en_revision_consultor' || $estadoActual === 'cerrada'): ?>
                            <a href="<?= site_url('proveedor/auditoria/' . $auditoria['id_auditoria']) ?>"
                               class="btn btn-outline-secondary w-100">
                                <i class="bi bi-eye"></i> Ver auditoría
                            </a>
                        <?php else: ?>
                            <a href="<?= site_url('proveedor/auditoria/' . $auditoria['id_auditoria']) ?>"
                               class="btn btn-warning w-100">
                                <i class="bi bi-pencil-square"></i>
                                <?= $progreso > 0 ? 'Continuar' : 'Iniciar' ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Resumen de Estadísticas -->
    <div class="card border-warning shadow-sm">
        <div class="card-body">
            <h6 class="card-title text-muted mb-3">
                <i class="bi bi-bar-chart-fill"></i> Resumen de Auditorías
            </h6>
            <div class="row text-center">
                <?php
                $total = count($auditorias);
                $completadas = count(array_filter($auditorias, fn($a) => ($a['progreso'] ?? 0) >= 100));
                $enProgreso = count(array_filter($auditorias, fn($a) => ($a['progreso'] ?? 0) < 100 && ($a['progreso'] ?? 0) > 0));
                $sinIniciar = count(array_filter($auditorias, fn($a) => ($a['progreso'] ?? 0) == 0));
                $promedio = $total > 0 ? array_sum(array_column($auditorias, 'progreso')) / $total : 0;
                ?>
                <div class="col-6 col-md-3 border-end">
                    <h3 class="text-primary mb-0"><?= $total ?></h3>
                    <small class="text-muted">Total</small>
                </div>
                <div class="col-6 col-md-3 border-end">
                    <h3 class="text-success mb-0"><?= $completadas ?></h3>
                    <small class="text-muted">Completadas</small>
                </div>
                <div class="col-6 col-md-3 border-end">
                    <h3 class="text-warning mb-0"><?= $enProgreso ?></h3>
                    <small class="text-muted">En Progreso</small>
                </div>
                <div class="col-6 col-md-3">
                    <h3 class="<?= $promedio >= 70 ? 'text-success' : ($promedio >= 40 ? 'text-warning' : 'text-danger') ?> mb-0">
                        <?= number_format($promedio, 0) ?>%
                    </h3>
                    <small class="text-muted">Promedio</small>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
