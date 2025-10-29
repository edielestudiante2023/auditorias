<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2><i class="bi bi-plus-circle"></i> Nueva Auditoría - Paso 1 de 4</h2>
    <p class="text-muted">Complete la información básica de la auditoría</p>
</div>

<?= view('partials/flash') ?>

<!-- Progress Stepper -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    1
                </div>
                <div class="mt-2 small"><strong>Paso 1</strong><br>Información Básica</div>
            </div>
            <div style="flex: 1; height: 2px; background-color: #dee2e6;"></div>
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    2
                </div>
                <div class="mt-2 small"><strong>Paso 2</strong><br>Seleccionar Ítems</div>
            </div>
            <div style="flex: 1; height: 2px; background-color: #dee2e6;"></div>
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
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

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <i class="bi bi-file-earmark-text"></i> Información Básica
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Proceso:</strong> Primero cree la auditoría en estado borrador. En el siguiente paso
                    podrá asignar los clientes y luego enviar la invitación al proveedor.
                </div>

                    <form action="<?= site_url('consultor/auditorias/guardar') ?>" method="POST">
                        <?= csrf_field() ?>

                        <!-- Proveedor -->
                        <div class="mb-3">
                            <label for="id_proveedor" class="form-label">
                                Proveedor <span class="text-danger">*</span>
                            </label>
                            <select class="form-select <?= isset($errors['id_proveedor']) ? 'is-invalid' : '' ?>"
                                    id="id_proveedor"
                                    name="id_proveedor"
                                    required>
                                <option value="">Seleccionar proveedor...</option>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <option value="<?= $proveedor['id_proveedor'] ?>"
                                            <?= old('id_proveedor') == $proveedor['id_proveedor'] ? 'selected' : '' ?>>
                                        <?= esc($proveedor['razon_social']) ?> - <?= esc($proveedor['nit']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['id_proveedor'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['id_proveedor']) ?></div>
                            <?php endif; ?>
                            <div class="form-text">
                                <i class="bi bi-info-circle"></i>
                                Seleccione el proveedor que completará la auditoría
                            </div>
                        </div>

                        <!-- Código de Formato -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="codigo_formato" class="form-label">Código de Formato</label>
                                <input type="text"
                                       class="form-control <?= isset($errors['codigo_formato']) ? 'is-invalid' : '' ?>"
                                       id="codigo_formato"
                                       name="codigo_formato"
                                       value="<?= old('codigo_formato', 'FT-SST-217') ?>"
                                       maxlength="50"
                                       placeholder="Ej: FT-SST-217">
                                <?php if (isset($errors['codigo_formato'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['codigo_formato']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="version_formato" class="form-label">Versión</label>
                                <input type="text"
                                       class="form-control <?= isset($errors['version_formato']) ? 'is-invalid' : '' ?>"
                                       id="version_formato"
                                       name="version_formato"
                                       value="<?= old('version_formato', '001') ?>"
                                       maxlength="20"
                                       placeholder="Ej: 001">
                                <?php if (isset($errors['version_formato'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['version_formato']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Fecha Programada -->
                        <div class="mb-3">
                            <label for="fecha_programada" class="form-label">Fecha Programada</label>
                            <input type="date"
                                   class="form-control <?= isset($errors['fecha_programada']) ? 'is-invalid' : '' ?>"
                                   id="fecha_programada"
                                   name="fecha_programada"
                                   value="<?= old('fecha_programada') ?>">
                            <?php if (isset($errors['fecha_programada'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['fecha_programada']) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Fecha estimada para completar la auditoría (opcional)</div>
                        </div>

                        <!-- Info del Consultor -->
                        <div class="alert alert-secondary">
                            <strong>Consultor asignado:</strong> <?= esc($consultor['nombre_completo']) ?>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= site_url('consultor/auditorias') ?>" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Continuar al Paso 2 <i class="bi bi-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
