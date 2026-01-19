<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
/* SweetAlert personalizado para navegación de clientes */
.swal-wide {
    max-width: 450px !important;
}
.swal2-actions {
    flex-wrap: wrap;
    gap: 8px;
}
.swal2-actions button {
    margin: 0 !important;
}
/* Barra de cliente actual fija */
#current-client-bar {
    display: none;
    transition: all 0.3s ease;
}
#current-client-bar.show {
    display: block;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-clipboard-check-fill"></i> Diligenciar Auditoría</h2>
        <p class="text-muted mb-0">
            <?= esc($auditoria['codigo_formato'] ?? 'Auditoría #' . $auditoria['id_auditoria']) ?>
        </p>
    </div>
    <a href="<?= site_url('proveedor/auditorias') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>
<?= $this->include('components/toast') ?>

<!-- Barra de progreso compacta y flotante -->
<?php
$pctGlobales = $progreso['globales_total'] > 0
    ? ($progreso['globales_completos'] / $progreso['globales_total']) * 100
    : 0;
$pctPorCliente = $progreso['por_cliente_total'] > 0
    ? ($progreso['por_cliente_completos'] / $progreso['por_cliente_total']) * 100
    : 0;
$todoCompleto = $progreso['porcentaje_total'] >= 100;
?>
<div id="progress-bar-compact" class="sticky-top" style="top: 56px; z-index: 1020;">
    <div class="bg-white border-bottom shadow-sm py-2 px-3">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center flex-grow-1 me-3">
                <span class="badge <?= $todoCompleto ? 'bg-success' : 'bg-primary' ?> me-2" style="min-width: 80px;">
                    <?= number_format($progreso['porcentaje_total'], 0) ?>%
                </span>
                <div class="progress flex-grow-1" style="height: 8px; max-width: 200px;">
                    <div class="progress-bar <?= $todoCompleto ? 'bg-success' : 'bg-primary' ?>"
                         role="progressbar"
                         style="width: <?= $progreso['porcentaje_total'] ?>%;">
                    </div>
                </div>
                <small class="ms-2 text-muted d-none d-md-inline">
                    <?= $progreso['total_completados'] ?>/<?= $progreso['total'] ?> ítems
                </small>
            </div>
            <?php if ($todoCompleto): ?>
                <span class="badge bg-success">
                    <i class="bi bi-check-circle-fill"></i> Listo para enviar
                </span>
            <?php endif; ?>
            <button class="btn btn-sm btn-link text-muted p-0 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#progress-details" aria-expanded="false">
                <i class="bi bi-chevron-down"></i>
            </button>
        </div>

        <!-- Panel expandible con detalles -->
        <div class="collapse" id="progress-details">
            <div class="pt-2 mt-2 border-top">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-globe text-primary me-1"></i>
                            <small class="text-muted">Globales: <?= $progreso['globales_completos'] ?>/<?= $progreso['globales_total'] ?></small>
                            <div class="progress ms-2 flex-grow-1" style="height: 4px;">
                                <div class="progress-bar bg-primary" style="width: <?= $pctGlobales ?>%;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-building text-warning me-1"></i>
                            <small class="text-muted">Por Cliente: <?= $progreso['por_cliente_completos'] ?>/<?= $progreso['por_cliente_total'] ?></small>
                            <div class="progress ms-2 flex-grow-1" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: <?= $pctPorCliente ?>%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($todoCompleto): ?>
                    <small class="text-success mt-2 d-block">
                        <i class="bi bi-check-circle-fill"></i> Todos los ítems están completos. Puede enviar a revisión.
                    </small>
                <?php else: ?>
                    <small class="text-muted mt-2 d-block">
                        <i class="bi bi-info-circle"></i> Faltan <?= $progreso['total'] - $progreso['total_completados'] ?> ítem(s) por completar.
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Barra de cliente actual (fija) -->
<div id="current-client-bar" class="sticky-top" style="top: 100px; z-index: 1019;">
    <div class="py-2 px-3 shadow-sm" id="current-client-inner" style="border-left: 4px solid #2196f3; background-color: #e3f2fd;">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="bi bi-building-fill me-2" id="current-client-icon" style="color: #1565c0; font-size: 1.2rem;"></i>
                <div>
                    <small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1;">Trabajando en:</small>
                    <strong id="current-client-name" style="color: #1565c0; font-size: 1rem;">Cliente</strong>
                </div>
            </div>
            <span class="badge" id="current-client-badge" style="background-color: #1565c0;">
                <i class="bi bi-person-workspace"></i> Cliente Activo
            </span>
        </div>
    </div>
</div>

