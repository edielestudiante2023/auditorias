<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Reordenar Ítems') ?> | Auditorías</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .sortable-item {
      cursor: move;
      transition: background-color 0.2s;
    }
    .sortable-item:hover {
      background-color: #f8f9fa;
    }
    .sortable-item.dragging {
      opacity: 0.5;
    }
  </style>
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
          <a class="nav-link" href="<?= site_url('admin/items') ?>">
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

  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= site_url('admin/dashboard') ?>">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= site_url('admin/items') ?>">Banco de Ítems</a></li>
      <li class="breadcrumb-item active">Reordenar</li>
    </ol>
  </nav>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-0"><i class="bi bi-arrow-down-up"></i> Reordenar Ítems</h4>
      <small class="text-muted">Arrastra los ítems para cambiar su orden</small>
    </div>
    <div>
      <a href="<?= site_url('admin/items') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
      <button type="button" class="btn btn-success" id="btnGuardar">
        <i class="bi bi-save"></i> Guardar Orden
      </button>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <!-- Lista ordenable -->
      <div class="card shadow-sm">
        <div class="card-body">
          <?php if (empty($items)): ?>
            <div class="text-center py-5">
              <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
              <p class="text-muted mt-3">No hay ítems para ordenar.</p>
            </div>
          <?php else: ?>
            <ul class="list-group" id="sortableList">
              <?php foreach ($items as $index => $item): ?>
                <li class="list-group-item sortable-item d-flex align-items-center"
                    data-id="<?= $item['id_item'] ?>"
                    draggable="true">
                  <i class="bi bi-grip-vertical text-muted me-3" style="font-size: 1.5rem;"></i>
                  <div class="flex-grow-1">
                    <div class="d-flex align-items-center">
                      <span class="badge bg-secondary me-2"><?= esc($item['codigo_item']) ?></span>
                      <strong><?= esc($item['titulo']) ?></strong>
                    </div>
                    <?php if (!empty($item['descripcion'])): ?>
                      <small class="text-muted">
                        <?= esc(mb_substr($item['descripcion'], 0, 80)) ?>
                        <?= mb_strlen($item['descripcion']) > 80 ? '...' : '' ?>
                      </small>
                    <?php endif; ?>
                  </div>
                  <div class="text-end">
                    <?php if ($item['aplica_alto_riesgo']): ?>
                      <span class="badge bg-danger me-2">
                        <i class="bi bi-exclamation-triangle"></i>
                      </span>
                    <?php endif; ?>
                    <?php if (!$item['activo']): ?>
                      <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Sidebar con instrucciones -->
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <i class="bi bi-info-circle"></i> Instrucciones
        </div>
        <div class="card-body">
          <h6 class="fw-bold">Cómo reordenar:</h6>
          <ol class="small">
            <li>Haz clic y mantén presionado sobre el ícono <i class="bi bi-grip-vertical"></i> o cualquier parte del ítem</li>
            <li>Arrastra el ítem a la posición deseada</li>
            <li>Suelta para colocar el ítem en la nueva posición</li>
            <li>Haz clic en "Guardar Orden" para aplicar los cambios</li>
          </ol>

          <div class="alert alert-warning mt-3 mb-0" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            <small>
              <strong>Importante:</strong> Los cambios no se guardarán hasta que hagas clic en el botón "Guardar Orden".
            </small>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mt-3">
        <div class="card-header bg-light">
          <i class="bi bi-lightbulb"></i> Consejos
        </div>
        <div class="card-body">
          <ul class="small mb-0">
            <li>El orden afecta cómo se muestran los ítems en las auditorías</li>
            <li>Agrupa ítems relacionados juntos para mejor organización</li>
            <li>Los ítems inactivos también pueden reordenarse</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Drag and Drop functionality
const sortableList = document.getElementById('sortableList');
let draggedElement = null;

if (sortableList) {
  const items = sortableList.querySelectorAll('.sortable-item');

  items.forEach(item => {
    item.addEventListener('dragstart', function(e) {
      draggedElement = this;
      this.classList.add('dragging');
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/html', this.innerHTML);
    });

    item.addEventListener('dragend', function(e) {
      this.classList.remove('dragging');
    });

    item.addEventListener('dragover', function(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';

      if (this !== draggedElement) {
        const rect = this.getBoundingClientRect();
        const midpoint = rect.top + rect.height / 2;

        if (e.clientY < midpoint) {
          this.parentNode.insertBefore(draggedElement, this);
        } else {
          this.parentNode.insertBefore(draggedElement, this.nextSibling);
        }
      }
    });
  });
}

// Guardar orden
document.getElementById('btnGuardar')?.addEventListener('click', function() {
  const items = sortableList.querySelectorAll('.sortable-item');
  const orden = {};

  items.forEach((item, index) => {
    const id = item.getAttribute('data-id');
    orden[id] = index + 1;
  });

  // Enviar vía AJAX
  fetch('<?= site_url('admin/items/updateOrden') ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ orden: orden })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Orden actualizado exitosamente');
      window.location.href = '<?= site_url('admin/items') ?>';
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Ocurrió un error al guardar el orden');
  });
});
</script>

</body>
</html>
