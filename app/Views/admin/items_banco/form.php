<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title ?? 'Formulario Ítem') ?> | Auditorías</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
      <li class="breadcrumb-item active"><?= $item ? 'Editar' : 'Crear' ?></li>
    </ol>
  </nav>

  <!-- Mensajes de error generales -->
  <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle"></i> <strong>Errores de validación:</strong>
      <ul class="mb-0 mt-2">
        <?php foreach (session()->getFlashdata('errors') as $error): ?>
          <li><?= esc($error) ?></li>
        <?php endforeach; ?>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
      <i class="bi bi-<?= $item ? 'pencil-square' : 'plus-circle' ?>"></i>
      <?= $item ? 'Editar Ítem' : 'Crear Nuevo Ítem' ?>
    </h4>
  </div>

  <!-- Formulario -->
  <div class="row">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <form method="post" action="<?= $item ? site_url('admin/items/update/' . $item['id_item']) : site_url('admin/items/store') ?>">
            <?= csrf_field() ?>

            <!-- Código del Ítem -->
            <div class="mb-3">
              <label for="codigo_item" class="form-label">
                Código <span class="text-danger">*</span>
              </label>
              <input type="text"
                     class="form-control <?= $validation->hasError('codigo_item') ? 'is-invalid' : '' ?>"
                     id="codigo_item"
                     name="codigo_item"
                     value="<?= old('codigo_item', $item['codigo_item'] ?? '') ?>"
                     maxlength="10"
                     placeholder="Ej: 1.1, A-01, etc."
                     required>
              <?php if ($validation->hasError('codigo_item')): ?>
                <div class="invalid-feedback"><?= $validation->getError('codigo_item') ?></div>
              <?php endif; ?>
              <small class="form-text text-muted">
                Código único del ítem (máx. 10 caracteres)
              </small>
            </div>

            <!-- Título -->
            <div class="mb-3">
              <label for="titulo" class="form-label">
                Título <span class="text-danger">*</span>
              </label>
              <input type="text"
                     class="form-control <?= $validation->hasError('titulo') ? 'is-invalid' : '' ?>"
                     id="titulo"
                     name="titulo"
                     value="<?= old('titulo', $item['titulo'] ?? '') ?>"
                     maxlength="255"
                     placeholder="Título del ítem de auditoría"
                     required>
              <?php if ($validation->hasError('titulo')): ?>
                <div class="invalid-feedback"><?= $validation->getError('titulo') ?></div>
              <?php endif; ?>
            </div>

            <!-- Descripción -->
            <div class="mb-3">
              <label for="descripcion" class="form-label">Descripción</label>
              <textarea class="form-control <?= $validation->hasError('descripcion') ? 'is-invalid' : '' ?>"
                        id="descripcion"
                        name="descripcion"
                        rows="4"
                        maxlength="5000"
                        placeholder="Descripción detallada del ítem (opcional)"><?= old('descripcion', $item['descripcion'] ?? '') ?></textarea>
              <?php if ($validation->hasError('descripcion')): ?>
                <div class="invalid-feedback"><?= $validation->getError('descripcion') ?></div>
              <?php endif; ?>
              <small class="form-text text-muted">
                Información adicional sobre el ítem (máx. 5000 caracteres)
              </small>
            </div>

            <!-- Orden -->
            <div class="mb-3">
              <label for="orden" class="form-label">Orden</label>
              <input type="number"
                     class="form-control <?= $validation->hasError('orden') ? 'is-invalid' : '' ?>"
                     id="orden"
                     name="orden"
                     value="<?= old('orden', $item['orden'] ?? '') ?>"
                     min="0"
                     placeholder="Dejar vacío para asignar automáticamente">
              <?php if ($validation->hasError('orden')): ?>
                <div class="invalid-feedback"><?= $validation->getError('orden') ?></div>
              <?php endif; ?>
              <small class="form-text text-muted">
                Orden de visualización (menor número = primero en la lista)
              </small>
            </div>

            <!-- Alcance -->
            <div class="mb-3">
              <label for="alcance" class="form-label">
                <i class="bi bi-diagram-3"></i> Alcance <span class="text-danger">*</span>
              </label>
              <select class="form-select <?= $validation->hasError('alcance') ? 'is-invalid' : '' ?>"
                      id="alcance"
                      name="alcance"
                      required>
                <option value="">Seleccione el alcance...</option>
                <option value="global" <?= old('alcance', $item['alcance'] ?? '') == 'global' ? 'selected' : '' ?>>
                  🌐 Global - Se responde UNA sola vez para toda la auditoría
                </option>
                <option value="por_cliente" <?= old('alcance', $item['alcance'] ?? '') == 'por_cliente' ? 'selected' : '' ?>>
                  🏢 Por Cliente - Se responde PARA CADA cliente asignado
                </option>
              </select>
              <?php if ($validation->hasError('alcance')): ?>
                <div class="invalid-feedback"><?= $validation->getError('alcance') ?></div>
              <?php endif; ?>
              <small class="form-text text-muted">
                <strong>Global:</strong> Información general del proveedor (ej: RUT, política SST, licencias)<br>
                <strong>Por Cliente:</strong> Información específica de cada cliente (ej: afiliaciones, matriz de riesgos)
              </small>
            </div>

            <!-- Checkboxes -->
            <div class="mb-3">
              <div class="form-check mb-2">
                <input type="checkbox"
                       class="form-check-input"
                       id="aplica_alto_riesgo"
                       name="aplica_alto_riesgo"
                       value="1"
                       <?= old('aplica_alto_riesgo', $item['aplica_alto_riesgo'] ?? 0) ? 'checked' : '' ?>>
                <label class="form-check-label" for="aplica_alto_riesgo">
                  <i class="bi bi-exclamation-triangle text-danger"></i>
                  <strong>Aplica para Tareas de Alto Riesgo</strong>
                </label>
                <br>
                <small class="form-text text-muted ms-4">
                  Marcar si este ítem es aplicable a empresas con tareas de alto riesgo (trabajo en alturas, espacios confinados, trabajos eléctricos, etc.) según normatividad colombiana SST
                </small>
              </div>

              <div class="form-check">
                <input type="checkbox"
                       class="form-check-input"
                       id="activo"
                       name="activo"
                       value="1"
                       <?= old('activo', $item['activo'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label" for="activo">
                  <i class="bi bi-eye text-success"></i>
                  <strong>Activo</strong>
                </label>
                <br>
                <small class="form-text text-muted ms-4">
                  Solo los ítems activos estarán disponibles para auditorías
                </small>
              </div>
            </div>

            <hr>

            <!-- Botones de acción -->
            <div class="d-flex justify-content-between">
              <a href="<?= site_url('admin/items') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Cancelar
              </a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> <?= $item ? 'Actualizar' : 'Crear' ?> Ítem
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Sidebar con ayuda -->
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-light">
          <i class="bi bi-info-circle"></i> Ayuda
        </div>
        <div class="card-body">
          <h6 class="fw-bold">Campos obligatorios:</h6>
          <ul class="small">
            <li><strong>Código:</strong> Identificador único del ítem</li>
            <li><strong>Título:</strong> Nombre descriptivo del ítem</li>
          </ul>

          <h6 class="fw-bold mt-3">Campos opcionales:</h6>
          <ul class="small">
            <li><strong>Descripción:</strong> Información adicional o instrucciones</li>
            <li><strong>Orden:</strong> Define la secuencia de aparición</li>
          </ul>

          <h6 class="fw-bold mt-3">Opciones especiales:</h6>
          <ul class="small">
            <li><strong>Alto Riesgo:</strong> Marca ítems relacionados con tareas de alto riesgo según normatividad colombiana de SST</li>
            <li><strong>Activo:</strong> Solo ítems activos aparecen en auditorías</li>
          </ul>

          <div class="alert alert-warning mt-3 mb-0" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            <small>
              <strong>Tareas de Alto Riesgo en Colombia:</strong><br>
              • Trabajo en alturas (≥1.5m) - Res. 1409/2012<br>
              • Espacios confinados - Res. 0491/2020<br>
              • Trabajos eléctricos - RETIE, Res. 5018/2019<br>
              • Trabajos en caliente (soldadura, corte)<br>
              • Sistemas presurizados y calderas<br>
              • Izaje de cargas con grúas<br>
              • Excavaciones y zanjas<br>
              • Sustancias químicas peligrosas<br>
              • Radiaciones ionizantes<br>
              • Riesgo biológico - laboratorios/salud<br>
              • Exposición a tránsito vehicular
            </small>
          </div>

          <div class="alert alert-info mt-3 mb-0" role="alert">
            <i class="bi bi-lightbulb"></i>
            <small>
              <strong>Tip:</strong> Usa códigos numéricos (1.1, 1.2) o alfanuméricos (A-01, B-02) para facilitar la organización.
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
