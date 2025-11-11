<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-person-plus"></i> Adicionar Clientes a Auditoría</h2>
        <p class="text-muted mb-0">
            Auditoría: <strong><?= esc($auditoria['codigo_formato']) ?></strong> v<?= esc($auditoria['version_formato']) ?>
            | Proveedor: <strong><?= esc($auditoria['proveedor_nombre']) ?></strong>
        </p>
    </div>
    <a href="<?= site_url('admin/auditorias/cerradas') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<!-- Información de la auditoría -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-info-circle"></i> Información de la Auditoría
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Estado:</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-<?= $auditoria['estado'] === 'cerrada' ? 'success' : 'warning' ?>">
                            <?= ucfirst($auditoria['estado']) ?>
                        </span>
                    </dd>

                    <dt class="col-sm-4">Consultor:</dt>
                    <dd class="col-sm-8"><?= esc($auditoria['consultor_nombre']) ?></dd>

                    <dt class="col-sm-4">Fecha envío:</dt>
                    <dd class="col-sm-8">
                        <?php
                        if (!empty($auditoria['fecha_envio_proveedor'])) {
                            $fecha = new DateTime($auditoria['fecha_envio_proveedor']);
                            echo $fecha->format('d/m/Y H:i');
                        } else {
                            echo 'No enviada';
                        }
                        ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <i class="bi bi-people"></i> Clientes Actuales
            </div>
            <div class="card-body">
                <?php if (empty($auditoria['clientes'])): ?>
                    <p class="text-muted mb-0">Sin clientes asignados</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($auditoria['clientes'] as $cliente): ?>
                            <li class="mb-1">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <?= esc($cliente['razon_social']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Formulario: Seleccionar nuevos clientes -->
<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <i class="bi bi-person-plus-fill"></i> Seleccionar Clientes para Adicionar
    </div>
    <div class="card-body">
        <?php if (empty($clientesDisponibles)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>No hay clientes disponibles para adicionar.</strong>
                <p class="mb-0">Todos los clientes del proveedor ya están asignados a esta auditoría.</p>
            </div>
        <?php else: ?>
            <form id="formAdicionarClientes">
                <p class="text-muted">Seleccione los clientes que desea adicionar a esta auditoría:</p>

                <div class="row">
                    <?php foreach ($clientesDisponibles as $cliente): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="clientes[]"
                                               value="<?= $cliente['id_cliente'] ?>"
                                               id="cliente_<?= $cliente['id_cliente'] ?>">
                                        <label class="form-check-label" for="cliente_<?= $cliente['id_cliente'] ?>">
                                            <strong><?= esc($cliente['razon_social']) ?></strong>
                                            <br>
                                            <small class="text-muted">NIT: <?= esc($cliente['nit']) ?></small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary" onclick="seleccionarTodos()">
                        <i class="bi bi-check-all"></i> Seleccionar Todos
                    </button>
                    <button type="button" class="btn btn-success" onclick="adicionarClientes()">
                        <i class="bi bi-person-plus"></i> Adicionar Clientes Seleccionados
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Alerta sobre reenvío de credenciales -->
<?php if (!empty($auditoria['fecha_envio_proveedor'])): ?>
    <div class="alert alert-warning mt-3 d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Nota importante:</strong> Esta auditoría ya fue enviada al proveedor.
            Después de adicionar clientes, deberás reenviar las credenciales de acceso actualizadas.
        </div>
        <button type="button" class="btn btn-warning" onclick="reenviarCredenciales()">
            <i class="bi bi-envelope-fill"></i> Reenviar Credenciales
        </button>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
function seleccionarTodos() {
    const checkboxes = document.querySelectorAll('input[name="clientes[]"]');
    const algunoMarcado = Array.from(checkboxes).some(cb => cb.checked);

    checkboxes.forEach(cb => {
        cb.checked = !algunoMarcado;
    });
}

function adicionarClientes() {
    const checkboxes = document.querySelectorAll('input[name="clientes[]"]:checked');

    if (checkboxes.length === 0) {
        alert('Debes seleccionar al menos un cliente');
        return;
    }

    const clientesIds = Array.from(checkboxes).map(cb => cb.value);

    if (!confirm(`¿Confirmas adicionar ${clientesIds.length} cliente(s) a esta auditoría?`)) {
        return;
    }

    $.ajax({
        url: '<?= site_url('admin/auditorias/' . $auditoria['id_auditoria'] . '/adicionar-clientes') ?>',
        type: 'POST',
        data: {
            clientes: clientesIds,
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error al adicionar clientes');
        }
    });
}

function reenviarCredenciales() {
    if (!confirm('¿Confirmas reenviar las credenciales al proveedor?\n\nSe generará una nueva contraseña y se enviará por correo electrónico.')) {
        return;
    }

    $.ajax({
        url: '<?= site_url('admin/auditorias/' . $auditoria['id_auditoria'] . '/reenviar-credenciales') ?>',
        type: 'POST',
        data: {
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error al reenviar credenciales');
        }
    });
}
</script>
<?= $this->endSection() ?>
