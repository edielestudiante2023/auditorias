<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title) ?></title>
  <link rel="icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <style>
    .table-responsive {
      overflow-x: auto;
    }
    .badge-estado {
      font-size: 0.85rem;
      padding: 0.4rem 0.6rem;
    }
    .detalle-motivo {
      max-width: 400px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      cursor: pointer;
    }
    .detalle-motivo:hover {
      white-space: normal;
    }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= site_url('admin/dashboard') ?>">
      <i class="bi bi-arrow-left-circle"></i> Volver al Dashboard
    </a>
    <span class="navbar-text text-white">
      <i class="bi bi-person-circle"></i> <?= session()->get('nombre') ?? 'Usuario' ?>
    </span>
  </div>
</nav>

<div class="container-fluid">
  <div class="row mb-4">
    <div class="col-12">
      <div class="card shadow">
        <div class="card-header bg-secondary text-white">
          <h4 class="mb-0">
            <i class="bi bi-clock-history"></i> Auditoría de Cambios - Historial de Reaperturas
          </h4>
        </div>
        <div class="card-body">
          <p class="text-muted">
            Este historial registra todas las veces que una auditoría cerrada fue reabierta por un SuperAdmin,
            incluyendo el motivo y el momento exacto de cada acción.
          </p>

          <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle"></i> <?= session()->getFlashdata('success') ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle"></i> <?= session()->getFlashdata('error') ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <div class="table-responsive">
            <table id="historialTable" class="table table-striped table-hover">
              <thead class="table-dark">
                <tr>
                  <th>Fecha/Hora</th>
                  <th>Código Auditoría</th>
                  <th>Proveedor</th>
                  <th>Consultor</th>
                  <th>Fecha Programada</th>
                  <th>Estado Actual</th>
                  <th>Usuario que Reabrió</th>
                  <th>Motivo</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($historial)): ?>
                  <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                      <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                      <p class="mt-2">No hay registros de reaperturas</p>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($historial as $registro): ?>
                    <tr>
                      <td>
                        <small>
                          <i class="bi bi-calendar-event"></i>
                          <?= date('d/m/Y', strtotime($registro['created_at'])) ?><br>
                          <i class="bi bi-clock"></i>
                          <?= date('H:i:s', strtotime($registro['created_at'])) ?>
                        </small>
                      </td>
                      <td>
                        <strong><?= esc($registro['codigo_formato']) ?></strong>
                      </td>
                      <td>
                        <small>
                          <?= esc($registro['proveedor_nombre']) ?><br>
                          <span class="text-muted">NIT: <?= esc($registro['proveedor_nit']) ?></span>
                        </small>
                      </td>
                      <td>
                        <small><?= esc($registro['consultor_nombre']) ?></small>
                      </td>
                      <td>
                        <small><?= date('d/m/Y', strtotime($registro['fecha_programada'])) ?></small>
                      </td>
                      <td>
                        <?php
                          $estadoBadge = [
                            'borrador' => 'secondary',
                            'en_proveedor' => 'warning',
                            'en_revision' => 'info',
                            'cerrada' => 'success',
                            'anulada' => 'danger'
                          ];
                          $estadoTexto = [
                            'borrador' => 'Borrador',
                            'en_proveedor' => 'En Proveedor',
                            'en_revision' => 'En Revisión',
                            'cerrada' => 'Cerrada',
                            'anulada' => 'Anulada'
                          ];
                          $badge = $estadoBadge[$registro['estado_actual']] ?? 'secondary';
                          $texto = $estadoTexto[$registro['estado_actual']] ?? $registro['estado_actual'];
                        ?>
                        <span class="badge bg-<?= $badge ?> badge-estado">
                          <?= $texto ?>
                        </span>
                      </td>
                      <td>
                        <small>
                          <i class="bi bi-person-fill"></i> <?= esc($registro['usuario_nombre']) ?><br>
                          <span class="text-muted"><?= esc($registro['usuario_email']) ?></span>
                        </small>
                      </td>
                      <td>
                        <?php if (!empty($registro['detalle'])): ?>
                          <div class="detalle-motivo" title="<?= esc($registro['detalle']) ?>">
                            <?= esc($registro['detalle']) ?>
                          </div>
                        <?php else: ?>
                          <span class="text-muted fst-italic">Sin motivo especificado</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a href="<?= site_url('admin/auditorias/' . $registro['id_auditoria'] . '/adicionar-clientes') ?>"
                           class="btn btn-sm btn-primary"
                           title="Gestionar auditoría"
                           target="_blank">
                          <i class="bi bi-pencil-square"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
  // Configurar DataTable con ordenamiento por fecha descendente
  $('#historialTable').DataTable({
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
    },
    order: [[0, 'desc']], // Ordenar por fecha descendente
    pageLength: 25,
    responsive: true
  });
});
</script>

</body>
</html>
