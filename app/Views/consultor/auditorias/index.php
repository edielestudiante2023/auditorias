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
      <?= view('partials/filtro_anio', [
          'anio_actual' => $anio ?? date('Y'),
          'url_base' => site_url('consultor/auditorias'),
          'auditorias_por_anio' => $auditoriasPorAnio ?? []
      ]) ?>
      <a href="<?= site_url('consultor/auditorias/crear') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nueva Auditoría
      </a>
    </div>
  </div>

  <?php
  // Calcular estadísticas por estado
  $total = count($auditorias ?? []);
  $borradores = count(array_filter($auditorias ?? [], fn($a) => $a['estado'] === 'borrador'));
  $enProveedor = count(array_filter($auditorias ?? [], fn($a) => $a['estado'] === 'en_proveedor'));
  $enRevision = count(array_filter($auditorias ?? [], fn($a) => $a['estado'] === 'en_revision_consultor'));
  $cerradas = count(array_filter($auditorias ?? [], fn($a) => $a['estado'] === 'cerrada'));
  ?>

  <!-- Tarjetas de filtrado por estado -->
  <div class="row mb-4">
    <div class="col">
      <div class="d-flex flex-wrap gap-2 justify-content-center">
        <!-- Todas -->
        <div class="card filter-card active" data-filter="todos" style="cursor: pointer; min-width: 140px; transition: all 0.2s;">
          <div class="card-body text-center py-3 px-3">
            <i class="bi bi-grid-3x3-gap-fill text-primary fs-4"></i>
            <h4 class="mb-0 mt-1"><?= $total ?></h4>
            <small class="text-muted">Todas</small>
          </div>
        </div>

        <!-- Borradores -->
        <div class="card filter-card" data-filter="borrador" style="cursor: pointer; min-width: 140px; transition: all 0.2s;">
          <div class="card-body text-center py-3 px-3">
            <i class="bi bi-pencil-square text-secondary fs-4"></i>
            <h4 class="mb-0 mt-1"><?= $borradores ?></h4>
            <small class="text-muted">Borradores</small>
          </div>
        </div>

        <!-- En Proveedor -->
        <div class="card filter-card" data-filter="en_proveedor" style="cursor: pointer; min-width: 140px; transition: all 0.2s;">
          <div class="card-body text-center py-3 px-3">
            <i class="bi bi-send-fill text-info fs-4"></i>
            <h4 class="mb-0 mt-1"><?= $enProveedor ?></h4>
            <small class="text-muted">En Proveedor</small>
          </div>
        </div>

        <!-- Pendientes de Revisión -->
        <div class="card filter-card" data-filter="en_revision_consultor" style="cursor: pointer; min-width: 140px; transition: all 0.2s;">
          <div class="card-body text-center py-3 px-3">
            <i class="bi bi-clock-history text-warning fs-4"></i>
            <h4 class="mb-0 mt-1"><?= $enRevision ?></h4>
            <small class="text-muted">Por Revisar</small>
          </div>
        </div>

        <!-- Cerradas -->
        <div class="card filter-card" data-filter="cerrada" style="cursor: pointer; min-width: 140px; transition: all 0.2s;">
          <div class="card-body text-center py-3 px-3">
            <i class="bi bi-check-circle-fill text-success fs-4"></i>
            <h4 class="mb-0 mt-1"><?= $cerradas ?></h4>
            <small class="text-muted">Cerradas</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
    .filter-card {
      border: 2px solid transparent;
      opacity: 0.7;
    }
    .filter-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      opacity: 1;
    }
    .filter-card.active {
      border-color: #0d6efd;
      opacity: 1;
      box-shadow: 0 2px 8px rgba(13,110,253,0.25);
    }
  </style>

  <?php if (empty($auditorias)): ?>
    <div class="text-center py-5">
      <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
      <?php
      $auditoriasPorAnio = $auditoriasPorAnio ?? [];
      $hayEnOtrosAnios = !empty($auditoriasPorAnio);
      $anioActual = $anio ?? date('Y');
      ?>

      <?php if ($hayEnOtrosAnios && $anioActual !== 'todos'): ?>
          <!-- Hay auditorías pero en otros años -->
          <p class="text-muted mt-3 fs-5">No tienes auditorías en <strong><?= $anioActual ?></strong></p>
          <div class="alert alert-info d-inline-block mt-3">
              <i class="bi bi-lightbulb"></i>
              <strong>Tienes auditorías en otros años:</strong>
              <div class="mt-2">
                  <?php foreach ($auditoriasPorAnio as $year => $cantidad): ?>
                      <a href="<?= site_url('consultor/auditorias?anio=' . $year) ?>"
                         class="btn btn-sm <?= $cantidad > 0 ? 'btn-success' : 'btn-outline-secondary' ?> m-1">
                          <i class="bi bi-calendar"></i> <?= $year ?>
                          <span class="badge bg-light text-dark"><?= $cantidad ?></span>
                      </a>
                  <?php endforeach; ?>
              </div>
          </div>
      <?php else: ?>
          <!-- No hay auditorías en ningún año -->
          <p class="text-muted mt-3">No tienes auditorías asignadas</p>
      <?php endif; ?>
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

    // Mapeo de filtros a textos de estado en la tabla
    var estadoTextos = {
        'borrador': 'Borrador',
        'en_proveedor': 'En Proveedor',
        'en_revision_consultor': 'En Revisión',
        'cerrada': 'Cerrada'
    };

    // Click en tarjetas de filtrado
    $('.filter-card').on('click', function() {
        var filtro = $(this).data('filter');

        // Actualizar estado visual de las tarjetas
        $('.filter-card').removeClass('active');
        $(this).addClass('active');

        // Aplicar filtro a la columna de estado (índice 4)
        if (filtro === 'todos') {
            table.column(4).search('').draw();
        } else {
            var textoEstado = estadoTextos[filtro] || filtro;
            table.column(4).search(textoEstado).draw();
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
