<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-building-check"></i> Asignar Clientes</h2>
        <p class="text-muted mb-0">
            Auditoría: <?= esc($auditoria['codigo_formato'] ?? '#' . $auditoria['id_auditoria']) ?>
        </p>
    </div>
    <a href="<?= site_url('consultor/auditoria/' . $auditoria['id_auditoria']) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-building"></i> Clientes Disponibles
    </div>
    <div class="card-body">
        <?php if (empty($clientes)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                No hay clientes disponibles con contratos activos para este proveedor.
            </div>
        <?php else: ?>
            <p class="text-muted mb-4">
                Seleccione los clientes que recibirán el informe de esta auditoría.
                Solo se muestran clientes con contratos activos.
            </p>

            <form method="post" action="<?= site_url('consultor/auditoria/' . $auditoria['id_auditoria'] . '/asignar-clientes') ?>">
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
                        <a href="<?= site_url('consultor/auditoria/' . $auditoria['id_auditoria']) ?>" class="btn btn-outline-secondary me-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Asignación
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

<?= $this->endSection() ?>