<!-- Ítems de la Auditoría - ACCORDION -->
<div class="accordion" id="accordionItems">
<?php foreach ($items as $index => $item): ?>
    <?php
    // Determinar si el ítem está completo según su alcance
    if ($item['alcance'] === 'global') {
        $itemCompleto = !empty($item['comentario_proveedor']);
    } else {
        // Para ítems por cliente, verificar que TODOS los clientes tengan comentario
        $itemCompleto = false;
        if (!empty($item['items_cliente'])) {
            $itemCompleto = true;
            foreach ($item['items_cliente'] as $ic) {
                if (empty($ic['comentario_proveedor_cliente'])) {
                    $itemCompleto = false;
                    break;
                }
            }
        }
    }

    // El primer item pendiente debe estar expandido, o el primero si todos están completos
    $isExpanded = false;
    if ($index === 0 && !$itemCompleto) {
        $isExpanded = true;
    } elseif ($index > 0) {
        // Verificar si es el primer item pendiente
        $allPreviousComplete = true;
        for ($i = 0; $i < $index; $i++) {
            $prevItem = $items[$i];
            if ($prevItem['alcance'] === 'global') {
                if (empty($prevItem['comentario_proveedor'])) {
                    $allPreviousComplete = false;
                    break;
                }
            } else {
                foreach ($prevItem['items_cliente'] as $ic) {
                    if (empty($ic['comentario_proveedor_cliente'])) {
                        $allPreviousComplete = false;
                        break 2;
                    }
                }
            }
        }
        $isExpanded = $allPreviousComplete && !$itemCompleto;
    }
    ?>
    <div class="accordion-item mb-2 <?= $itemCompleto ? 'border-success' : '' ?>" id="item-<?= $item['id_auditoria_item'] ?>" data-item-index="<?= $index ?>">
        <h2 class="accordion-header" id="heading-<?= $item['id_auditoria_item'] ?>">
            <button class="accordion-button <?= $isExpanded ? '' : 'collapsed' ?> <?= $itemCompleto ? 'bg-success bg-opacity-10' : '' ?>"
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
                    <?php if ($itemCompleto): ?>
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle-fill"></i> Completado
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
            <div class="accordion-body" style="background-color: #fdebd0;">

            <?php if ($item['alcance'] === 'global'): ?>
                <!-- ÍTEM GLOBAL -->
                <?php if (!empty($item['descripcion'])): ?>
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i>
                        <strong>Descripción:</strong> <?= esc($item['descripcion']) ?>
                    </div>
                <?php endif; ?>

                <form class="form-item-ajax"
                      data-tipo="global"
                      data-id-auditoria="<?= $auditoria['id_auditoria'] ?>"
                      data-id-item="<?= $item['id_auditoria_item'] ?>"
                      enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-chat-left-text"></i> Comentario del Proveedor
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="comentario_proveedor"
                                  class="form-control autosave-textarea"
                                  rows="3"
                                  data-tipo="global"
                                  data-item-id="<?= $item['id_auditoria_item'] ?>"
                                  placeholder="Ingrese sus observaciones o comentarios para este ítem..."><?= esc($item['comentario_proveedor'] ?? '') ?></textarea>
                        <small class="text-muted">Este campo es requerido para marcar el ítem como completado</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-paperclip"></i> Cargar Evidencias (Opcional)
                        </label>
                        <input type="file"
                               name="evidencias[]"
                               class="form-control"
                               multiple
                               accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx">
                        <small class="text-muted">
                            Máximo 10MB por archivo. Formatos permitidos: PDF, Imágenes, Word, Excel
                        </small>
                    </div>

                    <div class="mb-3 evidencias-container" id="evidencias-<?= $item['id_auditoria_item'] ?>">
                        <?php if (!empty($item['evidencias'])): ?>
                            <label class="form-label">
                                <i class="bi bi-file-earmark-check"></i> Archivos Cargados (<span class="evidencias-count"><?= count($item['evidencias']) ?></span>)
                            </label>
                            <div class="list-group">
                                <?php foreach ($item['evidencias'] as $ev): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-file-earmark text-primary"></i>
                                            <strong><?= esc($ev['nombre_archivo_original']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?= number_format($ev['tamanio_bytes'] / 1024, 2) ?> KB
                                                • Subido: <?= date('d/m/Y H:i', strtotime($ev['created_at'])) ?>
                                            </small>
                                        </div>
                                        <?php if ($auditoria['estado'] == 'en_proveedor'): ?>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger btn-delete-evidencia"
                                                    data-url="<?= site_url('proveedor/auditoria/' . $auditoria['id_auditoria'] . '/evidencia/' . $ev['id_evidencia'] . '/eliminar') ?>"
                                                    data-nombre="<?= esc($ev['nombre_archivo_original']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="btn btn-success btn-guardar-item">
                        <i class="bi bi-save"></i> Guardar Ítem
                    </button>
                </form>

            <?php else: ?>
                <!-- ÍTEM POR CLIENTE - TABS (pestañas antes de la descripción) -->
                <?php if (empty($clientes)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No hay clientes asignados a esta auditoría para este ítem.
                    </div>
                <?php else: ?>
                    <?php
                    // Colores distintivos para cada cliente (basado en su ID para consistencia)
                    $coloresCliente = [
                        ['bg' => '#e3f2fd', 'border' => '#2196f3', 'text' => '#1565c0'], // Azul
                        ['bg' => '#fff3e0', 'border' => '#ff9800', 'text' => '#e65100'], // Naranja
                        ['bg' => '#f3e5f5', 'border' => '#9c27b0', 'text' => '#7b1fa2'], // Púrpura
                        ['bg' => '#e8f5e9', 'border' => '#4caf50', 'text' => '#2e7d32'], // Verde
                        ['bg' => '#fce4ec', 'border' => '#e91e63', 'text' => '#c2185b'], // Rosa
                        ['bg' => '#e0f7fa', 'border' => '#00bcd4', 'text' => '#00838f'], // Cian
                        ['bg' => '#fff8e1', 'border' => '#ffc107', 'text' => '#ff8f00'], // Ámbar
                        ['bg' => '#efebe9', 'border' => '#795548', 'text' => '#5d4037'], // Café
                    ];
                    ?>

                    <!-- Pestañas de clientes PRIMERO -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <?php foreach ($clientes as $idx => $cliente): ?>
                            <?php
                            // Asignar color basado en el ID del cliente (consistente en toda la auditoría)
                            $colorIdx = $cliente['id_cliente'] % count($coloresCliente);
                            $colorCliente = $coloresCliente[$colorIdx];

                            // Verificar si este cliente tiene comentario
                            $clienteCompleto = false;
                            foreach ($item['items_cliente'] as $ic) {
                                if ($ic['id_cliente'] == $cliente['id_cliente'] && !empty($ic['comentario_proveedor_cliente'])) {
                                    $clienteCompleto = true;
                                    break;
                                }
                            }
                            ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $idx === 0 ? 'active' : '' ?>"
                                        id="tab-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                        data-bs-toggle="tab"
                                        data-bs-target="#content-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                        type="button"
                                        role="tab"
                                        style="border-left: 4px solid <?= $colorCliente['border'] ?>; background-color: <?= $idx === 0 ? $colorCliente['bg'] : 'transparent' ?>;"
                                        data-color-bg="<?= $colorCliente['bg'] ?>"
                                        data-color-border="<?= $colorCliente['border'] ?>">
                                    <span style="color: <?= $colorCliente['text'] ?>; font-weight: 600;">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; vertical-align: middle;"></i>
                                        <?= esc($cliente['razon_social']) ?>
                                    </span>
                                    <?php if ($clienteCompleto): ?>
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
                            // Color para este cliente
                            $colorIdx = $cliente['id_cliente'] % count($coloresCliente);
                            $colorCliente = $coloresCliente[$colorIdx];
                            ?>
                            <div class="tab-pane fade <?= $idx === 0 ? 'show active' : '' ?>"
                                 id="content-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                 role="tabpanel"
                                 style="background-color: <?= $colorCliente['bg'] ?>; border-radius: 0 0 8px 8px; padding: 15px; border: 1px solid <?= $colorCliente['border'] ?>; border-top: none;"
                                 data-color-bg="<?= $colorCliente['bg'] ?>"
                                 data-color-border="<?= $colorCliente['border'] ?>">

                                <!-- Descripción del ítem (dentro de cada pestaña de cliente) -->
                                <?php if (!empty($item['descripcion'])): ?>
                                    <div class="alert alert-info mb-3">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Descripción:</strong> <?= esc($item['descripcion']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="alert mb-3 d-flex justify-content-between align-items-center"
                                     style="background-color: <?= $colorCliente['bg'] ?>; border-left: 4px solid <?= $colorCliente['border'] ?>;">
                                    <div>
                                        <i class="bi bi-building" style="color: <?= $colorCliente['text'] ?>;"></i>
                                        <strong style="color: <?= $colorCliente['text'] ?>;">Cliente:</strong>
                                        <span style="color: <?= $colorCliente['text'] ?>; font-weight: 600;"><?= esc($cliente['razon_social']) ?></span>
                                        (NIT: <?= esc($cliente['nit']) ?>)
                                    </div>
                                    <?php if ($itemCliente && !empty($itemCliente['comentario_proveedor_cliente'])): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle-fill"></i> Cliente Completo
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <form class="form-item-ajax"
                                      data-tipo="cliente"
                                      data-id-auditoria="<?= $auditoria['id_auditoria'] ?>"
                                      data-id-item="<?= $item['id_auditoria_item'] ?>"
                                      data-id-cliente="<?= $cliente['id_cliente'] ?>"
                                      enctype="multipart/form-data">
                                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-chat-left-text"></i> Comentario para este Cliente
                                            <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="comentario_proveedor_cliente"
                                                  class="form-control autosave-textarea"
                                                  rows="3"
                                                  data-tipo="cliente"
                                                  data-item-id="<?= $item['id_auditoria_item'] ?>"
                                                  data-cliente-id="<?= $cliente['id_cliente'] ?>"
                                                  placeholder="Ingrese las observaciones específicas para <?= esc($cliente['razon_social']) ?>..."><?= esc($itemCliente['comentario_proveedor_cliente'] ?? '') ?></textarea>
                                        <small class="text-muted">Este campo es requerido para este cliente</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-paperclip"></i> Cargar Evidencias para este Cliente (Opcional)
                                        </label>
                                        <input type="file"
                                               name="evidencias_cliente[]"
                                               class="form-control"
                                               multiple
                                               accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx">
                                        <small class="text-muted">
                                            Máximo 10MB por archivo. Formatos permitidos: PDF, Imágenes, Word, Excel
                                        </small>
                                    </div>

                                    <div class="mb-3 evidencias-container" id="evidencias-<?= $item['id_auditoria_item'] ?>-<?= $cliente['id_cliente'] ?>">
                                        <?php if ($itemCliente && !empty($itemCliente['evidencias'])): ?>
                                            <label class="form-label">
                                                <i class="bi bi-file-earmark-check"></i> Archivos Cargados (<span class="evidencias-count"><?= count($itemCliente['evidencias']) ?></span>)
                                            </label>
                                            <div class="list-group">
                                                <?php foreach ($itemCliente['evidencias'] as $ev): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="bi bi-file-earmark text-primary"></i>
                                                            <strong><?= esc($ev['nombre_archivo_original']) ?></strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?= number_format($ev['tamanio_bytes'] / 1024, 2) ?> KB
                                                                • Subido: <?= date('d/m/Y H:i', strtotime($ev['created_at'])) ?>
                                                            </small>
                                                        </div>
                                                        <?php if ($auditoria['estado'] == 'en_proveedor'): ?>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-danger btn-delete-evidencia-cliente"
                                                                    data-url="<?= site_url('proveedor/auditoria/' . $auditoria['id_auditoria'] . '/evidencia-cliente/' . $ev['id_evidencia_cliente'] . '/eliminar') ?>"
                                                                    data-nombre="<?= esc($ev['nombre_archivo_original']) ?>">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <button type="button" class="btn btn-success btn-guardar-item" data-cliente-nombre="<?= esc($cliente['razon_social']) ?>">
                                        <i class="bi bi-save"></i> Guardar para <?= esc($cliente['razon_social']) ?>
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

<!-- Botón Finalizar -->
<?php if ($auditoria['estado'] == 'en_proveedor'): ?>
    <div class="card border-primary shadow-sm">
        <div class="card-body text-center">
            <h5 class="card-title"><i class="bi bi-check-circle"></i> Finalizar Auditoría</h5>
            <p class="text-muted">
                Una vez finalizada, la auditoría será enviada al consultor para su revisión.
                <?php if ($progreso['porcentaje_total'] < 100): ?>
                    <br><strong class="text-warning">Recuerde completar todos los ítems antes de finalizar.</strong>
                    <br><small class="text-danger">Faltan <?= $progreso['total'] - $progreso['total_completados'] ?> ítems por completar</small>
                <?php endif; ?>
            </p>
            <form method="post"
                  action="<?= site_url('proveedor/auditoria/' . $auditoria['id_auditoria'] . '/finalizar') ?>"
                  onsubmit="return confirm('¿Está seguro de finalizar esta auditoría? Se enviará al consultor para revisión y no podrá realizar más cambios.');">
                <?= csrf_field() ?>
                <button type="submit"
                        class="btn btn-primary btn-lg"
                        <?= $progreso['porcentaje_total'] < 100 ? 'disabled' : '' ?>>
                    <i class="bi bi-send-check"></i> Finalizar y Enviar a Revisión
                </button>
                <?php if ($progreso['porcentaje_total'] < 100): ?>
                    <div class="mt-2">
                        <small class="text-muted">El botón se habilitará cuando complete el 100% de los ítems</small>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Esta auditoría ya fue enviada a revisión. No se pueden realizar más cambios.
    </div>
<?php endif; ?>


<!-- SweetAlert2 JS (cargado antes de los handlers que lo usan) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JavaScript for Accordion auto-navigation and Toast notifications -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show toast on successful save from flash messages
    <?php if (session()->getFlashdata('success')): ?>
        showToast('<?= addslashes(session()->getFlashdata('success')) ?>', 'success');

        // Auto-expand next item after save
        expandNextItem();
    <?php endif; ?>

    // Add event listeners to all forms to save current item index
    const forms = document.querySelectorAll('form[method="post"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // No guardar para el formulario de finalizar auditoría o eliminar
            if (!this.action.includes('/finalizar') && !this.action.includes('/eliminar')) {
                // Find the accordion item containing this form
                const accordionItem = this.closest('.accordion-item');
                if (accordionItem) {
                    const itemIndex = accordionItem.getAttribute('data-item-index');
                    sessionStorage.setItem('lastSavedItemIndex', itemIndex);
                }
            }
        });
    });

    // Colorear pestañas de clientes al cambiar y guardar pestaña activa
    document.querySelectorAll('.nav-tabs .nav-link[data-color-bg]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function() {
            // Quitar color de fondo de todas las pestañas hermanas
            const navTabs = this.closest('.nav-tabs');
            navTabs.querySelectorAll('.nav-link').forEach(t => {
                t.style.backgroundColor = 'transparent';
            });
            // Aplicar color a la pestaña activa
            this.style.backgroundColor = this.dataset.colorBg;

            // Guardar pestaña activa en sessionStorage para restaurar después de recargar
            const accordionItem = this.closest('.accordion-item');
            if (accordionItem) {
                const itemIndex = accordionItem.getAttribute('data-item-index');
                const tabId = this.getAttribute('id');
                sessionStorage.setItem(`activeTab_item_${itemIndex}`, tabId);
            }

            // Actualizar barra de cliente actual
            updateCurrentClientBar(this);
        });
    });

    // Detectar cuando se abre/cierra un accordion para mostrar/ocultar barra de cliente
    document.querySelectorAll('.accordion-collapse').forEach(collapse => {
        collapse.addEventListener('shown.bs.collapse', function() {
            // Verificar si el ítem expandido tiene pestañas de clientes
            const navTabs = this.querySelector('.nav-tabs');
            if (navTabs) {
                const activeTab = navTabs.querySelector('.nav-link.active');
                if (activeTab) {
                    updateCurrentClientBar(activeTab);
                }
            } else {
                // Es un ítem global, ocultar barra de cliente
                hideCurrentClientBar();
            }
        });

        collapse.addEventListener('hidden.bs.collapse', function() {
            // Verificar si hay otro accordion abierto con cliente
            checkVisibleClientBar();
        });
    });

    // Restaurar pestañas activas guardadas en sessionStorage
    restoreActiveTabs();

    // Verificar cliente activo inicial
    setTimeout(() => {
        checkVisibleClientBar();
    }, 100);
});

