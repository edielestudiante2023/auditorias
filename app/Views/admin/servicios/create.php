<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-plus-circle"></i> Crear Servicio</h2>
    <a href="<?= site_url('admin/servicios') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= site_url('admin/servicios/store') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control <?= session('errors.nombre') ? 'is-invalid' : '' ?>"
                       id="nombre"
                       name="nombre"
                       value="<?= old('nombre') ?>"
                       required
                       maxlength="100"
                       placeholder="Ej: Vigilancia, Aseo, Motobombas">
                <?php if (session('errors.nombre')): ?>
                    <div class="invalid-feedback">
                        <?= session('errors.nombre') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="activo" class="form-label">Estado</label>
                <select class="form-select <?= session('errors.activo') ? 'is-invalid' : '' ?>"
                        id="activo"
                        name="activo">
                    <option value="1" <?= old('activo', '1') == '1' ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= old('activo') == '0' ? 'selected' : '' ?>>Inactivo</option>
                </select>
                <?php if (session('errors.activo')): ?>
                    <div class="invalid-feedback">
                        <?= session('errors.activo') ?>
                    </div>
                <?php endif; ?>
            </div>

            <hr>

            <div class="d-flex justify-content-end gap-2">
                <a href="<?= site_url('admin/servicios') ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Servicio
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
