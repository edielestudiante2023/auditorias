<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Banco de Ítems') ?> | Auditorías</title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
  <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
  <link rel="apple-touch-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= site_url('admin/dashboard') ?>">
      <i class="bi bi-shield-check"></i> Auditorías - Admin
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active" href="<?= site_url('admin/items') ?>">
            <i class="bi bi-list-check"></i> Banco de Ítems
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i> <?= esc(userName()) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><span class="dropdown-item-text"><small>Rol: <?= esc(currentRoleName()) ?></small></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right"></i> Salir</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Contenido principal -->
<div class="container py-4">

  <!-- Mensajes flash -->
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle"></i> <?= esc(session()->getFlashdata('success')) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle"></i> <?= esc(session()->getFlashdata('error')) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-0"><i class="bi bi-list-check"></i> Banco de Ítems de Auditoría</h4>
      <small class="text-muted">Gestiona las preguntas del checklist de auditorías</small>
    </div>
    <div>
      <a href="<?= site_url('admin/items/reordenar') ?>" class="btn btn-outline-secondary me-2">
        <i class="bi bi-arrow-down-up"></i> Reordenar
      </a>
      <a href="<?= site_url('admin/items/crear') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nuevo Ítem
      </a>
    </div>
  </div>

  <!-- Tabla de items -->
  <div class="card shadow-sm">
    <div class="card-body">
      <?php if (empty($items)): ?>
        <div class="text-center py-5">
          <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
          <p class="text-muted mt-3">No hay ítems registrados.</p>
          <a href="<?= site_url('admin/items/crear') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Crear Primer Ítem
          </a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table id="tablaItems" class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th style="width: 60px;">Orden</th>
                <th style="width: 100px;">Código</th>
                <th>Título</th>
                <th style="width: 140px;" class="text-center">Alcance</th>
                <th style="width: 120px;" class="text-center">Alto Riesgo</th>
                <th style="width: 100px;" class="text-center">Estado</th>
                <th style="width: 180px;" class="text-center">Acciones</th>
              </tr>
            </thead>
            <tfoot class="table-light">
              <tr>
                <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar orden"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar código"></th>
                <th><input type="text" class="form-control form-control-sm" placeholder="Filtrar título"></th>
                <th>
                  <select class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Global">Global</option>
                    <option value="Por cliente">Por cliente</option>
                  </select>
                </th>
                <th>
                  <select class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Sí">Sí</option>
                    <option value="No">No</option>
                  </select>
                </th>
                <th>
                  <select class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                  </select>
                </th>
                <th></th>
              </tr>
            </tfoot>
            <tbody>
              <?php foreach ($items as $item): ?>
                <tr>
                  <td class="text-muted">
                    <i class="bi bi-grip-vertical"></i> <?= esc($item['orden']) ?>
                  </td>
                  <td>
                    <span class="badge bg-secondary"><?= esc($item['codigo_item']) ?></span>
                  </td>
                  <td>
                    <strong><?= esc($item['titulo']) ?></strong>
                    <?php if (!empty($item['descripcion'])): ?>
                      <br>
                      <small class="text-muted">
                        <?= esc(mb_substr($item['descripcion'], 0, 100)) ?>
                        <?= mb_strlen($item['descripcion']) > 100 ? '...' : '' ?>
                      </small>
                    <?php endif; ?>
                  </td>
                  <?php
                    $alcance = $item['alcance'] ?? 'global';
                    $alcanceTexto = ucfirst(str_replace('_', ' ', $alcance));
                    $badgeClass = [
                        'global' => 'primary',
                        'por_cliente' => 'info',
                        'mixto' => 'warning'
                    ][$alcance] ?? 'secondary';
                    $iconClass = [
                        'global' => 'globe',
                        'por_cliente' => 'building',
                        'mixto' => 'diagram-3'
                    ][$alcance] ?? 'question';
                  ?>
                  <td class="text-center" data-search="<?= $alcanceTexto ?>">
                    <span class="badge bg-<?= $badgeClass ?>">
                      <i class="bi bi-<?= $iconClass ?>"></i>
                      <?= $alcanceTexto ?>
                    </span>
                  </td>
                  <td class="text-center" data-search="<?= $item['aplica_alto_riesgo'] ? 'Sí' : 'No' ?>">
                    <?php if ($item['aplica_alto_riesgo']): ?>
                      <span class="badge bg-danger">
                        <i class="bi bi-exclamation-triangle"></i> Sí
                      </span>
                    <?php else: ?>
                      <span class="badge bg-secondary">No</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center" data-search="<?= $item['activo'] ? 'Activo' : 'Inactivo' ?>">
                    <?php if ($item['activo']): ?>
                      <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                      <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                      <a href="<?= site_url('admin/items/editar/' . $item['id_item']) ?>"
                         class="btn btn-outline-primary"
                         title="Editar">
                        <i class="bi bi-pencil"></i>
                      </a>

                      <button type="button"
                              class="btn btn-outline-<?= $item['activo'] ? 'warning' : 'success' ?>"
                              onclick="toggleEstado(<?= $item['id_item'] ?>, '<?= esc($item['titulo']) ?>', <?= $item['activo'] ?>)"
                              title="<?= $item['activo'] ? 'Desactivar' : 'Activar' ?>">
                        <i class="bi bi-<?= $item['activo'] ? 'eye-slash' : 'eye' ?>"></i>
                      </button>

                      <button type="button"
                              class="btn btn-outline-danger"
                              onclick="confirmarEliminar(<?= $item['id_item'] ?>, '<?= esc($item['titulo']) ?>')"
                              title="Eliminar">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Formulario oculto para toggle y eliminar -->
