<!-- Modal para Aplicar Override -->
<div class="modal fade" id="overrideModal" tabindex="-1" aria-labelledby="overrideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="overrideModalLabel">
                    <i class="bi bi-pencil-square"></i> Aplicar Override (Ajuste Específico)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>¿Qué es un override?</strong><br>
                    Un override permite aplicar una calificación diferente para un cliente específico en este ítem,
                    sin afectar la calificación general. Útil cuando un cliente tiene condiciones particulares.
                </div>

                <div class="mb-3">
                    <strong>Ítem:</strong>
                    <div id="override_item_info" class="text-muted"></div>
                </div>

                <form id="overrideForm">
                    <input type="hidden" id="override_item_id" name="id_auditoria_item">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">

                    <div class="mb-3">
                        <label class="form-label"><strong>Cliente <span class="text-danger">*</span></strong></label>
                        <select class="form-select" id="override_cliente" name="id_cliente" required>
                            <option value="">Seleccione un cliente...</option>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id_cliente'] ?>">
                                        <?= esc($cliente['razon_social']) ?> (<?= esc($cliente['nit']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Calificación Ajustada <span class="text-danger">*</span></strong></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="calificacion_ajustada" value="cumple" id="override_cumple" required>
                            <label class="btn btn-outline-success" for="override_cumple">
                                <i class="bi bi-check-circle"></i> Cumple
                            </label>

                            <input type="radio" class="btn-check" name="calificacion_ajustada" value="parcial" id="override_parcial">
                            <label class="btn btn-outline-warning" for="override_parcial">
                                <i class="bi bi-dash-circle"></i> Parcial
                            </label>

                            <input type="radio" class="btn-check" name="calificacion_ajustada" value="no_cumple" id="override_no">
                            <label class="btn btn-outline-danger" for="override_no">
                                <i class="bi bi-x-circle"></i> No Cumple
                            </label>

                            <input type="radio" class="btn-check" name="calificacion_ajustada" value="no_aplica" id="override_na">
                            <label class="btn btn-outline-secondary" for="override_na">
                                <i class="bi bi-slash-circle"></i> No Aplica
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Comentario Específico del Cliente</strong></label>
                        <textarea class="form-control" id="override_comentario" name="comentario_cliente" rows="3"
                                  placeholder="Explique el motivo del ajuste para este cliente..."></textarea>
                    </div>

                    <div id="override_alert" class="alert alert-danger d-none"></div>
                    <div id="override_success" class="alert alert-success d-none"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnSaveOverride">
                    <i class="bi bi-save"></i> Guardar Override
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btnSaveOverride')?.addEventListener('click', function() {
    const form = document.getElementById('overrideForm');
    const formData = new FormData(form);
    const alertDiv = document.getElementById('override_alert');
    const successDiv = document.getElementById('override_success');

    // Validar que se haya seleccionado cliente
    if (!formData.get('id_cliente')) {
        alertDiv.textContent = 'Debe seleccionar un cliente';
        alertDiv.classList.remove('d-none');
        return;
    }

    // Validar que se haya seleccionado calificación
    if (!formData.get('calificacion_ajustada')) {
        alertDiv.textContent = 'Debe seleccionar una calificación';
        alertDiv.classList.remove('d-none');
        return;
    }

    alertDiv.classList.add('d-none');
    successDiv.classList.add('d-none');

    // Enviar petición AJAX
    fetch('/consultor/auditoria/<?= $auditoria['id_auditoria'] ?>/override', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            successDiv.textContent = data.message;
            successDiv.classList.remove('d-none');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            alertDiv.textContent = data.message;
            alertDiv.classList.remove('d-none');
        }
    })
    .catch(error => {
        alertDiv.textContent = 'Error al aplicar override: ' + error;
        alertDiv.classList.remove('d-none');
    });
});

// Limpiar formulario al cerrar modal
document.getElementById('overrideModal')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('overrideForm').reset();
    document.getElementById('override_alert').classList.add('d-none');
    document.getElementById('override_success').classList.add('d-none');
});
</script>
