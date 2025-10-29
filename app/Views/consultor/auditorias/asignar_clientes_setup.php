<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2><i class="bi bi-building-check"></i> Asignar Clientes - Paso 3 de 4</h2>
    <p class="text-muted">
        Seleccione los clientes que recibirán el informe de esta auditoría.
        <br>
        <strong>Proveedor:</strong> <?= esc($proveedor['razon_social']) ?> (NIT: <?= esc($proveedor['nit']) ?>)
    </p>
</div>

<?= view('partials/flash') ?>

<!-- Progress Stepper -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-check"></i>
                </div>
                <div class="mt-2 small"><strong>Paso 1</strong><br>Información Básica</div>
            </div>
            <div style="flex: 1; height: 2px; background-color: #28a745;"></div>
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-check"></i>
                </div>
                <div class="mt-2 small"><strong>Paso 2</strong><br>Seleccionar Ítems</div>
            </div>
            <div style="flex: 1; height: 2px; background-color: #28a745;"></div>
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    3
                </div>
                <div class="mt-2 small"><strong>Paso 3</strong><br>Asignar Clientes</div>
            </div>
            <div style="flex: 1; height: 2px; background-color: #dee2e6;"></div>
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    4
                </div>
                <div class="mt-2 small"><strong>Paso 4</strong><br>Enviar Invitación</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-building"></i> Clientes Disponibles
    </div>
    <div class="card-body">
        <?php if (empty($clientes)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                No hay clientes disponibles con contratos activos para este proveedor.
                <br><br>
                Debe crear contratos activos en <a href="<?= site_url('admin/contratos') ?>">Gestión de Contratos</a> antes de continuar.
            </div>
            <div class="mt-3">
                <a href="<?= site_url('consultor/auditorias') ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver a Auditorías
                </a>
            </div>
        <?php else: ?>
            <p class="text-muted mb-4">
                Seleccione los clientes que recibirán el informe de auditoría.
                Solo se muestran clientes con contratos activos.
            </p>

            <form method="post" action="<?= site_url('consultor/auditorias/' . $auditoria['id_auditoria'] . '/asignar-clientes-setup') ?>" id="formAsignarClientes">
                <?= csrf_field() ?>

                <div class="row">
                    <?php foreach ($clientes as $cliente): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="clientes[]"
                                               value="<?= $cliente['id_cliente'] ?>"
                                               id="cliente<?= $cliente['id_cliente'] ?>"
                                               data-contrato="<?= $cliente['id_contrato'] ?>"
                                               <?= in_array($cliente['id_cliente'], $asignados) ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100" for="cliente<?= $cliente['id_cliente'] ?>">
                                            <div>
                                                <strong><?= esc($cliente['razon_social']) ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-card-text"></i> NIT: <?= esc($cliente['nit']) ?>
                                                </small>
                                                <?php if (!empty($cliente['email_admin'])): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-envelope"></i> <?= esc($cliente['email_admin']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            <?= count($clientes) ?> cliente(s) disponible(s)
                        </small>
                    </div>
                    <div>
                        <a href="<?= site_url('consultor/auditorias') ?>" class="btn btn-outline-secondary me-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Continuar al Paso 4 <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($asignados)): ?>
    <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle"></i>
        <strong>Clientes actualmente asignados:</strong> <?= count($asignados) ?>
    </div>
<?php endif; ?>

<style>
/* Hacer checkboxes más grandes y visibles */
.form-check-input {
    width: 1.5em !important;
    height: 1.5em !important;
    margin-top: 0.1em !important;
    border: 2px solid #0d6efd !important;
    cursor: pointer !important;
}

.form-check-input:checked {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
}

.form-check-input:focus {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

.form-check-label {
    cursor: pointer !important;
    padding-left: 0.5rem;
}

/* Resaltar la tarjeta cuando el checkbox está marcado */
.card:has(.form-check-input:checked) {
    border: 2px solid #0d6efd !important;
    background-color: #e7f1ff !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formAsignarClientes');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        // Recopilar datos de contratos y servicios de los clientes seleccionados
        const checkboxes = form.querySelectorAll('input[name="clientes[]"]:checked');

        checkboxes.forEach(checkbox => {
            const idCliente = checkbox.value;
            const idContrato = checkbox.getAttribute('data-contrato');

            // Crear campo oculto para enviar el snapshot de contrato
            if (idContrato) {
                const inputContrato = document.createElement('input');
                inputContrato.type = 'hidden';
                inputContrato.name = `contrato[${idCliente}]`;
                inputContrato.value = idContrato;
                form.appendChild(inputContrato);
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
