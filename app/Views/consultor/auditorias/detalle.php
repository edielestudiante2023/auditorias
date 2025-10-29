<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Incluir componente Toast -->
<?= $this->include('components/toast') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-clipboard-check"></i> Revisión de Auditoría</h2>
        <p class="text-muted mb-0">
            <?= esc($auditoria['codigo_formato'] ?? 'Auditoría #' . $auditoria['id_auditoria']) ?>
            • Estado:
            <?php
            $estadoTexto = [
                'borrador' => 'Borrador',
                'en_proveedor' => 'En proveedor',
                'en_revision_consultor' => 'En revisión',
                'cerrada' => 'Cerrada',
            ];
            echo $estadoTexto[$auditoria['estado']] ?? $auditoria['estado'];
            ?>
        </p>
    </div>
    <div>
        <a href="<?= site_url('consultor/auditoria/' . $auditoria['id_auditoria'] . '/asignar-clientes') ?>" class="btn btn-outline-primary me-2">
            <i class="bi bi-building"></i> Asignar Clientes (<?= count($clientes) ?>)
        </a>
        <a href="<?= site_url('consultor/auditorias') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Indicador de Progreso -->
<?php
// Calcular progreso de calificación
$totalItems = 0;
$itemsCalificados = 0;
$debugInfo = []; // Para debug

foreach ($items as $item) {
    if ($item['alcance'] === 'global') {
        $totalItems++;
        // Un ítem está calificado solo si tiene una calificación diferente a 'sin_revision' o NULL
        $calificacion = $item['calificacion_consultor'] ?? null;
        $isCalificado = !empty($calificacion) && $calificacion !== 'sin_revision';
        if ($isCalificado) {
            $itemsCalificados++;
        }
        $debugInfo[] = [
            'tipo' => 'global',
            'id' => $item['id_auditoria_item'],
            'titulo' => $item['titulo'],
            'calificado' => $isCalificado ? 'SI' : 'NO',
            'calificacion' => $calificacion ?? 'null'
        ];
    } else {
        // Para por_cliente: cada cliente cuenta como un ítem
        foreach ($clientes as $cliente) {
            $totalItems++;
            $isCalificado = false;
            $calificacionCliente = null;
            // Verificar si este cliente tiene calificación
            foreach ($item['items_cliente'] as $ic) {
                if ($ic['id_cliente'] == $cliente['id_cliente']) {
                    $calificacionCliente = $ic['calificacion_ajustada'] ?? null;
                    // Solo está calificado si tiene una calificación diferente a 'sin_revision' o NULL
                    if (!empty($calificacionCliente) && $calificacionCliente !== 'sin_revision') {
                        $itemsCalificados++;
                        $isCalificado = true;
                    }
                    break;
                }
            }
            $debugInfo[] = [
                'tipo' => 'por_cliente',
                'id' => $item['id_auditoria_item'],
                'titulo' => $item['titulo'],
                'cliente' => $cliente['razon_social'],
                'calificado' => $isCalificado ? 'SI' : 'NO',
                'calificacion' => $calificacionCliente ?? 'null'
            ];
        }
    }
}

$porcentajeProgreso = $totalItems > 0 ? round(($itemsCalificados / $totalItems) * 100) : 0;
$todoCalificado = $itemsCalificados === $totalItems && $totalItems > 0;
?>

