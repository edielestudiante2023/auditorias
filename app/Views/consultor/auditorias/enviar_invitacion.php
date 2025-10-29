<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2><i class="bi bi-envelope"></i> Enviar Invitación - Paso 3 de 3</h2>
    <p class="text-muted">
        Revise la información y envíe la invitación al proveedor
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
                <div class="mt-2 small"><strong>Paso 2</strong><br>Asignar Clientes</div>
            </div>
            <div style="flex: 1; height: 2px; background-color: #28a745;"></div>
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    3
                </div>
                <div class="mt-2 small"><strong>Paso 3</strong><br>Enviar Invitación</div>
            </div>
        </div>
    </div>
</div>

<!-- Validación de clientes asignados -->
<?php if (empty($clientes)): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>No hay clientes asignados</strong>
        <br>
        Debe asignar al menos un cliente antes de enviar la invitación.
        <br><br>
        <a href="<?= site_url('consultor/auditorias/' . $auditoria['id_auditoria'] . '/asignar-clientes-setup') ?>"
           class="btn btn-warning">
            <i class="bi bi-arrow-left"></i> Volver al Paso 2
        </a>
    </div>
<?php else: ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-people"></i> Clientes Asignados
            </div>
            <div class="card-body">
                <p class="mb-3">
                    <strong><?= count($clientes) ?></strong> cliente(s) recibirán el informe de esta auditoría:
                </p>
                <ul>
                    <?php foreach ($clientes as $cliente): ?>
                        <li>
                            <strong><?= esc($cliente['razon_social']) ?></strong>
                            <small class="text-muted">(NIT: <?= esc($cliente['nit']) ?>)</small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-envelope"></i> Información de Invitación
            </div>
            <div class="card-body">

                <div class="alert alert-warning mb-4">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>IMPORTANTE:</strong> La invitación se enviará al <strong>Usuario Responsable del Proveedor</strong>
                    (Analista SST o Coordinador SG-SST) asignado en la Relación Cliente-Proveedor,
                    <strong>NO</strong> al email genérico del proveedor.
                </div>

                <!-- Información del Usuario Responsable -->
                <dl class="row mb-4">
                    <dt class="col-sm-4">Proveedor:</dt>
                    <dd class="col-sm-8">
                        <strong><?= esc($proveedor['razon_social']) ?></strong>
                        <br><small class="text-muted">NIT: <?= esc($proveedor['nit']) ?></small>
                    </dd>

                    <dt class="col-sm-4">Responsable:</dt>
                    <dd class="col-sm-8">
                        <?php if ($usuario): ?>
                            <i class="bi bi-person-circle text-success"></i>
                            <strong><?= esc($usuario['nombre']) ?></strong>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-envelope-fill"></i> <?= esc($usuario['email']) ?>
                            </small>
                        <?php else: ?>
                            <span class="text-danger">
                                <i class="bi bi-exclamation-triangle"></i>
                                Sin usuario responsable asignado
                            </span>
                        <?php endif; ?>
                    </dd>
                </dl>

                <?php if (!$usuario): ?>
                    <!-- No se puede enviar sin usuario responsable -->
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>No se puede enviar invitación</strong>
                        <br>
                        No hay un usuario responsable asignado en la Relación Cliente-Proveedor.
                        Por favor, asigne un usuario responsable antes de enviar la invitación.
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= site_url('consultor/auditorias') ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver a Mis Auditorías
                        </a>
                        <a href="<?= site_url('admin/contratos') ?>"
                           class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Ir a Relaciones Cliente-Proveedor
                        </a>
                    </div>

                <?php else: ?>
                    <!-- Formulario para enviar -->
                    <div class="alert alert-info">
                        <strong><i class="bi bi-info-circle"></i> ¿Qué sucederá al enviar?</strong>
                        <ul class="mb-0 mt-2">
                            <li>Se crearán automáticamente los ítems según su alcance:
                                <ul>
                                    <li><strong>Ítems globales:</strong> aplicables al proveedor en general</li>
                                    <li><strong>Ítems por cliente:</strong> uno por cada cliente asignado</li>
                                </ul>
                            </li>
                            <li>Se enviará un email a <strong><?= esc($usuario['nombre']) ?> (<?= esc($usuario['email']) ?>)</strong></li>
                            <li>El email incluirá un link directo a la auditoría</li>
                            <li>El usuario responsable podrá ingresar al sistema y completar los ítems</li>
                        </ul>
                    </div>

                    <form action="<?= site_url('consultor/auditorias/' . $auditoria['id_auditoria'] . '/enviar-invitacion') ?>"
                          method="POST"
                          onsubmit="return confirm('¿Está seguro de enviar la invitación al proveedor?');">
                        <?= csrf_field() ?>

                        <div class="d-flex justify-content-between">
                            <a href="<?= site_url('consultor/auditorias') ?>" class="btn btn-outline-secondary">
                                Enviar Después
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-send"></i> Enviar Invitación
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
