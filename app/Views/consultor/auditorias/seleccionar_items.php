<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2><i class="bi bi-list-check"></i> Seleccionar Ítems - Paso 2 de 4</h2>
    <p class="text-muted">
        Seleccione los ítems del banco de preguntas que aplicarán a esta auditoría.
        <br>
        <strong>Auditoría:</strong> <?= esc($auditoria['codigo_formato'] ?? '#' . $auditoria['id_auditoria']) ?>
    </p>
</div>

<?= view('partials/flash') ?>

<!-- Progress Stepper -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-check"></i>
                </div>
                <div class="mt-2 small"><strong>Paso 1</strong><br>Información Básica</div>
            </div>
            <div style="flex: 1; height: 2px; background-color: #28a745;"></div>
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    2
                </div>
                <div class="mt-2 small"><strong>Paso 2</strong><br>Seleccionar Ítems</div>
            </div>
            <div style="flex: 1; height: 2px; background-color: #dee2e6;"></div>
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    3
                </div>
                <div class="mt-2 small"><strong>Paso 3</strong><br>Asignar Clientes</div>
            </div>
            <div style="flex: 1; height: 2px; background-color: #dee2e6;"></div>
            <div class="text-center" style="flex: 1;">
                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    4
                </div>
                <div class="mt-2 small"><strong>Paso 4</strong><br>Enviar Invitación</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center">
            <span><i class="bi bi-clipboard-check"></i> Ítems del Banco de Preguntas</span>
            <div>
                <button type="button" class="btn btn-sm btn-light" onclick="seleccionarTodos()">
                    <i class="bi bi-check-all"></i> Seleccionar Todos
                </button>
                <button type="button" class="btn btn-sm btn-light" onclick="deseleccionarTodos()">
                    <i class="bi bi-x-circle"></i> Deseleccionar Todos
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($items)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                No hay ítems disponibles en el banco de preguntas. Por favor contacte al administrador.
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle"></i>
                <strong>Instrucciones:</strong>
                <ul class="mb-0 mt-2">
                    <li><strong>Global:</strong> Se responde UNA sola vez para toda la auditoría</li>
                    <li><strong>Por Cliente:</strong> Se responde PARA CADA cliente asignado</li>
                    <li><strong>Alto Riesgo:</strong> Ítems críticos que requieren especial atención según normativa colombiana</li>
                </ul>
            </div>

            <form method="post" action="<?= site_url('consultor/auditorias/' . $auditoria['id_auditoria'] . '/guardar-items') ?>" id="formItems">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <input type="text" class="form-control" id="buscarItem" placeholder="🔍 Buscar ítems por título o descripción...">
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tablaItems">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleAll()">
                                </th>
                                <th width="10%">Orden</th>
                                <th width="45%">Título</th>
                                <th width="15%">Alcance</th>
                                <th width="15%">Alto Riesgo</th>
                                <th width="10%">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr class="item-row">
                                    <td>
                                        <input class="form-check-input item-checkbox"
                                               type="checkbox"
                                               name="items[]"
                                               value="<?= $item['id_item'] ?>"
                                               id="item<?= $item['id_item'] ?>"
                                               <?= in_array($item['id_item'], $itemsAsignados) ? 'checked' : '' ?>>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= esc($item['orden']) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= esc($item['titulo']) ?></strong>
                                        <?php if (!empty($item['descripcion'])): ?>
                                            <br>
                                            <small class="text-muted"><?= esc($item['descripcion']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $alcanceTexto = '';
                                        $badgeClass = 'bg-secondary';
                                        $iconClass = 'question-circle';

                                        if ($item['alcance'] === 'global') {
                                            $alcanceTexto = 'Global';
                                            $badgeClass = 'bg-info';
                                            $iconClass = 'globe';
                                        } elseif ($item['alcance'] === 'por_cliente') {
                                            $alcanceTexto = 'Por Cliente';
                                            $badgeClass = 'bg-warning text-dark';
                                            $iconClass = 'building';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <i class="bi bi-<?= $iconClass ?>"></i>
                                            <?= $alcanceTexto ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($item['aplica_alto_riesgo']): ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-exclamation-triangle-fill"></i> Sí
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($item['activo']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Total: <?= count($items) ?> ítems disponibles |
                            <span id="contadorSeleccionados"><?= count($itemsAsignados) ?></span> seleccionados
                        </small>
                    </div>
                    <div>
                        <a href="<?= site_url('consultor/auditorias') ?>" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-right-circle"></i> Continuar al Paso 3
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
/* Hacer checkboxes más grandes y visibles */
.form-check-input {
    width: 1.5em !important;
    height: 1.5em !important;
    margin-top: 0.1em !important;
    border: 2px solid #0d6efd !important;
    cursor: pointer !important;
}

.form-check-input:checked {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
}

/* Resaltar la fila cuando el checkbox está marcado */
tr:has(.form-check-input:checked) {
    background-color: #e7f1ff !important;
}

tr:has(.form-check-input:checked) td {
    font-weight: 500;
}
</style>

<script>
// Seleccionar/Deseleccionar todos
function seleccionarTodos() {
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        if (!checkbox.closest('tr').style.display || checkbox.closest('tr').style.display !== 'none') {
            checkbox.checked = true;
        }
    });
    actualizarContador();
}

function deseleccionarTodos() {
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    actualizarContador();
}

function toggleAll() {
    const selectAll = document.getElementById('selectAll');
    document.querySelectorAll('.item-checkbox').forEach(checkbox => {
        if (!checkbox.closest('tr').style.display || checkbox.closest('tr').style.display !== 'none') {
            checkbox.checked = selectAll.checked;
        }
    });
    actualizarContador();
}

// Actualizar contador de seleccionados
function actualizarContador() {
    const seleccionados = document.querySelectorAll('.item-checkbox:checked').length;
    document.getElementById('contadorSeleccionados').textContent = seleccionados;
}

// Buscar ítems
document.getElementById('buscarItem')?.addEventListener('input', function(e) {
    const termino = e.target.value.toLowerCase();
    document.querySelectorAll('.item-row').forEach(row => {
        const texto = row.textContent.toLowerCase();
        row.style.display = texto.includes(termino) ? '' : 'none';
    });
});

// Actualizar contador cuando se marca/desmarca un checkbox
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', actualizarContador);
});

// Validación antes de enviar
document.getElementById('formItems')?.addEventListener('submit', function(e) {
    const seleccionados = document.querySelectorAll('.item-checkbox:checked').length;
    if (seleccionados === 0) {
        e.preventDefault();
        alert('⚠️ Debe seleccionar al menos un ítem para continuar.');
        return false;
    }
});
</script>

<?= $this->endSection() ?>
