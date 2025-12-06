<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?> | Auditorías</title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
  <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
  <link rel="apple-touch-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= site_url('consultor/dashboard') ?>">
      <i class="bi bi-clipboard-check"></i> Auditorías - Consultor
    </a>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item">
        <a class="nav-link" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Salir</a>
      </li>
    </ul>
  </div>
</nav>

<div class="container-fluid py-4">
  <!-- Breadcrumbs -->
  <?= view('partials/breadcrumbs', [
      'breadcrumbs' => [
          ['title' => 'Dashboard', 'url' => 'consultor/dashboard'],
          ['title' => 'Mis Auditorías', 'url' => null]
      ]
  ]) ?>

  <!-- Flash Messages -->
  <?= view('partials/flash') ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-list-ul"></i> Mis Auditorías</h4>
    <div class="d-flex align-items-center gap-2">
      <?= view('partials/filtro_anio', ['anio_actual' => $anio ?? date('Y'), 'url_base' => site_url('consultor/auditorias')]) ?>
      <a href="<?= site_url('consultor/auditorias/crear') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nueva Auditoría
      </a>
    </div>
  </div>

  <?php if (empty($auditorias)): ?>
    <div class="text-center py-5">
      <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
      <p class="text-muted mt-3">No tienes auditorías asignadas</p>
    </div>
  <?php else: ?>
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table id="tablaAuditorias" class="table table-hover table-striped align-middle">
            <thead class="table-success">
              <tr>
                <th>#</th>
                <th>Proveedor</th>
                <th>Fecha Creación</th>
                <th>Fecha Vencimiento</th>
                <th>Estado</th>
                <th>Calificación</th>
                <th>Acciones</th>
              </tr>
              <tr class="filters">
                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Buscar..."></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($auditorias as $aud): ?>
                <tr>
                  <td><strong>#<?= esc($aud['id_auditoria']) ?></strong></td>
                  <td>
                    <strong><?= esc($aud['proveedor_nombre']) ?></strong>
                    <?php if (!empty($aud['proveedor_nit'])): ?>
                      <br><small class="text-muted">NIT: <?= esc($aud['proveedor_nit']) ?></small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?= formatoFechaSolo($aud['created_at']) ?>
                    <br><small class="text-muted"><?= tiempoRelativo($aud['created_at']) ?></small>
                  </td>
                  <td>
                    <?php if (!empty($aud['fecha_programada'])): ?>
                      <?= formatoFechaSolo($aud['fecha_programada']) ?>
                      <br><small class="text-muted"><?= tiempoRelativo($aud['fecha_programada']) ?></small>
                    <?php else: ?>
                      <span class="text-muted">No establecida</span>
                    <?php endif; ?>
                  </td>
                  <td><?= estadoBadge($aud['estado']) ?></td>
                  <td>
                    <?php
                    $porcentaje = $aud['porcentaje_cumplimiento'] ?? 0;
                    ?>
                    <?= progressBar($porcentaje, true, '25px') ?>
                  </td>
                  <td>
                <div class="btn-group btn-group-sm" role="group">
                  <?php if ($aud['estado'] === 'en_revision_consultor' || $aud['estado'] === 'cerrada'): ?>
                    <!-- Auditoría lista para revisar o ya cerrada -->
                    <a href="<?= site_url('consultor/auditoria/' . $aud['id_auditoria']) ?>"
                       class="btn btn-success"
                       title="Revisar auditoría">
                      <i class="bi bi-eye"></i> Revisar
                    </a>
                  <?php elseif ($aud['estado'] === 'en_proveedor'): ?>
                    <!-- Auditoría en manos del proveedor -->
                    <a href="<?= site_url('consultor/auditoria/' . $aud['id_auditoria']) ?>"
                       class="btn btn-outline-secondary"
                       title="Ver auditoría">
                      <i class="bi bi-eye"></i> Ver
                    </a>
                    <a href="<?= site_url('consultor/auditorias/' . $aud['id_auditoria'] . '/editar') ?>"
                       class="btn btn-primary"
                       title="Editar fecha de vencimiento">
                      <i class="bi bi-pencil"></i> Editar
                    </a>
                    <button type="button"
                            class="btn btn-warning"
                            onclick="reenviarEmail(<?= $aud['id_auditoria'] ?>)"
                            title="Reenviar invitación por email al proveedor">
                      <i class="bi bi-envelope-arrow-up"></i> Reenviar Email
                    </button>
                    <button type="button"
                            class="btn btn-danger"
                            onclick="eliminarAuditoria(<?= $aud['id_auditoria'] ?>, '<?= esc($aud['proveedor_nombre']) ?>')"
                            title="Eliminar auditoría">
                      <i class="bi bi-trash"></i>
                    </button>
                  <?php else: ?>
                    <!-- Estado borrador u otro -->
                    <a href="<?= site_url('consultor/auditoria/' . $aud['id_auditoria']) ?>"
                       class="btn btn-outline-primary"
                       title="Ver auditoría">
                      <i class="bi bi-eye"></i> Ver
                    </a>
                  <?php endif; ?>

                  <?php if ($aud['estado'] === 'borrador'): ?>
                    <!-- Solo permitir editar ítems en borrador -->
                    <a href="<?= site_url('consultor/auditorias/' . $aud['id_auditoria'] . '/seleccionar-items') ?>"
                       class="btn btn-primary"
                       title="Editar ítems de la auditoría">
                      <i class="bi bi-list-check"></i> Ítems
                    </a>
                    <button type="button"
                            class="btn btn-danger"
                            onclick="eliminarAuditoria(<?= $aud['id_auditoria'] ?>, '<?= esc($aud['proveedor_nombre']) ?>')"
                            title="Eliminar auditoría">
                      <i class="bi bi-trash"></i>
                    </button>
                  <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Resumen de Estadísticas -->
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title text-muted mb-3">
              <i class="bi bi-graph-up"></i> Resumen
            </h6>
            <div class="row text-center">
              <?php
              $total = count($auditorias);
              $cerradas = count(array_filter($auditorias, fn($a) => $a['estado'] === 'cerrada'));
              $enRevision = count(array_filter($auditorias, fn($a) => $a['estado'] === 'en_revision_consultor'));
              $enProgreso = count(array_filter($auditorias, fn($a) => $a['estado'] === 'en_progreso'));
              ?>
              <div class="col-md-3">
                <div class="border-end">
                  <h3 class="text-primary mb-0"><?= $total ?></h3>
                  <small class="text-muted">Total</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="border-end">
                  <h3 class="text-success mb-0"><?= $cerradas ?></h3>
                  <small class="text-muted">Cerradas</small>
                </div>
              </div>
              <div class="col-md-3">
                <div class="border-end">
                  <h3 class="text-info mb-0"><?= $enRevision ?></h3>
                  <small class="text-muted">En Revisión</small>
                </div>
              </div>
              <div class="col-md-3">
                <h3 class="text-warning mb-0"><?= $enProgreso ?></h3>
                <small class="text-muted">En Progreso</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#tablaAuditorias').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="bi bi-file-earmark-excel"></i> Exportar a Excel',
                className: 'btn btn-success btn-sm',
                title: 'Mis Auditorías',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5]
                }
            }
        ],
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 25,
        order: [[0, 'desc']],
        responsive: true,
        initComplete: function() {
            var api = this.api();

            api.columns().eq(0).each(function(colIdx) {
                var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                var input = $('input', cell);

                input.off('keyup change').on('keyup change', function() {
                    api.column(colIdx).search(this.value).draw();
                });
            });
        }
    });
});