/**
 * Restaura las pestañas de clientes activas después de recargar la página
 */
function restoreActiveTabs() {
    // Buscar todos los ítems con pestañas
    document.querySelectorAll('.accordion-item').forEach(item => {
        const itemIndex = item.getAttribute('data-item-index');
        const savedTabId = sessionStorage.getItem(`activeTab_item_${itemIndex}`);

        if (savedTabId) {
            const savedTab = document.getElementById(savedTabId);
            if (savedTab) {
                // Activar la pestaña guardada
                const tab = new bootstrap.Tab(savedTab);
                tab.show();
            }
        }
    });
}

/**
 * Actualiza la barra de cliente actual con la información del cliente seleccionado
 */
function updateCurrentClientBar(tabElement) {
    const bar = document.getElementById('current-client-bar');
    const inner = document.getElementById('current-client-inner');
    const nameEl = document.getElementById('current-client-name');
    const iconEl = document.getElementById('current-client-icon');
    const badgeEl = document.getElementById('current-client-badge');

    // Obtener nombre del cliente desde el contenido del tab
    const clienteName = tabElement.querySelector('span')?.textContent.trim() || 'Cliente';
    const colorBg = tabElement.dataset.colorBg || '#e3f2fd';
    const colorBorder = tabElement.dataset.colorBorder || '#2196f3';

    // Calcular color de texto basado en el borde
    const colorText = colorBorder;

    // Actualizar contenido
    nameEl.textContent = clienteName;
    nameEl.style.color = colorText;
    iconEl.style.color = colorText;
    badgeEl.style.backgroundColor = colorBorder;

    // Actualizar estilos de la barra
    inner.style.backgroundColor = colorBg;
    inner.style.borderLeftColor = colorBorder;

    // Mostrar barra
    bar.classList.add('show');
}

