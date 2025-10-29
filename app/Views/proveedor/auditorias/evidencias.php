<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-file-earmark-text"></i> Mis Evidencias</h2>
    <a href="<?= site_url('proveedor/dashboard') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?= view('partials/flash') ?>

<!-- Tabs para evidencias globales y por cliente -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-globales" data-bs-toggle="tab" data-bs-target="#content-globales" type="button" role="tab">
            <i class="bi bi-globe"></i> Evidencias Globales (<?= count($evidencias_globales) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-clientes" data-bs-toggle="tab" data-bs-target="#content-clientes" type="button" role="tab">
            <i class="bi bi-building"></i> Evidencias por Cliente (<?= count($evidencias_cliente) ?>)
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Evidencias Globales -->
    <div class="tab-pane fade show active" id="content-globales" role="tabpanel">
        <?php if (empty($evidencias_globales)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">No hay evidencias globales</h5>
                    <p class="text-muted">Aún no has subido evidencias globales.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Archivo</th>
                                    <th>Auditoría</th>
                                    <th>Ítem</th>
                                    <th>Tamaño</th>
                                    <th>Fecha Subida</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evidencias_globales as $ev): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-earmark text-primary"></i>
                                            <strong><?= esc($ev['nombre_archivo_original']) ?></strong>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= esc($ev['codigo_formato'] ?? 'N/A') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= esc($ev['codigo_item']) ?></span>
                                            <br>
                                            <small><?= esc($ev['item_titulo']) ?></small>
                                        </td>
                                        <td><?= number_format($ev['tamanio_bytes'] / 1024, 2) ?> KB</td>
                                        <td>
                                            <?php
                                            $fecha = new DateTime($ev['created_at']);
                                            echo $fecha->format('d/m/Y H:i');
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $extension = strtolower(pathinfo($ev['nombre_archivo_original'], PATHINFO_EXTENSION));
                                            // Para evidencias globales
                                            $idEvidencia = isset($ev['id_evidencia']) ? $ev['id_evidencia'] : (isset($ev['id_evidencia_cliente']) ? $ev['id_evidencia_cliente'] : null);
                                            $tipoRuta = isset($ev['id_evidencia']) ? 'evidencia' : 'evidencia-cliente';
                                            $rutaArchivo = site_url('proveedor/' . $tipoRuta . '/' . $idEvidencia . '/ver');
                                            $esVisualizableEnNavegador = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif']);
                                            ?>

                                            <?php if ($esVisualizableEnNavegador): ?>
                                                <!-- Botón Ver (PDF e imágenes) -->
                                                <a href="<?= $rutaArchivo ?>" target="_blank" class="btn btn-sm btn-info" title="Ver archivo">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            <?php endif; ?>

                                            <!-- Botón Descargar (todos los archivos) -->
                                            <a href="<?= $rutaArchivo ?>" download class="btn btn-sm btn-primary" title="Descargar">
                                                <i class="bi bi-download"></i>
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

    <!-- Evidencias por Cliente -->
    <div class="tab-pane fade" id="content-clientes" role="tabpanel">
        <?php if (empty($evidencias_cliente)): ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">No hay evidencias por cliente</h5>
                    <p class="text-muted">Aún no has subido evidencias específicas por cliente.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Archivo</th>
                                    <th>Auditoría</th>
                                    <th>Ítem</th>
                                    <th>Cliente</th>
                                    <th>Tamaño</th>
                                    <th>Fecha Subida</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evidencias_cliente as $ev): ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-earmark text-info"></i>
                                            <strong><?= esc($ev['nombre_archivo_original']) ?></strong>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= esc($ev['codigo_formato'] ?? 'N/A') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?= esc($ev['codigo_item']) ?></span>
                                            <br>
                                            <small><?= esc($ev['item_titulo']) ?></small>
                                        </td>
                                        <td>
                                            <i class="bi bi-building"></i>
                                            <?= esc($ev['cliente_nombre']) ?>
                                        </td>
                                        <td><?= number_format($ev['tamanio_bytes'] / 1024, 2) ?> KB</td>
                                        <td>
                                            <?php
                                            $fecha = new DateTime($ev['created_at']);
                                            echo $fecha->format('d/m/Y H:i');
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $extension = strtolower(pathinfo($ev['nombre_archivo_original'], PATHINFO_EXTENSION));
                                            // Para evidencias globales
                                            $idEvidencia = isset($ev['id_evidencia']) ? $ev['id_evidencia'] : (isset($ev['id_evidencia_cliente']) ? $ev['id_evidencia_cliente'] : null);
                                            $tipoRuta = isset($ev['id_evidencia']) ? 'evidencia' : 'evidencia-cliente';
                                            $rutaArchivo = site_url('proveedor/' . $tipoRuta . '/' . $idEvidencia . '/ver');
                                            $esVisualizableEnNavegador = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif']);
                                            ?>

                                            <?php if ($esVisualizableEnNavegador): ?>
                                                <!-- Botón Ver (PDF e imágenes) -->
                                                <a href="<?= $rutaArchivo ?>" target="_blank" class="btn btn-sm btn-info" title="Ver archivo">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            <?php endif; ?>

                                            <!-- Botón Descargar (todos los archivos) -->
                                            <a href="<?= $rutaArchivo ?>" download class="btn btn-sm btn-primary" title="Descargar">
                                                <i class="bi bi-download"></i>
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
</div>

<div class="mt-3 text-muted">
    <small>
        <i class="bi bi-info-circle"></i>
        Total: <?= count($evidencias_globales) + count($evidencias_cliente) ?> archivo(s) subido(s)
    </small>
</div>

<?= $this->endSection() ?>
