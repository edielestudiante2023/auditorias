<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-lock-fill"></i> Gestión de Auditorías Cerradas</h2>
    <div class="d-flex align-items-center gap-2">
        <?= view('partials/filtro_anio', ['anio_actual' => $anio ?? date('Y'), 'url_base' => site_url('admin/auditorias/cerradas')]) ?>
        <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?= view('partials/flash') ?>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4" id="cerradasTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="auditorias-tab" data-bs-toggle="tab" data-bs-target="#auditorias" type="button" role="tab">
            <i class="bi bi-list-check"></i> Auditorías Cerradas
            <span class="badge bg-secondary ms-1"><?= count($auditorias) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab">
            <i class="bi bi-clock-history"></i> Historial de Reaperturas
            <span class="badge bg-info ms-1"><?= count($historial) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content" id="cerradasTabContent">
    <!-- Tab: Auditorías Cerradas -->
    <div class="tab-pane fade show active" id="auditorias" role="tabpanel">
        <?php if (empty($auditorias)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">No hay auditorías cerradas</h5>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaCerradas" class="table table-hover align-middle" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Proveedor</th>
                                    <th>Consultor</th>
                                    <th>Clientes</th>
                                    <th>Fecha Cierre</th>
                                    <th>% Cumplimiento</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($auditorias as $auditoria): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($auditoria['codigo_formato'] ?? 'N/A') ?></strong>
                                            <br>
                                            <small class="text-muted">v<?= esc($auditoria['version_formato'] ?? '1.0') ?></small>
                                        </td>
                                        <td>
                                            <?= esc($auditoria['proveedor_nombre']) ?>
                                            <br>
                                            <small class="text-muted">NIT: <?= esc($auditoria['proveedor_nit']) ?></small>
                                        </td>
                                        <td>
                                            <?= esc($auditoria['consultor_nombre']) ?>
                                            <br>
                                            <small class="text-muted"><?= esc($auditoria['consultor_email'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= $auditoria['total_clientes'] ?> cliente(s)
                                            </span>
                                        </td>
                                        <td data-order="<?= !empty($auditoria['updated_at']) ? strtotime($auditoria['updated_at']) : 0 ?>">
                                            <?php
                                            if (!empty($auditoria['updated_at'])) {
                                                $fecha = new DateTime($auditoria['updated_at']);
                                                echo $fecha->format('d/m/Y H:i');
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                        <td data-order="<?= $auditoria['porcentaje_cumplimiento'] ?? 0 ?>">
                                            <?php
                                            $porcentaje = $auditoria['porcentaje_cumplimiento'] ?? 0;
                                            $badgeClass = 'secondary';
                                            if ($porcentaje >= 90) {
                                                $badgeClass = 'success';
                                            } elseif ($porcentaje >= 70) {
                                                $badgeClass = 'info';
                                            } elseif ($porcentaje >= 50) {
                                                $badgeClass = 'warning';
                                            } else {
                                                $badgeClass = 'danger';
                                            }
                                            ?>
                                            <span class="badge bg-<?= $badgeClass ?>"><?= number_format($porcentaje, 1) ?>%</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="mostrarModalReabrir(<?= $auditoria['id_auditoria'] ?>, '<?= esc($auditoria['codigo_formato']) ?>')">
                                                <i class="bi bi-unlock"></i> Reabrir
                                            </button>
                                            <a href="<?= site_url('admin/auditorias/' . $auditoria['id_auditoria'] . '/adicionar-clientes') ?>" class="btn btn-sm btn-info" title="Adicionar clientes">
                                                <i class="bi bi-person-plus"></i> Adicionar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab: Historial de Reaperturas -->
    <div class="tab-pane fade" id="historial" role="tabpanel">
        <?php if (empty($historial)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-clock-history text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">No hay reaperturas registradas</h5>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Auditoría</th>
                                    <th>Usuario</th>
                                    <th>Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial as $log): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            if (!empty($log['created_at'])) {
                                                $fecha = new DateTime($log['created_at']);
                                                echo $fecha->format('d/m/Y H:i');
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <strong><?= esc($log['codigo_formato']) ?></strong>
                                        </td>
                                        <td>
                                            <?= esc($log['usuario_nombre']) ?>
                                            <br>
                                            <small class="text-muted"><?= esc($log['usuario_email']) ?></small>
                                        </td>
                                        <td>
                                            <?= esc($log['detalle'] ?? 'Sin detalles') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Reabrir Auditoría -->
<div class="modal fade" id="modalReabrir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-unlock"></i> Reabrir Auditoría
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea reabrir la auditoría <strong id="codigoReabrir"></strong>?</p>

                <div class="mb-3">
                    <label class="form-label">¿Quién debe actuar primero? <span class="text-danger">*</span></label>
                    <div class="d-grid gap-2">
                        <div class="form-check border rounded p-3">
                            <input class="form-check-input" type="radio" name="destinoReabrir" id="destinoProveedor" value="proveedor">
                            <label class="form-check-label w-100" for="destinoProveedor">
                                <i class="bi bi-building text-primary"></i> <strong>Proveedor primero</strong>
                                <small class="d-block text-muted mt-1">
                                    <i class="bi bi-1-circle-fill text-primary"></i> Proveedor sube documentos
                                    <i class="bi bi-arrow-right mx-1"></i>
                                    <i class="bi bi-2-circle-fill text-success"></i> Luego pasa a Consultor para revisión
                                </small>
                            </label>
                        </div>
                        <div class="form-check border rounded p-3">
                            <input class="form-check-input" type="radio" name="destinoReabrir" id="destinoConsultor" value="consultor" checked>
                            <label class="form-check-label w-100" for="destinoConsultor">
                                <i class="bi bi-person-badge text-success"></i> <strong>Consultor directamente</strong>
                                <small class="d-block text-muted mt-1">
                                    El consultor revisa y recalifica sin nuevos documentos del proveedor
                                </small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="motivoReabrir" class="form-label">Motivo de reapertura <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="motivoReabrir" rows="3" placeholder="Ingrese el motivo por el cual reabre esta auditoría..." required></textarea>
                    <div class="form-text">Este motivo quedará registrado en el historial.</div>
                </div>

                <div class="alert alert-info mb-0" id="alertaDestino">
                    <i class="bi bi-info-circle"></i>
                    <span id="textoDestino">La auditoría será enviada al consultor para revisión.</span>
                </div>

                <input type="hidden" id="idAuditoriaReabrir">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarReabrir()">
                    <i class="bi bi-unlock"></i> Reabrir Auditoría
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!-- jQuery (requerido por DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#tablaCerradas').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        order: [[4, 'desc']],
        pageLength: 25
    });
});

function mostrarModalReabrir(idAuditoria, codigo) {
    $('#idAuditoriaReabrir').val(idAuditoria);
    $('#codigoReabrir').text(codigo);
    $('#motivoReabrir').val('');
    $('#destinoConsultor').prop('checked', true);
    actualizarTextoDestino();
    $('#modalReabrir').modal('show');
}

// Actualizar texto según destino seleccionado
$('input[name="destinoReabrir"]').on('change', function() {
    actualizarTextoDestino();
});

function actualizarTextoDestino() {
    const destino = $('input[name="destinoReabrir"]:checked').val();
    if (destino === 'proveedor') {
        $('#textoDestino').html('<i class="bi bi-arrow-right-circle"></i> <strong>Flujo:</strong> Proveedor sube documentos → Luego el Consultor revisa y califica');
        $('#alertaDestino').removeClass('alert-info').addClass('alert-primary');
    } else {
        $('#textoDestino').html('<i class="bi bi-person-badge"></i> <strong>Directo:</strong> El Consultor revisa/recalifica con las evidencias existentes');
        $('#alertaDestino').removeClass('alert-primary').addClass('alert-info');
    }
}

function confirmarReabrir() {
    const idAuditoria = $('#idAuditoriaReabrir').val();
    const motivo = $('#motivoReabrir').val().trim();
    const destino = $('input[name="destinoReabrir"]:checked').val();

    if (!motivo) {
        alert('Debe ingresar un motivo para reabrir la auditoría');
        return;
    }

    $.ajax({
        url: '<?= site_url('admin/auditorias') ?>/' + idAuditoria + '/reabrir',
        type: 'POST',
        data: {
            motivo: motivo,
            destino: destino,
            <?= csrf_token() ?>: '<?= csrf_hash() ?>'
        },
        success: function(response) {
            if (response.success) {
                $('#modalReabrir').modal('hide');

                // Construir opciones según destino
                let opcionesHtml = '';
                if (response.destino === 'proveedor') {
                    opcionesHtml = `
                        <a href="<?= site_url('admin/auditorias') ?>/${idAuditoria}/reenviar-email" class="btn btn-primary">
                            <i class="bi bi-envelope"></i> Enviar Email al Proveedor
                        </a>
                        <a href="<?= site_url('admin/auditorias') ?>/${idAuditoria}/adicionar-clientes" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus"></i> Adicionar Clientes
                        </a>
                    `;
                } else {
                    opcionesHtml = `
                        <a href="<?= site_url('admin/auditorias') ?>/${idAuditoria}/adicionar-clientes" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Adicionar Clientes
                        </a>
                    `;
                }

                // Mostrar modal con opciones
                const modalHtml = `
                    <div class="modal fade" id="modalReabiertaExito" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">
                                        <i class="bi bi-check-circle"></i> Auditoría Reabierta
                                    </h5>
                                </div>
                                <div class="modal-body">
                                    <p><strong>${response.message}</strong></p>
                                    <p class="text-muted">¿Qué deseas hacer ahora?</p>
                                    <div class="d-grid gap-2">
                                        ${opcionesHtml}
                                        <button class="btn btn-secondary" onclick="location.reload()">
                                            <i class="bi bi-arrow-clockwise"></i> Volver a la Lista
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('body').append(modalHtml);
                $('#modalReabiertaExito').modal('show');

                // Limpiar modal después de cerrar
                $('#modalReabiertaExito').on('hidden.bs.modal', function() {
                    $(this).remove();
                });
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Error al reabrir la auditoría');
        }
    });
}
</script>
<?= $this->endSection() ?>