/**
 * Oculta la barra de cliente actual
 */
function hideCurrentClientBar() {
    const bar = document.getElementById('current-client-bar');
    bar.classList.remove('show');
}

/**
 * Verifica si hay un accordion con cliente visible y actualiza la barra
 */
function checkVisibleClientBar() {
    // Buscar si hay un accordion abierto con pestañas de clientes
    const openCollapse = document.querySelector('.accordion-collapse.show');
    if (openCollapse) {
        const navTabs = openCollapse.querySelector('.nav-tabs');
        if (navTabs) {
            const activeTab = navTabs.querySelector('.nav-link.active');
            if (activeTab && activeTab.dataset.colorBg) {
                updateCurrentClientBar(activeTab);
                return;
            }
        }
    }
    // No hay cliente visible, ocultar barra
    hideCurrentClientBar();
}

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

                // Scroll suave al siguiente ítem con offset para el header sticky
                setTimeout(() => {
                    const headerOffset = 100; // Altura del header sticky + margen
                    const elementPosition = nextItem.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }, 350);
            }, 350);
        } else {
            // No more items - scroll to finalizar button
            const finalizarCard = document.querySelector('.card.border-primary');
            if (finalizarCard) {
                setTimeout(() => {
                    const headerOffset = 100;
                    const elementPosition = finalizarCard.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }, 500);
            }
        }

        // Clear the saved index
        sessionStorage.removeItem('lastSavedItemIndex');
    }
}


/**
 * Manejar eliminación de evidencias globales vía AJAX
 */
