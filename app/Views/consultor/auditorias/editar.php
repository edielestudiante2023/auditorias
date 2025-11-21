<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2><i class="bi bi-pencil-square"></i> Editar Auditoría</h2>
    <p class="text-muted">Modificar la fecha de vencimiento de la auditoría</p>
</div>

<?= view('partials/flash') ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-calendar-event"></i> Información de la Auditoría
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Importante:</strong> Esta opción le permite ajustar la fecha de vencimiento cuando
                    los proveedores soliciten más plazo para completar la auditoría.
                </div>

                <!-- Información de la Auditoría (Solo lectura) -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Proveedor</label>
                        <p class="fw-bold"><?= esc($proveedor['razon_social']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">NIT</label>
                        <p class="fw-bold"><?= esc($proveedor['nit']) ?></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Código de Formato</label>
                        <p class="fw-bold"><?= esc($auditoria['codigo_formato'] ?: 'No especificado') ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Versión</label>
                        <p class="fw-bold"><?= esc($auditoria['version_formato'] ?: 'No especificado') ?></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Estado</label>
                        <p><?= estadoBadge($auditoria['estado']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Fecha de Creación</label>
                        <p class="fw-bold"><?= formatoFechaSolo($auditoria['created_at']) ?></p>
                    </div>
                </div>

                <hr>

                <!-- Formulario de Edición -->
                <form action="<?= site_url('consultor/auditorias/' . $auditoria['id_auditoria'] . '/actualizar') ?>" method="POST">
                    <?= csrf_field() ?>

                    <!-- Fecha Programada (Editable) -->
                    <div class="mb-3">
                        <label for="fecha_programada" class="form-label">
                            Fecha de Vencimiento <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               class="form-control <?= isset($errors['fecha_programada']) ? 'is-invalid' : '' ?>"
                               id="fecha_programada"
                               name="fecha_programada"
                               value="<?= old('fecha_programada', $auditoria['fecha_programada']) ?>"
                               required>
                        <?php if (isset($errors['fecha_programada'])): ?>
                            <div class="invalid-feedback"><?= esc($errors['fecha_programada']) ?></div>
                        <?php endif; ?>
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i>
                            Establezca la nueva fecha límite para que el proveedor complete la auditoría
                        </div>
                    </div>

                    <!-- Comentario opcional sobre el cambio -->
                    <div class="mb-3">
                        <label for="motivo_cambio" class="form-label">
                            Motivo del Cambio (Opcional)
                        </label>
                        <textarea class="form-control"
                                  id="motivo_cambio"
                                  name="motivo_cambio"
                                  rows="3"
                                  placeholder="Ej: Proveedor solicitó extensión debido a alta carga de trabajo"><?= old('motivo_cambio') ?></textarea>
                        <div class="form-text">
                            Este comentario quedará registrado en la bitácora de la auditoría (opcional)
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?= site_url('consultor/auditorias') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Configurar fecha mínima a hoy
    const today = new Date().toISOString().split('T')[0];
    $('#fecha_programada').attr('min', today);
});
</script>
<?= $this->endSection() ?>