function reenviarEmail(idAuditoria) {
    if (confirm('¿Estás seguro de reenviar la invitación por email al proveedor?')) {
        // Mostrar loading en el botón
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';

        // Hacer petición AJAX
        fetch('<?= site_url('consultor/auditorias/') ?>' + idAuditoria + '/reenviar-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;

            if (data.ok) {
                const mensaje = data.email
                    ? `✓ Email reenviado exitosamente a:\n\n${data.nombre}\n${data.email}`
                    : '✓ Email reenviado exitosamente al proveedor';
                alert(mensaje);
            } else {
                alert('✗ Error al reenviar email: ' + (data.error || data.message));
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            alert('✗ Error de conexión: ' + error.message);
            console.error('Error:', error);
        });
    }
}

function eliminarAuditoria(idAuditoria, nombreProveedor) {
    if (confirm(`¿Estás seguro de eliminar la auditoría del proveedor "${nombreProveedor}"?\n\nEsta acción eliminará:\n- La auditoría\n- Todos los items asociados\n- Todas las evidencias\n- Todos los clientes asignados\n\nEsta acción NO se puede deshacer.`)) {
        // Mostrar loading en el botón
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        // Crear FormData para enviar el token CSRF correctamente
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        // Hacer petición AJAX
        fetch('<?= site_url('consultor/auditorias/') ?>' + idAuditoria + '/eliminar', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Auditoría eliminada exitosamente');
                location.reload();
            } else {
                alert('✗ Error: ' + (data.message || 'No se pudo eliminar la auditoría'));
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            alert('✗ Error de conexión: ' + error.message);
            console.error('Error:', error);
        });
    }
}
</script>
</body>
</html>