document.addEventListener('click', async function(e) {
    if (e.target.closest('.btn-delete-evidencia')) {
        const btn = e.target.closest('.btn-delete-evidencia');
        const url = btn.getAttribute('data-url');
        const nombre = btn.getAttribute('data-nombre');

        const result = await Swal.fire({
            title: '¿Eliminar archivo?',
            text: `Se eliminará "${nombre}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            // Deshabilitar botón y mostrar loading
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const formData = new FormData();
                formData.append('<?= csrf_token() ?>', FormManager ? FormManager.csrfHash : '<?= csrf_hash() ?>');

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                // Actualizar token CSRF
                if (data.csrf_token && data.csrf_hash && FormManager) {
                    FormManager.csrfToken = data.csrf_token;
                    FormManager.csrfHash = data.csrf_hash;
                }

                if (data.ok) {
                    showToast(data.message || 'Archivo eliminado', 'success');
                    // Eliminar el elemento de la lista visualmente
                    const listItem = btn.closest('.list-group-item');
                    if (listItem) {
                        listItem.remove();
                        // Actualizar contador de evidencias
                        const container = document.querySelector(`#evidencias-${data.id_item || ''}`);
                        if (container) {
                            const countSpan = container.querySelector('.evidencias-count');
                            if (countSpan) {
                                const newCount = parseInt(countSpan.textContent) - 1;
                                countSpan.textContent = newCount;
                                if (newCount === 0) {
                                    container.innerHTML = '';
                                }
                            }
                        }
                    }
                } else {
                    showToast(data.message || 'Error al eliminar', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            } catch (error) {
                console.error('Error al eliminar:', error);
                showToast('Error de conexión', 'error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    }
});

/**
 * Manejar eliminación de evidencias de cliente vía AJAX
 */
document.addEventListener('click', async function(e) {
    if (e.target.closest('.btn-delete-evidencia-cliente')) {
        const btn = e.target.closest('.btn-delete-evidencia-cliente');
        const url = btn.getAttribute('data-url');
        const nombre = btn.getAttribute('data-nombre');

        const result = await Swal.fire({
            title: '¿Eliminar archivo?',
            text: `Se eliminará "${nombre}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            // Deshabilitar botón y mostrar loading
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const formData = new FormData();
                formData.append('<?= csrf_token() ?>', FormManager ? FormManager.csrfHash : '<?= csrf_hash() ?>');

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                // Actualizar token CSRF
                if (data.csrf_token && data.csrf_hash && FormManager) {
                    FormManager.csrfToken = data.csrf_token;
                    FormManager.csrfHash = data.csrf_hash;
                }

                if (data.ok) {
                    showToast(data.message || 'Archivo eliminado', 'success');
                    // Eliminar el elemento de la lista visualmente
                    const listItem = btn.closest('.list-group-item');
                    if (listItem) {
                        listItem.remove();
                        // Actualizar contador de evidencias en el contenedor correcto
                        const tabPane = btn.closest('.tab-pane');
                        if (tabPane) {
                            const container = tabPane.querySelector('.evidencias-container');
                            if (container) {
                                const countSpan = container.querySelector('.evidencias-count');
                                if (countSpan) {
                                    const newCount = parseInt(countSpan.textContent) - 1;
                                    countSpan.textContent = newCount;
                                    if (newCount === 0) {
                                        container.innerHTML = '';
                                    }
                                }
                            }
                        }
                    }
                } else {
                    showToast(data.message || 'Error al eliminar', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            } catch (error) {
                console.error('Error al eliminar:', error);
                showToast('Error de conexión', 'error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    }
});
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($auditoria['estado'] === 'en_proveedor'): ?>
<script>
// ============================================================
// SISTEMA DE FORMULARIOS AJAX - Proveedor
// ============================================================
const FormManager = {
    idAuditoria: <?= $auditoria['id_auditoria'] ?>,
    csrfToken: '<?= csrf_token() ?>',
    csrfHash: '<?= csrf_hash() ?>',
    debounceTimers: {},
    pendingChanges: new Set(),

    init() {
        this.setupButtonListeners();
        this.setupTextareaListeners();
        this.setupBeforeUnloadWarning();
        this.addIndicator();
        console.log('FormManager Proveedor inicializado para auditoría', this.idAuditoria);
    },

    addIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'autosave-indicator';
        indicator.className = 'position-fixed';
        indicator.style.cssText = 'bottom: 20px; right: 20px; z-index: 1050; display: none;';
        indicator.innerHTML = `
            <div class="alert alert-success py-2 px-3 mb-0 shadow-sm d-flex align-items-center">
                <span class="spinner-border spinner-border-sm me-2 autosave-spinner" style="display: none;"></span>
                <i class="bi bi-check-circle me-2 autosave-check" style="display: none;"></i>
                <i class="bi bi-exclamation-triangle me-2 autosave-error" style="display: none;"></i>
                <span class="autosave-text">Guardando...</span>
            </div>
        `;
        document.body.appendChild(indicator);
    },

    showIndicator(status, message) {
        const indicator = document.getElementById('autosave-indicator');
        const spinner = indicator.querySelector('.autosave-spinner');
        const check = indicator.querySelector('.autosave-check');
        const errorIcon = indicator.querySelector('.autosave-error');
        const text = indicator.querySelector('.autosave-text');
        const alert = indicator.querySelector('.alert');

        indicator.style.display = 'block';
        text.textContent = message;

        spinner.style.display = 'none';
        check.style.display = 'none';
        errorIcon.style.display = 'none';

        if (status === 'saving') {
            spinner.style.display = 'inline-block';
            alert.className = 'alert alert-info py-2 px-3 mb-0 shadow-sm d-flex align-items-center';
        } else if (status === 'saved') {
            check.style.display = 'inline-block';
            alert.className = 'alert alert-success py-2 px-3 mb-0 shadow-sm d-flex align-items-center';
            setTimeout(() => { indicator.style.display = 'none'; }, 4000);
        } else if (status === 'error') {
            errorIcon.style.display = 'inline-block';
            alert.className = 'alert alert-danger py-2 px-3 mb-0 shadow-sm d-flex align-items-center';
            setTimeout(() => { indicator.style.display = 'none'; }, 6000);
        }
    },

    // Listeners para botones de guardar
    setupButtonListeners() {
        document.querySelectorAll('.btn-guardar-item').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const form = e.target.closest('.form-item-ajax');
                if (form) {
                    const tipo = form.dataset.tipo;
                    const saved = await this.saveFormWithFiles(form, btn);
                    if (saved) {
                        if (tipo === 'global') {
                            // Para ítems globales: avanzar al siguiente ítem automáticamente
                            const accordionItem = form.closest('.accordion-item');
                            if (accordionItem) {
                                const itemIndex = parseInt(accordionItem.getAttribute('data-item-index'));
                                this.expandNextItemFromIndex(itemIndex);
                            }
                        } else {
                            // Para ítems por cliente: preguntar qué desea hacer
                            await this.askNextActionForClient(form, btn);
                        }
                    }
                }
            });
        });
    },

    // Preguntar al usuario qué desea hacer después de guardar para un cliente
    // Solo se muestra si hay más de 1 cliente
    async askNextActionForClient(form, btn) {
        const accordionItem = form.closest('.accordion-item');
        const tabPane = form.closest('.tab-pane');
        const navTabs = accordionItem.querySelector('.nav-tabs');

        if (!navTabs || !tabPane) return;

        // Contar clientes (pestañas)
        const allTabs = Array.from(navTabs.querySelectorAll('.nav-link'));

        // Si solo hay 1 cliente, no mostrar el diálogo - simplemente quedarse
        if (allTabs.length <= 1) {
            return;
        }

        // Obtener información del ítem actual
        const clienteNombre = btn.dataset.clienteNombre || 'este cliente';

        // Encontrar la pestaña actual activa
        const currentTab = navTabs.querySelector('.nav-link.active');
        const currentIndex = allTabs.indexOf(currentTab);

        // Verificar si hay siguiente cliente en este ítem
        const hasNextClient = currentIndex < allTabs.length - 1;
        const nextClientName = hasNextClient
            ? allTabs[currentIndex + 1].querySelector('span')?.textContent.trim() || 'siguiente cliente'
            : null;

        // Construir las opciones del SweetAlert
        let html = `<p class="mb-3">Has guardado exitosamente para <strong>${clienteNombre}</strong>.</p>`;
        html += `<p class="text-muted">¿Qué deseas hacer ahora?</p>`;

        const result = await Swal.fire({
            title: '¿Continuar?',
            html: html,
            icon: 'success',
            showCancelButton: true,
            showDenyButton: hasNextClient,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            denyButtonColor: '#0d6efd',
            confirmButtonText: `<i class="bi bi-arrow-repeat"></i> Seguir en ${clienteNombre}`,
            denyButtonText: hasNextClient ? `<i class="bi bi-arrow-right"></i> Ir a ${nextClientName}` : '',
            cancelButtonText: '<i class="bi bi-x"></i> Cerrar',
            allowOutsideClick: false,
            customClass: {
                popup: 'swal-wide'
            }
        });

        if (result.isDenied && hasNextClient) {
            // Ir al siguiente cliente del mismo ítem
            const nextTab = allTabs[currentIndex + 1];
            if (nextTab) {
                const tab = new bootstrap.Tab(nextTab);
                tab.show();
            }
        }
        // Si confirma o cancela, se queda en la pestaña actual
    },

    // Listeners para textareas (autosave)
    setupTextareaListeners() {
        document.querySelectorAll('textarea.autosave-textarea').forEach(textarea => {
            const textareaId = this.getTextareaId(textarea);

            textarea.addEventListener('input', (e) => {
                this.pendingChanges.add(textareaId);

                if (this.debounceTimers[textareaId]) {
                    clearTimeout(this.debounceTimers[textareaId]);
                }

                this.debounceTimers[textareaId] = setTimeout(() => {
                    this.saveTextarea(textarea);
                }, 2000);
            });

            textarea.addEventListener('blur', (e) => {
                if (this.pendingChanges.has(textareaId)) {
                    if (this.debounceTimers[textareaId]) {
                        clearTimeout(this.debounceTimers[textareaId]);
                    }
                    this.saveTextarea(textarea);
                }
            });
        });
    },

    getTextareaId(textarea) {
        const tipo = textarea.dataset.tipo;
        const itemId = textarea.dataset.itemId;
        const clienteId = textarea.dataset.clienteId || '';
        return `${tipo}-${itemId}-${clienteId}`;
    },

    // Guardar solo textarea vía AJAX (autosave)
    async saveTextarea(textarea) {
        const textareaId = this.getTextareaId(textarea);
        const tipo = textarea.dataset.tipo;
        const idAuditoriaItem = textarea.dataset.itemId;
        const idCliente = textarea.dataset.clienteId;
        const comentario = textarea.value;

        this.showIndicator('saving', 'Guardando...');

        try {
            const formData = new FormData();
            formData.append(this.csrfToken, this.csrfHash);
            formData.append('tipo', tipo);
            formData.append('id_auditoria_item', idAuditoriaItem);
            formData.append('comentario', comentario);
            if (idCliente) {
                formData.append('id_cliente', idCliente);
            }

            const response = await fetch(`<?= site_url('proveedor/auditoria/') ?>${this.idAuditoria}/autosave`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();

            if (data.csrf_token && data.csrf_hash) {
                this.csrfToken = data.csrf_token;
                this.csrfHash = data.csrf_hash;
            }

            if (data.ok) {
                this.pendingChanges.delete(textareaId);
                this.showIndicator('saved', `Guardado ${data.timestamp}`);

                if (comentario.trim()) {
                    this.updateItemBadge(idAuditoriaItem, tipo, idCliente);
                }

                // Actualizar progreso si viene en la respuesta
                if (data.progreso) {
                    this.updateProgressBar(data.progreso);
                }
            } else {
                this.showIndicator('error', data.message || 'Error al guardar');
            }
        } catch (error) {
            console.error('Autosave error:', error);
            this.showIndicator('error', 'Error de conexión');
        }
    },

    // Guardar formulario completo con archivos
    async saveFormWithFiles(form, btn) {
        const tipo = form.dataset.tipo;
        const idAuditoria = form.dataset.idAuditoria;
        const idAuditoriaItem = form.dataset.idItem;
        const idCliente = form.dataset.idCliente;

        // Validar comentario
        let comentarioField;
        if (tipo === 'global') {
            comentarioField = form.querySelector('textarea[name="comentario_proveedor"]');
        } else {
            comentarioField = form.querySelector('textarea[name="comentario_proveedor_cliente"]');
        }

        if (!comentarioField || !comentarioField.value.trim()) {
            showToast('Debe ingresar un comentario', 'warning');
            comentarioField?.focus();
            return false;
        }

        // Deshabilitar botón y mostrar loading
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

        this.showIndicator('saving', 'Guardando ítem...');

        try {
            const formData = new FormData(form);
            formData.append(this.csrfToken, this.csrfHash);

            const url = `<?= site_url('proveedor/auditoria/') ?>${idAuditoria}/item/${idAuditoriaItem}/guardar-ajax`;

            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();

            // Actualizar token CSRF
            if (data.csrf_token && data.csrf_hash) {
                this.csrfToken = data.csrf_token;
                this.csrfHash = data.csrf_hash;
            }

            if (data.ok) {
                this.showIndicator('saved', data.message);
                showToast(data.message, 'success');

                // Limpiar input de archivos
                const fileInputs = form.querySelectorAll('input[type="file"]');
                fileInputs.forEach(input => input.value = '');

                // Actualizar lista de evidencias si hay archivos
                if (data.evidencias && data.evidencias.length > 0) {
                    this.renderEvidencias(data.evidencias, data.id_item, data.id_cliente, data.tipo, data.id_auditoria);
                }

                // Actualizar badge
                this.updateItemBadge(idAuditoriaItem, tipo, idCliente);

                // Actualizar progreso
                if (data.progreso) {
                    this.updateProgressBar(data.progreso);
                }

                // Limpiar cambios pendientes
                const textareaId = `${tipo}-${idAuditoriaItem}-${idCliente || ''}`;
                this.pendingChanges.delete(textareaId);

                btn.disabled = false;
                btn.innerHTML = originalText;
                return true;
            } else {
                this.showIndicator('error', data.message || 'Error al guardar');
                showToast(data.message || 'Error al guardar', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
                return false;
            }
        } catch (error) {
            console.error('Error al guardar:', error);
            this.showIndicator('error', 'Error de conexión');
            showToast('Error de conexión. Intente nuevamente.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
            return false;
        }
    },

    // Actualizar barra de progreso en tiempo real
    updateProgressBar(progreso) {
        const porcentaje = Math.round(progreso.porcentaje_total);
        const todoCompleto = porcentaje >= 100;

        // Actualizar badge de porcentaje
        const badgePct = document.querySelector('#progress-bar-compact .badge');
        if (badgePct) {
            badgePct.textContent = `${porcentaje}%`;
            badgePct.className = `badge ${todoCompleto ? 'bg-success' : 'bg-primary'} me-2`;
        }

        // Actualizar barra principal
        const progressBar = document.querySelector('#progress-bar-compact .progress-bar');
        if (progressBar) {
            progressBar.style.width = `${porcentaje}%`;
            progressBar.className = `progress-bar ${todoCompleto ? 'bg-success' : 'bg-primary'}`;
        }

        // Actualizar contador de ítems
        const itemsCount = document.querySelector('#progress-bar-compact small.text-muted');
        if (itemsCount) {
            itemsCount.textContent = `${progreso.total_completados}/${progreso.total} ítems`;
        }

        // Mostrar/ocultar badge "Listo para enviar"
        let readyBadge = document.querySelector('#progress-bar-compact .badge.bg-success:last-of-type');
        if (todoCompleto) {
            if (!readyBadge || !readyBadge.textContent.includes('Listo')) {
                const container = document.querySelector('#progress-bar-compact .d-flex');
                const badge = document.createElement('span');
                badge.className = 'badge bg-success';
                badge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Listo para enviar';
                const chevronBtn = container.querySelector('button[data-bs-toggle="collapse"]');
                if (chevronBtn) {
                    container.insertBefore(badge, chevronBtn);
                }
            }
        }

        // Habilitar/deshabilitar botón de finalizar
        const finalizarBtn = document.querySelector('.card.border-primary button[type="submit"]');
        if (finalizarBtn) {
            finalizarBtn.disabled = !todoCompleto;
        }

        // Actualizar detalles expandibles
        const globalesInfo = document.querySelector('#progress-details .col-6:first-child small');
        if (globalesInfo) {
            globalesInfo.textContent = `Globales: ${progreso.globales_completos}/${progreso.globales_total}`;
        }

        const clientesInfo = document.querySelector('#progress-details .col-6:last-child small');
        if (clientesInfo) {
            clientesInfo.textContent = `Por Cliente: ${progreso.por_cliente_completos}/${progreso.por_cliente_total}`;
        }
    },

    // Actualizar badge visual del ítem
    updateItemBadge(idAuditoriaItem, tipo, idCliente) {
        const accordionItem = document.getElementById(`item-${idAuditoriaItem}`);
        if (!accordionItem) return;

        const button = accordionItem.querySelector('.accordion-button');
        if (!button) return;

        // Para ítems por cliente, actualizar el tab del cliente
        if (tipo === 'cliente' && idCliente) {
            const tabButton = document.getElementById(`tab-item${idAuditoriaItem}-cliente${idCliente}`);
            if (tabButton && !tabButton.querySelector('.bi-check-circle-fill')) {
                const checkIcon = document.createElement('i');
                checkIcon.className = 'bi bi-check-circle-fill text-success ms-1';
                tabButton.appendChild(checkIcon);
            }
        }

        // Actualizar badge del accordion header (global o cuando todos los clientes están completos)
        if (tipo === 'global') {
            const statusBadge = button.querySelector('.badge.bg-warning');
            if (statusBadge) {
                statusBadge.className = 'badge bg-success';
                statusBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Completado';
            }
            accordionItem.classList.add('border-success');
            button.classList.add('bg-success', 'bg-opacity-10');
        }
    },

    // Expandir siguiente ítem con scroll suave
    expandNextItemFromIndex(currentIndex) {
        const nextIndex = currentIndex + 1;
        const nextItem = document.querySelector(`.accordion-item[data-item-index="${nextIndex}"]`);

        if (nextItem) {
            const currentItem = document.querySelector(`.accordion-item[data-item-index="${currentIndex}"]`);
            if (currentItem) {
                const currentCollapse = currentItem.querySelector('.accordion-collapse');
                const bsCollapse = bootstrap.Collapse.getInstance(currentCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            }

            const nextCollapse = nextItem.querySelector('.accordion-collapse');
            const nextBsCollapse = new bootstrap.Collapse(nextCollapse, { toggle: false });

            setTimeout(() => {
                nextBsCollapse.show();

                setTimeout(() => {
                    const headerOffset = 100;
                    const elementPosition = nextItem.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }, 350);
            }, 300);
        } else {
            const finalizarCard = document.querySelector('.card.border-primary');
            if (finalizarCard) {
                setTimeout(() => {
                    const headerOffset = 100;
                    const elementPosition = finalizarCard.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }, 400);
            }
        }
    },

    // Alerta al salir con cambios pendientes
    setupBeforeUnloadWarning() {
        window.addEventListener('beforeunload', (e) => {
            if (this.pendingChanges.size > 0) {
                e.preventDefault();
                e.returnValue = 'Tienes cambios sin guardar. ¿Seguro que quieres salir?';
                return e.returnValue;
            }
        });
    },

    // Renderizar lista de evidencias después de subir archivos
    renderEvidencias(evidencias, idItem, idCliente, tipo, idAuditoria) {
        let containerId;
        if (tipo === 'global') {
            containerId = `evidencias-${idItem}`;
        } else {
            containerId = `evidencias-${idItem}-${idCliente}`;
        }

        const container = document.getElementById(containerId);
        if (!container) {
            console.error('Contenedor de evidencias no encontrado:', containerId);
            return;
        }

        // Construir HTML de la lista de evidencias
        let html = `
            <label class="form-label">
                <i class="bi bi-file-earmark-check"></i> Archivos Cargados (<span class="evidencias-count">${evidencias.length}</span>)
            </label>
            <div class="list-group">
        `;

        evidencias.forEach(ev => {
            const sizeKB = (ev.tamanio_bytes / 1024).toFixed(2);
            const fecha = new Date(ev.created_at).toLocaleString('es-CO', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            // Determinar URL de eliminación según tipo
            let deleteUrl;
            if (tipo === 'global') {
                deleteUrl = `<?= site_url('proveedor/auditoria/') ?>${idAuditoria}/evidencia/${ev.id_evidencia}/eliminar`;
            } else {
                deleteUrl = `<?= site_url('proveedor/auditoria/') ?>${idAuditoria}/evidencia-cliente/${ev.id_evidencia_cliente}/eliminar`;
            }

            const btnClass = tipo === 'global' ? 'btn-delete-evidencia' : 'btn-delete-evidencia-cliente';

            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-file-earmark text-primary"></i>
                        <strong>${this.escapeHtml(ev.nombre_archivo_original)}</strong>
                        <br>
                        <small class="text-muted">
                            ${sizeKB} KB
                            • Subido: ${fecha}
                        </small>
                    </div>
                    <button type="button"
                            class="btn btn-sm btn-outline-danger ${btnClass}"
                            data-url="${deleteUrl}"
                            data-nombre="${this.escapeHtml(ev.nombre_archivo_original)}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
    },

    // Escapar HTML para prevenir XSS
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    FormManager.init();
});
</script>
<?php endif; ?>

<script>
/**
 * Validación de tamaño de archivos antes de enviar
 * Muestra instructivo de Google Drive si el archivo es muy pesado
 */
const MAX_FILE_SIZE_MB = 10;
const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;

function validarTamanoArchivos(inputElement) {
    const files = inputElement.files;
    let archivosGrandes = [];

    for (let i = 0; i < files.length; i++) {
        if (files[i].size > MAX_FILE_SIZE_BYTES) {
            const sizeMB = (files[i].size / (1024 * 1024)).toFixed(2);
            archivosGrandes.push(`<li><strong>${files[i].name}</strong> (${sizeMB} MB)</li>`);
        }
    }

    if (archivosGrandes.length > 0) {
        // Limpiar el input de archivos
        inputElement.value = '';

        // Mostrar SweetAlert con instructivo
        Swal.fire({
            icon: 'warning',
            title: '¡Archivo(s) demasiado grande(s)!',
            html: `
                <div style="text-align: left;">
                    <p>Los siguientes archivos superan el límite de <strong>${MAX_FILE_SIZE_MB} MB</strong>:</p>
                    <ul style="color: #dc3545; margin-bottom: 15px;">${archivosGrandes.join('')}</ul>

                    <hr>

                    <h5 style="color: #0d6efd;"><i class="bi bi-cloud-upload"></i> Solución alternativa (medida excepcional):</h5>

                    <ol style="padding-left: 20px;">
                        <li><strong>Suba el archivo a Google Drive</strong>
                            <br><small class="text-muted">Puede usar su cuenta personal o empresarial</small>
                        </li>
                        <li><strong>Haga clic derecho sobre el archivo → "Compartir"</strong></li>
                        <li><strong>Cambie el acceso a "Cualquier persona con el enlace"</strong>
                            <br><small class="text-muted">Esto permite que el consultor pueda ver el archivo</small>
                        </li>
                        <li><strong>Copie el enlace</strong></li>
                        <li><strong>Pegue el enlace en el campo de "Comentarios"</strong> de este ítem
                            <br><small class="text-muted">El enlace es válido como evidencia</small>
                        </li>
                    </ol>

                    <div class="alert alert-success mt-3 mb-0" style="font-size: 14px;">
                        <i class="bi bi-check-circle-fill"></i>
                        <strong>¡Importante!</strong> Con el enlace de Google Drive puede continuar sin necesidad de adjuntar el archivo directamente.
                    </div>
                </div>
            `,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#0d6efd',
            width: '600px',
            timer: 30000,
            timerProgressBar: true
        });

        return false;
    }

    return true;
}

// Agregar validación a todos los inputs de archivo
document.querySelectorAll('input[type="file"]').forEach(function(input) {
    input.addEventListener('change', function() {
        validarTamanoArchivos(this);
    });
});

// Validar antes de enviar el formulario
document.querySelectorAll('form[enctype="multipart/form-data"]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        const fileInputs = this.querySelectorAll('input[type="file"]');
        let todosValidos = true;

        fileInputs.forEach(function(input) {
            if (input.files.length > 0 && !validarTamanoArchivos(input)) {
                todosValidos = false;
            }
        });

        if (!todosValidos) {
            e.preventDefault();
        }
    });
});
</script>
<?= $this->endSection() ?>
