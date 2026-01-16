<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
            <?php if (!empty($item['descripcion'])): ?>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle"></i>
                    <strong>Descripción:</strong> <?= esc($item['descripcion']) ?>
                </div>
            <?php endif; ?>

            <?php if ($item['alcance'] === 'global'): ?>
                <!-- ÍTEM GLOBAL -->
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
                <!-- ÍTEM POR CLIENTE - TABS -->
                <?php if (empty($clientes)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        No hay clientes asignados a esta auditoría para este ítem.
                    </div>
                <?php else: ?>
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <?php foreach ($clientes as $idx => $cliente): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $idx === 0 ? 'active' : '' ?>"
                                        id="tab-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                        data-bs-toggle="tab"
                                        data-bs-target="#content-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                        type="button"
                                        role="tab">
                                    <?php
                                    // Verificar si este cliente tiene comentario
                                    $clienteCompleto = false;
                                    foreach ($item['items_cliente'] as $ic) {
                                        if ($ic['id_cliente'] == $cliente['id_cliente'] && !empty($ic['comentario_proveedor_cliente'])) {
                                            $clienteCompleto = true;
                                            break;
                                        }
                                    }
                                    ?>
                                    <?= esc($cliente['razon_social']) ?>
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
                            ?>
                            <div class="tab-pane fade <?= $idx === 0 ? 'show active' : '' ?>"
                                 id="content-item<?= $item['id_auditoria_item'] ?>-cliente<?= $cliente['id_cliente'] ?>"
                                 role="tabpanel">

                                <div class="alert alert-secondary mb-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-building"></i>
                                        <strong>Cliente:</strong> <?= esc($cliente['razon_social']) ?>
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
 * Manejar eliminación de evidencias globales
 */
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-delete-evidencia')) {
        const btn = e.target.closest('.btn-delete-evidencia');
        const url = btn.getAttribute('data-url');
        const nombre = btn.getAttribute('data-nombre');

        if (confirm(`¿Está seguro de eliminar el archivo "${nombre}"?`)) {
            // Deshabilitar botón y mostrar loading
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            // Crear formulario y enviarlo
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            // Agregar token CSRF
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '<?= csrf_token() ?>';
            csrfInput.value = '<?= csrf_hash() ?>';
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
});

/**
 * Manejar eliminación de evidencias de cliente
 */
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-delete-evidencia-cliente')) {
        const btn = e.target.closest('.btn-delete-evidencia-cliente');
        const url = btn.getAttribute('data-url');
        const nombre = btn.getAttribute('data-nombre');

        if (confirm(`¿Está seguro de eliminar el archivo "${nombre}"?`)) {
            // Deshabilitar botón y mostrar loading
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            // Crear formulario y enviarlo
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            // Agregar token CSRF
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '<?= csrf_token() ?>';
            csrfInput.value = '<?= csrf_hash() ?>';
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
});
</script>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                    const saved = await this.saveFormWithFiles(form, btn);
                    if (saved) {
                        const accordionItem = form.closest('.accordion-item');
                        if (accordionItem) {
                            const itemIndex = parseInt(accordionItem.getAttribute('data-item-index'));
                            this.expandNextItemFromIndex(itemIndex);
                        }
                    }
                }
            });
        });
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
