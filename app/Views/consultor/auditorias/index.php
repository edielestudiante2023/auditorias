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

<div class="container py-4">
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
    <a href="<?= site_url('consultor/auditorias/crear') ?>" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Nueva Auditoría
    </a>
  </div>

  <?php if (empty($auditorias)): ?>
    <div class="text-center py-5">
      <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
      <p class="text-muted mt-3">No tienes auditorías asignadas</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover table-striped align-middle">
        <thead class="table-success">
          <tr>
            <th width="5%">#</th>
            <th width="30%">Proveedor</th>
            <th width="15%">Fecha Creación</th>
            <th width="15%">Estado</th>
            <th width="20%">Progreso</th>
            <th width="15%">Acciones</th>
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
                    <button type="button"
                            class="btn btn-warning"
                            onclick="reenviarEmail(<?= $aud['id_auditoria'] ?>)"
                            title="Reenviar invitación por email al proveedor">
                      <i class="bi bi-envelope-arrow-up"></i> Reenviar Email
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
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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
<script>
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
                alert('✓ Email reenviado exitosamente al proveedor');
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
</script>
</body>
</html>
