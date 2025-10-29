<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('/consultor/auditorias') ?>">Auditorías</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/consultor/auditoria/' . $auditoria['id_auditoria']) ?>">Auditoría #<?= $auditoria['id_auditoria'] ?></a></li>
            <li class="breadcrumb-item active">Bitácora</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="bi bi-journal-text"></i> Bitácora de Auditoría
            </h1>
            <p class="text-muted mb-0">
                Auditoría #<?= $auditoria['id_auditoria'] ?> -
                <span class="badge bg-<?= $auditoria['estado'] === 'cerrada' ? 'success' : 'warning' ?>">
                    <?= ucfirst($auditoria['estado']) ?>
                </span>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?= base_url('/consultor/auditoria/' . $auditoria['id_auditoria']) ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Auditoría
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-bar-chart"></i> Resumen de Actividad
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h3 class="text-primary"><?= $total_acciones ?></h3>
                            <p class="text-muted mb-0">Acciones Totales</p>
                        </div>
                        <div class="col-md-9">
                            <h6 class="text-muted mb-2">Acciones por Tipo:</h6>
                            <div class="row">
                                <?php foreach ($estadisticas as $stat): ?>
                                    <div class="col-md-4 mb-2">
                                        <div class="d-flex align-items-center">
                                            <?php
                                            // Iconos según el tipo de acción
                                            $iconos = [
                                                'comentario_global_guardado' => 'bi-chat-left-text text-info',
                                                'comentario_cliente_guardado' => 'bi-chat-left-dots text-info',
                                                'evidencia_global_subida' => 'bi-file-earmark-arrow-up text-success',
                                                'evidencia_cliente_subida' => 'bi-file-earmark-arrow-up-fill text-success',
                                                'evidencia_global_eliminada' => 'bi-file-earmark-x text-danger',
                                                'evidencia_cliente_eliminada' => 'bi-file-earmark-x-fill text-danger',
                                                'auditoria_cerrada' => 'bi-check-circle text-primary',
                                                'clientes_asignados' => 'bi-people text-warning',
                                            ];
                                            $icono = $iconos[$stat['accion']] ?? 'bi-circle text-secondary';
                                            ?>
                                            <i class="bi <?= $icono ?> me-2"></i>
                                            <span class="small"><?= str_replace('_', ' ', ucfirst($stat['accion'])) ?>: <strong><?= $stat['total'] ?></strong></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de bitácora -->
    <div class="card">
        <div class="card-header bg-light">
            <i class="bi bi-list-ul"></i> Registro de Actividades (<?= $total_acciones ?> total)
        </div>
        <div class="card-body p-0">
            <?php if (empty($logs)): ?>
                <div class="alert alert-info m-3">
                    <i class="bi bi-info-circle"></i> No hay actividades registradas aún en esta auditoría.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="140">Fecha/Hora</th>
                                <th width="150">Usuario</th>
                                <th width="200">Acción</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="small text-nowrap">
                                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td class="small">
                                        <?php if (!empty($log['usuario_nombre'])): ?>
                                            <i class="bi bi-person-circle"></i> <?= esc($log['usuario_nombre']) ?>
                                        <?php else: ?>
                                            <i class="bi bi-robot text-muted"></i> Sistema
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Badge y texto según acción
                                        $badges = [
                                            'comentario_global_guardado' => '<span class="badge bg-info"><i class="bi bi-chat-left-text"></i> Comentario Global</span>',
                                            'comentario_cliente_guardado' => '<span class="badge bg-info"><i class="bi bi-chat-left-dots"></i> Comentario Cliente</span>',
                                            'evidencia_global_subida' => '<span class="badge bg-success"><i class="bi bi-upload"></i> Evidencia Global</span>',
                                            'evidencia_cliente_subida' => '<span class="badge bg-success"><i class="bi bi-upload"></i> Evidencia Cliente</span>',
                                            'evidencia_global_eliminada' => '<span class="badge bg-danger"><i class="bi bi-trash"></i> Evidencia Eliminada</span>',
                                            'evidencia_cliente_eliminada' => '<span class="badge bg-danger"><i class="bi bi-trash"></i> Evidencia Eliminada</span>',
                                            'auditoria_cerrada' => '<span class="badge bg-primary"><i class="bi bi-check-circle"></i> Auditoría Cerrada</span>',
                                            'clientes_asignados' => '<span class="badge bg-warning"><i class="bi bi-people"></i> Clientes Asignados</span>',
                                        ];
                                        echo $badges[$log['accion']] ?? '<span class="badge bg-secondary">' . esc($log['accion']) . '</span>';
                                        ?>
                                    </td>
                                    <td class="small">
                                        <?php
                                        // Detalles según acción
                                        if (!empty($log['detalle_json'])):
                                            $detalle = $log['detalle_json'];

                                            switch ($log['accion']) {
                                                case 'comentario_global_guardado':
                                                    echo '<strong>' . esc($detalle['item_titulo'] ?? 'Ítem') . '</strong>';
                                                    break;

                                                case 'comentario_cliente_guardado':
                                                    echo '<strong>' . esc($detalle['item_titulo'] ?? 'Ítem') . '</strong> - Cliente: ' . esc($detalle['cliente_nombre'] ?? 'N/A');
                                                    break;

                                                case 'evidencia_global_subida':
                                                    echo '<i class="bi bi-file-earmark"></i> ' . esc($detalle['nombre_archivo'] ?? 'archivo') . ' (' . ($detalle['tamano_mb'] ?? '0') . ' MB)';
                                                    break;

                                                case 'evidencia_cliente_subida':
                                                    echo '<i class="bi bi-file-earmark"></i> ' . esc($detalle['nombre_archivo'] ?? 'archivo') . ' (' . ($detalle['tamano_mb'] ?? '0') . ' MB) - Cliente: ' . esc($detalle['cliente_nombre'] ?? 'N/A');
                                                    break;

                                                case 'evidencia_global_eliminada':
                                                    echo '<i class="bi bi-file-earmark-x"></i> ' . esc($detalle['nombre_archivo'] ?? 'archivo');
                                                    break;

                                                case 'evidencia_cliente_eliminada':
                                                    echo '<i class="bi bi-file-earmark-x"></i> ' . esc($detalle['nombre_archivo'] ?? 'archivo') . ' - Cliente: ' . esc($detalle['cliente_nombre'] ?? 'N/A');
                                                    break;

                                                case 'auditoria_cerrada':
                                                    echo 'Porcentaje global: <strong>' . ($detalle['porcentaje_global'] ?? '0') . '%</strong> - ' . ($detalle['cantidad_clientes'] ?? '0') . ' cliente(s) evaluados';
                                                    break;

                                                case 'clientes_asignados':
                                                    echo '<strong>' . ($detalle['cantidad_clientes'] ?? '0') . '</strong> cliente(s) asignados';
                                                    break;

                                                default:
                                                    echo '<pre class="mb-0 small">' . esc(json_encode($detalle, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
                                            }
                                        else:
                                            echo '<span class="text-muted">Sin detalles</span>';
                                        endif;
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($pager->getPageCount() > 1): ?>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Mostrando <?= ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1 ?>
                                -
                                <?= min($pager->getCurrentPage() * $pager->getPerPage(), $total_acciones) ?>
                                de <?= $total_acciones ?> registros
                            </small>
                            <?= $pager->links('default', 'bootstrap_pagination') ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="card mt-3">
        <div class="card-body">
            <h6 class="card-title">Leyenda de Acciones:</h6>
            <div class="row small">
                <div class="col-md-3">
                    <i class="bi bi-chat-left-text text-info"></i> Comentario global guardado
                </div>
                <div class="col-md-3">
                    <i class="bi bi-chat-left-dots text-info"></i> Comentario por cliente guardado
                </div>
                <div class="col-md-3">
                    <i class="bi bi-upload text-success"></i> Evidencia subida
                </div>
                <div class="col-md-3">
                    <i class="bi bi-trash text-danger"></i> Evidencia eliminada
                </div>
            </div>
            <div class="row small mt-2">
                <div class="col-md-3">
                    <i class="bi bi-check-circle text-primary"></i> Auditoría cerrada
                </div>
                <div class="col-md-3">
                    <i class="bi bi-people text-warning"></i> Clientes asignados
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