<div class="card mb-4 <?= $todoCalificado ? 'border-success' : 'border-warning' ?> sticky-top" style="top: 70px; z-index: 1020; background-color: white;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">
                <i class="bi bi-clipboard-data"></i> Progreso de Calificación
            </h6>
            <span class="badge <?= $todoCalificado ? 'bg-success' : 'bg-warning' ?>">
                <?= $itemsCalificados ?>/<?= $totalItems ?> ítems calificados
            </span>
        </div>
        <div class="progress" style="height: 25px;">
            <div class="progress-bar <?= $todoCalificado ? 'bg-success' : 'bg-warning' ?>"
                 role="progressbar"
                 style="width: <?= $porcentajeProgreso ?>%;"
                 aria-valuenow="<?= $porcentajeProgreso ?>"
                 aria-valuemin="0"
                 aria-valuemax="100">
                <?= $porcentajeProgreso ?>%
            </div>
        </div>
        <?php if ($todoCalificado): ?>
            <small class="text-success mt-2 d-block">
                <i class="bi bi-check-circle-fill"></i> Todos los ítems han sido calificados. Puede cerrar la auditoría.
            </small>
        <?php else: ?>
            <small class="text-muted mt-2 d-block">
                <i class="bi bi-info-circle"></i> Faltan <?= $totalItems - $itemsCalificados ?> ítem(s) por calificar.
            </small>
        <?php endif; ?>

        <!-- DEBUG: Mostrar información de calificación -->
        <details class="mt-3">
            <summary class="text-primary" style="cursor: pointer;">
                <small><i class="bi bi-bug"></i> Ver detalle de calificación (debug)</small>
            </summary>
            <div class="mt-2 p-2 bg-light rounded" style="max-height: 300px; overflow-y: auto;">
                <small>
                    <strong>Total ítems:</strong> <?= $totalItems ?><br>
                    <strong>Ítems calificados:</strong> <?= $itemsCalificados ?><br>
                    <strong>Porcentaje:</strong> <?= $porcentajeProgreso ?>%<br>
                    <hr>
                    <?php foreach ($debugInfo as $idx => $info): ?>
                        <div class="mb-1 <?= $info['calificado'] === 'SI' ? 'text-success' : 'text-danger' ?>">
                            <?= ($idx + 1) ?>. [<?= $info['tipo'] ?>] <?= esc($info['titulo']) ?>
                            <?php if (isset($info['cliente'])): ?>
                                - <?= esc($info['cliente']) ?>
                            <?php endif; ?>
                            - <strong><?= $info['calificado'] ?></strong>
                            <?php if (isset($info['calificacion'])): ?>
                                (<?= $info['calificacion'] ?>)
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </small>
            </div>
        </details>
    </div>
</div>

<?= view('partials/flash') ?>

<!-- Clientes Asignados -->
<?php if (!empty($clientes)): ?>
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-building-check"></i> Clientes Asignados (<?= count($clientes) ?>)
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($clientes as $cliente): ?>
                    <div class="col-md-4 mb-2">
                        <div class="border rounded p-2">
                            <strong><?= esc($cliente['razon_social']) ?></strong>
                            <br><small class="text-muted">NIT: <?= esc($cliente['nit']) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        No hay clientes asignados a esta auditoría.
        <a href="<?= site_url('consultor/auditoria/' . $auditoria['id_auditoria'] . '/asignar-clientes') ?>">Asignar clientes ahora</a>
    </div>
<?php endif; ?>

<!-- Ítems de Auditoría - ACCORDION -->
<h5 class="mb-3"><i class="bi bi-list-check"></i> Ítems de Auditoría</h5>

