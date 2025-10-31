<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building"></i> Mi Empresa</h2>
    <a href="<?= site_url('proveedor/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<!-- Información del Proveedor -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-warning text-dark">
        <!-- Cambio de ícono: reemplazado bi-truck por bi-shop para representar alianza con proveedores -->
        <h5 class="mb-0"><i class="bi bi-shop"></i> Información del Proveedor</h5>
    </div>
    <div class="card-body">
        <?php foreach ($proveedores as $index => $proveedor): ?>
            <?php if ($index > 0): ?>
                <hr class="my-4">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Razón Social:</dt>
                        <dd class="col-sm-7"><?= esc($proveedor['razon_social']) ?></dd>

                        <dt class="col-sm-5">NIT:</dt>
                        <dd class="col-sm-7"><?= esc($proveedor['nit']) ?></dd>

                        <dt class="col-sm-5">Teléfono:</dt>
                        <dd class="col-sm-7"><?= esc($proveedor['telefono_contacto'] ?? 'No registrado') ?></dd>

                        <dt class="col-sm-5">Email:</dt>
                        <dd class="col-sm-7"><?= esc($proveedor['email_contacto'] ?? 'No registrado') ?></dd>

                        <dt class="col-sm-5">Fecha Registro:</dt>
                        <dd class="col-sm-7">
                            <?php
                            if (!empty($proveedor['created_at'])) {
                                $fecha = new DateTime($proveedor['created_at']);
                                echo $fecha->format('d/m/Y');
                            } else {
                                echo 'No registrada';
                            }
                            ?>
                        </dd>
                    </dl>
                </div>

                <div class="col-md-6">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Responsable:</dt>
                        <dd class="col-sm-7"><?= esc($proveedor['responsable_nombre'] ?? 'No registrado') ?></dd>

                        <dt class="col-sm-5">Cargo:</dt>
                        <dd class="col-sm-7"><?= esc($proveedor['responsable_cargo'] ?? 'No registrado') ?></dd>

                        <dt class="col-sm-5">Email Responsable:</dt>
                        <dd class="col-sm-7"><?= esc($proveedor['responsable_email'] ?? 'No registrado') ?></dd>

                        <dt class="col-sm-5">Teléfono Responsable:</dt>
                        <dd class="col-sm-7"><?= esc($proveedor['responsable_telefono'] ?? 'No registrado') ?></dd>
                    </dl>
                </div>
            </div>

            <?php if (!empty($proveedor['observaciones'])): ?>
                <div class="mt-3">
                    <strong>Observaciones:</strong>
                    <p class="text-muted mb-0"><?= nl2br(esc($proveedor['observaciones'])) ?></p>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<!-- Clientes Asociados -->
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-building-check"></i> Clientes Asociados (<?= count($clientes) ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($clientes)): ?>
            <div class="text-center py-4">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3">No hay clientes asociados actualmente.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Razón Social</th>
                            <th>NIT</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Proveedor</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($cliente['razon_social']) ?></strong>
                                </td>
                                <td><?= esc($cliente['nit']) ?></td>
                                <td>
                                    <?php if (!empty($cliente['email_contacto'])): ?>
                                        <a href="mailto:<?= esc($cliente['email_contacto']) ?>"><?= esc($cliente['email_contacto']) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">No registrado</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($cliente['telefono_contacto'] ?? 'No registrado') ?></td>
                                <td>
                                    <small class="text-muted"><?= esc($cliente['proveedor_nombre']) ?></small>
                                </td>
                                <td>
                                    <?php
                                    $estado = $cliente['estado'] ?? 'activo';
                                    $badgeClass = $estado === 'activo' ? 'success' : 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($estado) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-3 text-muted">
    <small>
        <i class="bi bi-info-circle"></i>
        Esta información es de solo lectura. Para actualizarla, contacte al administrador del sistema.
    </small>
</div>

<?= $this->endSection() ?>
