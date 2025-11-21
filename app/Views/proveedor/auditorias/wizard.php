<?= $this->extend('layouts/main') ?>

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

<!-- Progreso General -->
<div class="card mb-4 border-primary shadow-sm sticky-top" style="top: 70px; z-index: 1020; background-color: white;">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="bi bi-graph-up"></i> Progreso General</h5>
            <span class="badge bg-<?= $progreso['porcentaje_total'] >= 100 ? 'success' : 'warning' ?> fs-6">
                <?= number_format($progreso['porcentaje_total'], 0) ?>%
            </span>
        </div>

        <!-- Barra de progreso total -->
        <div class="progress mb-3" style="height: 30px;">
            <div class="progress-bar <?= $progreso['porcentaje_total'] >= 100 ? 'bg-success' : 'bg-primary' ?>"
                 role="progressbar"
                 style="width: <?= $progreso['porcentaje_total'] ?>%;"
                 aria-valuenow="<?= $progreso['porcentaje_total'] ?>"
                 aria-valuemin="0"
                 aria-valuemax="100">
                <?= number_format($progreso['porcentaje_total'], 0) ?>%
            </div>
        </div>

        <!-- Desglose de progreso -->
        <div class="row g-2">
            <!-- Ítems Globales -->
            <div class="col-md-6">
                <div class="border rounded p-2 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-bold text-primary">
                            <i class="bi bi-globe"></i> Ítems Globales
                        </small>
                        <small class="badge bg-primary">
                            <?= $progreso['globales_completos'] ?> / <?= $progreso['globales_total'] ?>
                        </small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <?php
                        $pctGlobales = $progreso['globales_total'] > 0
                            ? ($progreso['globales_completos'] / $progreso['globales_total']) * 100
                            : 0;
                        ?>
                        <div class="progress-bar bg-primary"
                             style="width: <?= $pctGlobales ?>%;"></div>
                    </div>
                </div>
            </div>

            <!-- Ítems Por Cliente -->
            <div class="col-md-6">
                <div class="border rounded p-2 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="fw-bold text-warning">
                            <i class="bi bi-building"></i> Ítems Por Cliente
                        </small>
                        <small class="badge bg-warning">
                            <?= $progreso['por_cliente_completos'] ?> / <?= $progreso['por_cliente_total'] ?>
                        </small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <?php
                        $pctPorCliente = $progreso['por_cliente_total'] > 0
                            ? ($progreso['por_cliente_completos'] / $progreso['por_cliente_total']) * 100
                            : 0;
                        ?>
                        <div class="progress-bar bg-warning"
                             style="width: <?= $pctPorCliente ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($progreso['porcentaje_total'] >= 100): ?>
            <div class="alert alert-success mt-3 mb-0">
                <i class="bi bi-check-circle-fill"></i> Todos los ítems están completos. Puede enviar a revisión.
            </div>
        <?php else: ?>
            <small class="text-muted mt-3 d-block">
                Complete todos los ítems con al menos un comentario para poder finalizar la auditoría
            </small>
        <?php endif; ?>
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
                <form method="post"
                      action="<?= site_url('proveedor/auditoria/' . $auditoria['id_auditoria'] . '/item/' . $item['id_auditoria_item'] . '/guardar') ?>"
                      enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-chat-left-text"></i> Comentario del Proveedor
                            <span class="text-danger">*</span>
                        </label>
                        <textarea name="comentario_proveedor"
                                  class="form-control"
                                  rows="3"
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

                    <?php if (!empty($item['evidencias'])): ?>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-file-earmark-check"></i> Archivos Cargados (<?= count($item['evidencias']) ?>)
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
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-success">
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

                                <form method="post"
                                      action="<?= site_url('proveedor/auditoria/' . $auditoria['id_auditoria'] . '/item/' . $item['id_auditoria_item'] . '/guardar') ?>"
                                      enctype="multipart/form-data">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">

                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-chat-left-text"></i> Comentario para este Cliente
                                            <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="comentario_proveedor_cliente"
                                                  class="form-control"
                                                  rows="3"
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

                                    <?php if ($itemCliente && !empty($itemCliente['evidencias'])): ?>
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="bi bi-file-earmark-check"></i> Archivos Cargados (<?= count($itemCliente['evidencias']) ?>)
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
                                        </div>
                                    <?php endif; ?>

                                    <button type="submit" class="btn btn-success save-cliente-btn" data-cliente-nombre="<?= esc($cliente['razon_social']) ?>">
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

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle-fill me-2"></i>
                <span id="toastMessage">Guardado exitosamente</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- JavaScript for Accordion auto-navigation and Toast notifications -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show toast on successful save from flash messages
    <?php if (session()->getFlashdata('success')): ?>
        showSuccessToast('<?= addslashes(session()->getFlashdata('success')) ?>');

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

                // Scroll to the next item smoothly
                setTimeout(() => {
                    nextItem.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 350);
            }, 350);
        } else {
            // No more items - scroll to finalizar button
            const finalizarCard = document.querySelector('.card.border-primary');
            if (finalizarCard) {
                setTimeout(() => {
                    finalizarCard.scrollIntoView({
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
 * Show success toast with custom message
 */
function showSuccessToast(message) {
    const toastEl = document.getElementById('successToast');
    const toastMessage = document.getElementById('toastMessage');

    toastMessage.textContent = message;

    const toast = new bootstrap.Toast(toastEl, {
        animation: true,
        autohide: true,
        delay: 4000
    });

    toast.show();
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