<div class="accordion" id="accordionItems">
<?php foreach ($items as $index => $item): ?>
    <?php
    // Determinar si está calificado según alcance
    if ($item['alcance'] === 'global') {
        $calificacion = $item['calificacion_consultor'] ?? null;
        $itemCalificado = !empty($calificacion) && $calificacion !== 'sin_revision';
    } else {
        // Para por_cliente: verificar que todos los clientes tengan calificación
        $itemCalificado = false;
        if (!empty($item['items_cliente'])) {
            $itemCalificado = true;
            foreach ($item['items_cliente'] as $ic) {
                $calif = $ic['calificacion_ajustada'] ?? null;
                if (empty($calif) || $calif === 'sin_revision') {
                    $itemCalificado = false;
                    break;
                }
            }
        }
    }

    // El primer item pendiente debe estar expandido
    $isExpanded = false;
    if ($index === 0 && !$itemCalificado) {
        $isExpanded = true;
    } elseif ($index > 0) {
        $allPreviousComplete = true;
        for ($i = 0; $i < $index; $i++) {
            $prevItem = $items[$i];
            if ($prevItem['alcance'] === 'global') {
                $prevCal = $prevItem['calificacion_consultor'] ?? null;
                if (empty($prevCal) || $prevCal === 'sin_revision') {
                    $allPreviousComplete = false;
                    break;
                }
            } else {
                foreach ($prevItem['items_cliente'] as $ic) {
                    $prevCalif = $ic['calificacion_ajustada'] ?? null;
                    if (empty($prevCalif) || $prevCalif === 'sin_revision') {
                        $allPreviousComplete = false;
                        break 2;
                    }
                }
            }
        }
        $isExpanded = $allPreviousComplete && !$itemCalificado;
    }
    ?>
    <div class="accordion-item mb-2 <?= $itemCalificado ? 'border-success' : '' ?>" id="item-<?= $item['id_auditoria_item'] ?>" data-item-index="<?= $index ?>">
        <h2 class="accordion-header" id="heading-<?= $item['id_auditoria_item'] ?>">
            <button class="accordion-button <?= $isExpanded ? '' : 'collapsed' ?> <?= $itemCalificado ? 'bg-success bg-opacity-10' : '' ?>"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapse-<?= $item['id_auditoria_item'] ?>"
                    aria-expanded="<?= $isExpanded ? 'true' : 'false' ?>"
                    aria-controls="collapse-<?= $item['id_auditoria_item'] ?>">
                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                    <div>
                        <span class="badge bg-secondary me-2"><?= $index + 1 ?></span>
                        <span class="badge bg-secondary me-2"><?= esc($item['codigo_item']) ?></span>
                        <strong><?= esc($item['titulo']) ?></strong>
                        <?php if ($item['alcance'] === 'global'): ?>
                            <span class="badge bg-info ms-2">Global</span>
                        <?php else: ?>
                            <span class="badge bg-warning ms-2">Por Cliente</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($itemCalificado): ?>
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle-fill"></i> Calificado
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning">
                            <i class="bi bi-clock-fill"></i> Pendiente
                        </span>
                    <?php endif; ?>
                </div>
            </button>
        </h2>
        <div id="collapse-<?= $item['id_auditoria_item'] ?>"
             class="accordion-collapse collapse <?= $isExpanded ? 'show' : '' ?>"
             aria-labelledby="heading-<?= $item['id_auditoria_item'] ?>"
             data-bs-parent="#accordionItems">
            <div class="accordion-body" style="background-color: #d5f4e6;">
            <?php if (!empty($item['descripcion'])): ?>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle"></i>
                    <strong>Descripción:</strong> <?= esc($item['descripcion']) ?>
                </div>
            <?php endif; ?>

            <?php if ($item['alcance'] === 'global'): ?>
                <!-- ÍTEM GLOBAL -->
                <div class="alert alert-light mb-3">
                    <strong><i class="bi bi-chat-left-text"></i> Comentario del Proveedor:</strong>
                    <p class="mb-0 mt-2"><?= nl2br(esc($item['comentario_proveedor'] ?? 'Sin comentario')) ?></p>
                </div>

                <?php if (!empty($item['evidencias'])): ?>
                    <div class="mb-3">
                        <strong><i class="bi bi-paperclip"></i> Evidencias (<?= count($item['evidencias']) ?>):</strong>
                        <div class="list-group mt-2">
                            <?php foreach ($item['evidencias'] as $ev): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark text-primary me-2"></i>
                                        <div>
                                            <strong><?= esc($ev['nombre_archivo_original']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= number_format($ev['tamanio_bytes'] / 1024, 2) ?> KB</small>
                                        </div>
                                    </div>
                                    <a href="<?= site_url('consultor/evidencia/' . $ev['id_evidencia'] . '/ver') ?>"
                                       class="btn btn-sm btn-primary"
                                       target="_blank"
                                       title="Ver evidencia">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('consultor/auditoria/item/' . $item['id_auditoria_item'] . '/calificar-global') ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label"><strong>Calificación del Consultor <span class="text-danger">*</span></strong></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="calificacion_consultor" value="cumple"
                                   id="cumple<?= $item['id_auditoria_item'] ?>"
                                   <?= ($item['calificacion_consultor'] ?? '') === 'cumple' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-success" for="cumple<?= $item['id_auditoria_item'] ?>">
                                <i class="bi bi-check-circle"></i> Cumple
                            </label>

                            <input type="radio" class="btn-check" name="calificacion_consultor" value="parcial"
                                   id="parcial<?= $item['id_auditoria_item'] ?>"
                                   <?= ($item['calificacion_consultor'] ?? '') === 'parcial' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-warning" for="parcial<?= $item['id_auditoria_item'] ?>">
                                <i class="bi bi-dash-circle"></i> Parcial
                            </label>

                            <input type="radio" class="btn-check" name="calificacion_consultor" value="no_cumple"
                                   id="no<?= $item['id_auditoria_item'] ?>"
                                   <?= ($item['calificacion_consultor'] ?? '') === 'no_cumple' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-danger" for="no<?= $item['id_auditoria_item'] ?>">
                                <i class="bi bi-x-circle"></i> No Cumple
                            </label>

                            <input type="radio" class="btn-check" name="calificacion_consultor" value="no_aplica"
                                   id="na<?= $item['id_auditoria_item'] ?>"
                                   <?= ($item['calificacion_consultor'] ?? '') === 'no_aplica' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-secondary" for="na<?= $item['id_auditoria_item'] ?>">
                                <i class="bi bi-slash-circle"></i> No Aplica
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Comentario del Consultor</strong></label>
                        <textarea name="comentario_consultor" class="form-control" rows="2"
                                  placeholder="Agregue sus observaciones..."><?= esc($item['comentario_consultor'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Guardar Calificación
                    </button>
                </form>

            <?php else: ?>
                <!-- ÍTEM POR CLIENTE - TABS -->
                <?php if (empty($clientes)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No hay clientes asignados para calificar este ítem.
                    </div>
                <?php else: ?>
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <?php foreach ($clientes as $idx => $cliente): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $idx === 0 ? 'active' : '' ?>"
                                        id="tab-consultor-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                        data-bs-toggle="tab"
                                        data-bs-target="#content-consultor-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                        type="button"
                                        role="tab">
                                    <?php
                                    // Verificar si este cliente tiene calificación
                                    $clienteCalificado = false;
                                    foreach ($item['items_cliente'] as $ic) {
                                        if ($ic['id_cliente'] == $cliente['id_cliente']) {
                                            $calif = $ic['calificacion_ajustada'] ?? null;
                                            $clienteCalificado = !empty($calif) && $calif !== 'sin_revision';
                                            break;
                                        }
                                    }
                                    ?>
                                    <?= esc($cliente['razon_social']) ?>
                                    <?php if ($clienteCalificado): ?>
                                        <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                    <?php endif; ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content">
                        <?php foreach ($clientes as $idx => $cliente): ?>
                            <?php
                            // Buscar datos del item-cliente
                            $itemCliente = null;
                            foreach ($item['items_cliente'] as $ic) {
                                if ($ic['id_cliente'] == $cliente['id_cliente']) {
                                    $itemCliente = $ic;
                                    break;
                                }
                            }
                            ?>
                            <div class="tab-pane fade <?= $idx === 0 ? 'show active' : '' ?>"
                                 id="content-consultor-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                 role="tabpanel">

                                <div class="alert alert-secondary mb-3">
                                    <i class="bi bi-building"></i>
                                    <strong>Cliente:</strong> <?= esc($cliente['razon_social']) ?>
                                    (NIT: <?= esc($cliente['nit']) ?>)
                                </div>

                                <!-- Comentario del Proveedor para este cliente -->
                                <?php if ($itemCliente): ?>
                                    <div class="alert alert-light mb-3">
                                        <strong><i class="bi bi-chat-left-text"></i> Comentario del Proveedor:</strong>
                                        <p class="mb-0 mt-2"><?= nl2br(esc($itemCliente['comentario_proveedor_cliente'] ?? 'Sin comentario')) ?></p>
                                    </div>

                                    <!-- Evidencias del cliente -->
                                    <?php if (!empty($itemCliente['evidencias'])): ?>
                                        <div class="mb-3">
                                            <strong><i class="bi bi-paperclip"></i> Evidencias (<?= count($itemCliente['evidencias']) ?>):</strong>
                                            <div class="list-group mt-2">
                                                <?php foreach ($itemCliente['evidencias'] as $ev): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-file-earmark text-primary me-2"></i>
                                                            <div>
                                                                <strong><?= esc($ev['nombre_archivo_original']) ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?= number_format($ev['tamanio_bytes'] / 1024, 2) ?> KB</small>
                                                            </div>
                                                        </div>
                                                        <a href="<?= site_url('consultor/evidencia-cliente/' . $ev['id_evidencia_cliente'] . '/ver') ?>"
                                                           class="btn btn-sm btn-primary"
                                                           target="_blank"
                                                           title="Ver evidencia">
                                                            <i class="bi bi-eye"></i> Ver
                                                        </a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        El proveedor aún no ha diligenciado este ítem para este cliente.
                                    </div>
                                <?php endif; ?>

                                <!-- Formulario de calificación por cliente -->
                                <form method="post" action="<?= site_url('consultor/auditoria/item/' . $item['id_auditoria_item'] . '/calificar-por-cliente/' . $cliente['id_cliente']) ?>">
                                    <?= csrf_field() ?>

                                    <div class="mb-3">
                                        <label class="form-label"><strong>Calificación para <?= esc($cliente['razon_social']) ?> <span class="text-danger">*</span></strong></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="calificacion_ajustada" value="cumple"
                                                   id="cumple-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                                   <?= ($itemCliente['calificacion_ajustada'] ?? '') === 'cumple' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-success" for="cumple-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>">
                                                <i class="bi bi-check-circle"></i> Cumple
                                            </label>

                                            <input type="radio" class="btn-check" name="calificacion_ajustada" value="parcial"
                                                   id="parcial-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                                   <?= ($itemCliente['calificacion_ajustada'] ?? '') === 'parcial' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-warning" for="parcial-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>">
                                                <i class="bi bi-dash-circle"></i> Parcial
                                            </label>

                                            <input type="radio" class="btn-check" name="calificacion_ajustada" value="no_cumple"
                                                   id="no-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                                   <?= ($itemCliente['calificacion_ajustada'] ?? '') === 'no_cumple' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-danger" for="no-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>">
                                                <i class="bi bi-x-circle"></i> No Cumple
                                            </label>

                                            <input type="radio" class="btn-check" name="calificacion_ajustada" value="no_aplica"
                                                   id="na-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                                   <?= ($itemCliente['calificacion_ajustada'] ?? '') === 'no_aplica' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-secondary" for="na-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>">
                                                <i class="bi bi-slash-circle"></i> No Aplica
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label"><strong>Observaciones del Consultor</strong></label>
                                        <textarea name="comentario_cliente" class="form-control" rows="2"
                                                  placeholder="Agregue observaciones específicas para este cliente..."><?= esc($itemCliente['comentario_cliente'] ?? '') ?></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save"></i> Guardar Calificación para <?= esc($cliente['razon_social']) ?>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            </div><!-- /accordion-body -->
        </div><!-- /accordion-collapse -->
    </div><!-- /accordion-item -->
<?php endforeach; ?>
</div><!-- /accordion -->

<!-- Botón Cerrar Auditoría -->
<?php if ($auditoria['estado'] !== 'cerrada' && !empty($clientes)): ?>
    <div class="card <?= $todoCalificado ? 'border-success' : 'border-secondary' ?> shadow-sm mt-4">
        <div class="card-body text-center">
            <h5 class="card-title"><i class="bi bi-lock"></i> Cerrar Auditoría</h5>
            <p class="text-muted">
                Al cerrar la auditoría, se calcularán los porcentajes de cumplimiento. Los PDFs se generarán bajo demanda.
            </p>
            <?php if (!$todoCalificado): ?>
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle"></i>
                    Debe calificar todos los ítems antes de cerrar la auditoría.
                    Faltan <strong><?= $totalItems - $itemsCalificados ?></strong> ítem(s) por calificar.
                </div>
            <?php endif; ?>
            <form method="post" action="<?= site_url('consultor/auditoria/' . $auditoria['id_auditoria'] . '/cerrar') ?>"
                  onsubmit="return confirm('¿Está seguro de cerrar esta auditoría? Los porcentajes se calcularán automáticamente.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-lg" <?= !$todoCalificado ? 'disabled' : '' ?>>
                    <i class="bi bi-lock-fill"></i> Cerrar Auditoría
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- PDFs por Cliente (solo si está cerrada) -->
<?php if ($auditoria['estado'] === 'cerrada' && !empty($clientes)): ?>
    <div class="card border-success shadow-sm mt-4">
        <div class="card-header bg-success bg-opacity-10">
            <h5 class="card-title mb-0"><i class="bi bi-file-pdf"></i> Informes PDF por Cliente</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Genere y descargue los informes en PDF para cada cliente, o envíelos directamente por email.</p>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>NIT</th>
                            <th class="text-center">Cumplimiento</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <?php
                            // Obtener porcentaje del cliente
                            $porcentajeCliente = 0;
                            $db = \Config\Database::connect();
                            $resultado = $db->table('auditoria_clientes')
                                ->select('porcentaje_cumplimiento')
                                ->where('id_auditoria', $auditoria['id_auditoria'])
                                ->where('id_cliente', $cliente['id_cliente'])
                                ->get()
                                ->getRowArray();

                            if ($resultado) {
                                $porcentajeCliente = $resultado['porcentaje_cumplimiento'] ?? 0;
                            }
                            ?>
                            <tr>
                                <td><strong><?= esc($cliente['razon_social']) ?></strong></td>
                                <td><?= esc($cliente['nit']) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $porcentajeCliente >= 75 ? 'bg-success' : ($porcentajeCliente >= 50 ? 'bg-warning' : 'bg-danger') ?>">
                                        <?= number_format($porcentajeCliente, 2) ?>%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Descargar PDF -->
                                        <a href="<?= site_url('consultor/auditoria/' . $auditoria['id_auditoria'] . '/cliente/' . $cliente['id_cliente'] . '/descargar-pdf') ?>"
                                           class="btn btn-primary"
                                           title="Descargar PDF"
                                           target="_blank">
                                            <i class="bi bi-download"></i> Descargar
                                        </a>

                                        <!-- Enviar por Email -->
                                        <button type="button"
                                                class="btn btn-success"
                                                onclick="enviarPdfCliente(<?= $auditoria['id_auditoria'] ?>, <?= $cliente['id_cliente'] ?>, '<?= esc($cliente['razon_social']) ?>')"
                                                title="Enviar por email">
                                            <i class="bi bi-envelope"></i> Enviar Email
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-info-circle"></i>
                <strong>Nota:</strong> Los PDFs se generan bajo demanda. El primer acceso puede tardar unos segundos.
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar toast si hay mensajes flash
    <?php if (session()->getFlashdata('success')): ?>
        showToast('<?= esc(session()->getFlashdata('success')) ?>', 'success', 5000);
        // Auto-expand next item after save
        expandNextItem();
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        showToast('<?= esc(session()->getFlashdata('error')) ?>', 'error', 5000);
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
        showToast('<?= esc(session()->getFlashdata('warning')) ?>', 'warning', 5000);
    <?php endif; ?>

    // Add event listeners to all forms to save current item index
    const forms = document.querySelectorAll('form[method="post"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // No guardar para el formulario de cerrar auditoría
            if (!this.action.includes('/cerrar')) {
                // Find the accordion item containing this form
                const accordionItem = this.closest('.accordion-item');
                if (accordionItem) {
                    const itemIndex = accordionItem.getAttribute('data-item-index');
                    sessionStorage.setItem('lastSavedItemIndex', itemIndex);
                }
            }
        });
    });
});

