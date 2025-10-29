<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-vcard"></i> Mi Perfil</h2>
    <a href="<?= site_url('consultor/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<div class="row">
    <!-- Información Personal -->
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Información Personal</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?= site_url('consultor/perfil/actualizar') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label"><strong>Nombre Completo</strong> <span class="text-danger">*</span></label>
                        <input type="text" name="nombre_completo" class="form-control"
                               value="<?= esc($consultor['nombre_completo']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Email</strong> <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= esc($consultor['email']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Teléfono</strong></label>
                            <input type="text" name="telefono" class="form-control"
                                   value="<?= esc($consultor['telefono'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Licencia SST</strong></label>
                        <input type="text" name="licencia_sst" class="form-control"
                               value="<?= esc($consultor['licencia_sst'] ?? '') ?>"
                               placeholder="Ej: SST-12345-2024">
                        <small class="text-muted">Número de licencia en Seguridad y Salud en el Trabajo</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Firma Digital</strong></label>
                        <?php if (!empty($consultor['firma_path'])): ?>
                            <div class="mb-2">
                                <img src="<?= site_url('files/firma/' . basename($consultor['firma_path'])) ?>"
                                     alt="Firma actual"
                                     class="img-thumbnail"
                                     style="max-height: 100px;">
                                <p class="text-muted mb-0"><small>Firma actual</small></p>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="firma" class="form-control" accept="image/*">
                        <small class="text-muted">Formatos permitidos: PNG, JPG, JPEG. Máximo 2MB. Dejar vacío para mantener firma actual.</small>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Los cambios se guardarán inmediatamente
                        </small>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Información de la Cuenta -->
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Cuenta de Usuario</h5>
            </div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt>Usuario:</dt>
                    <dd><?= esc($usuario['username'] ?? $consultor['email']) ?></dd>

                    <dt>Rol:</dt>
                    <dd>
                        <span class="badge bg-success">Consultor</span>
                    </dd>

                    <dt>Fecha de Registro:</dt>
                    <dd>
                        <?php
                        if (!empty($consultor['created_at'])) {
                            $fecha = new DateTime($consultor['created_at']);
                            echo $fecha->format('d/m/Y');
                        } else {
                            echo 'No disponible';
                        }
                        ?>
                    </dd>

                    <dt>Estado:</dt>
                    <dd>
                        <?php
                        $estado = $usuario['estado'] ?? 'activo';
                        $badgeClass = $estado === 'activo' ? 'success' : 'secondary';
                        ?>
                        <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($estado) ?></span>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm border-warning">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-exclamation-triangle text-warning"></i> Cambiar Contraseña</h6>
                <p class="card-text small text-muted">
                    Para cambiar tu contraseña, contacta al administrador del sistema.
                </p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