<form id="formToggle" method="post" style="display: none;">
  <?= csrf_field() ?>
</form>

<form id="formEliminar" method="post" style="display: none;">
  <?= csrf_field() ?>
</form>

<!-- jQuery (debe ir primero) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
function toggleEstado(id, titulo, estadoActual) {
  const accion = estadoActual ? 'desactivar' : 'activar';
  const mensaje = `¿Está seguro de ${accion} el ítem "${titulo}"?`;

  if (confirm(mensaje)) {
    const form = document.getElementById('formToggle');
    form.action = '<?= site_url('admin/items/toggle/') ?>' + id;
    form.submit();
  }
}

function confirmarEliminar(id, titulo) {
  const mensaje = `¿Está seguro de ELIMINAR el ítem "${titulo}"?\n\nEsta acción no se puede deshacer.`;

  if (confirm(mensaje)) {
    const form = document.getElementById('formEliminar');
    form.action = '<?= site_url('admin/items/eliminar/') ?>' + id;
    form.submit();
  }
}

// Inicializar DataTables con filtros en footer
$(document).ready(function() {
  var table = $('#tablaItems').DataTable({
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json"
    },
    "pageLength": 25,
    "order": [[0, 'asc']], // Ordenar por columna Orden
    "columnDefs": [
      {
        "orderable": false,
        "targets": 6 // Columna de Acciones no ordenable
      },
      {
        // Usar atributo data-search para columnas con badges
        "targets": [3, 4, 5],
        "render": function(data, type, row, meta) {
          if (type === 'filter') {
            var $cell = $(table.cell(meta.row, meta.col).node());
            return $cell.attr('data-search') || '';
          }
          return data;
        }
      }
    ],
    "initComplete": function() {
      var api = this.api();

      // Filtros en columnas específicas del footer
      // Columna 0: Orden (input text)
      $('input', api.column(0).footer()).on('keyup change', function() {
        api.column(0).search(this.value).draw();
      });

      // Columna 1: Código (input text)
      $('input', api.column(1).footer()).on('keyup change', function() {
        api.column(1).search(this.value).draw();
      });

      // Columna 2: Título (input text)
      $('input', api.column(2).footer()).on('keyup change', function() {
        api.column(2).search(this.value).draw();
      });

      // Columna 3: Alcance (select)
      $('select', api.column(3).footer()).on('change', function() {
        var val = $.fn.dataTable.util.escapeRegex($(this).val());
        api.column(3).search(val ? '^' + val + '$' : '', true, false).draw();
      });

      // Columna 4: Alto Riesgo (select)
      $('select', api.column(4).footer()).on('change', function() {
        var val = $.fn.dataTable.util.escapeRegex($(this).val());
        api.column(4).search(val ? '^' + val + '$' : '', true, false).draw();
      });

      // Columna 5: Estado (select)
      $('select', api.column(5).footer()).on('change', function() {
        var val = $.fn.dataTable.util.escapeRegex($(this).val());
        api.column(5).search(val ? '^' + val + '$' : '', true, false).draw();
      });
    }
  });
});
</script>

</body>
</html>