/**
 * Expand next item after successful save
 */
function expandNextItem() {
    const lastSavedIndex = sessionStorage.getItem('lastSavedItemIndex');

    if (lastSavedIndex !== null) {
        const currentIndex = parseInt(lastSavedIndex);
        const nextIndex = currentIndex + 1;

        // Find next accordion item
        const nextItem = document.querySelector(`.accordion-item[data-item-index="${nextIndex}"]`);

        if (nextItem) {
            // Close current item
            const currentItem = document.querySelector(`.accordion-item[data-item-index="${currentIndex}"]`);
            if (currentItem) {
                const currentCollapse = currentItem.querySelector('.accordion-collapse');
                const bsCollapse = bootstrap.Collapse.getInstance(currentCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            }

            // Open next item
            const nextCollapse = nextItem.querySelector('.accordion-collapse');
            const nextBsCollapse = new bootstrap.Collapse(nextCollapse, {
                toggle: false
            });

            // Wait a bit for the current to close, then open next
            setTimeout(() => {
                nextBsCollapse.show();

                // Scroll to the next item smoothly
                setTimeout(() => {
                    nextItem.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 350);
            }, 350);
        } else {
            // No more items - scroll to cerrar button
            const cerrarCard = document.querySelector('.card.border-success, .card.border-secondary');
            if (cerrarCard) {
                setTimeout(() => {
                    cerrarCard.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 500);
            }
        }

        // Clear the saved index
        sessionStorage.removeItem('lastSavedItemIndex');
    }
}

/**
 * Enviar PDF por email a un cliente
 */
function enviarPdfCliente(idAuditoria, idCliente, nombreCliente) {
    if (!confirm(`¿Enviar el informe PDF de ${nombreCliente} por email?\n\nSe generará el PDF y se enviará al email de contacto del cliente.`)) {
        return;
    }

    // Crear formulario y enviarlo
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `<?= site_url('consultor/auditoria/') ?>${idAuditoria}/cliente/${idCliente}/enviar-pdf`;

    // Agregar CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '<?= csrf_token() ?>';
    csrfInput.value = '<?= csrf_hash() ?>';
    form.appendChild(csrfInput);

    document.body.appendChild(form);
    form.submit();
}
</script>

<?= $this->endSection() ?>
