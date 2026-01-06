<?php
/**
 * Filtro de Año - Partial Reutilizable
 *
 * Uso: <?= view('partials/filtro_anio', ['anio_actual' => $anio, 'url_base' => current_url()]) ?>
 *
 * Parámetros:
 * - anio_actual: Año seleccionado actualmente (default: año en curso)
 * - url_base: URL base para el filtro (default: URL actual sin parámetros)
 * - anio_inicio: Año desde el cual mostrar opciones (default: 2024)
 * - mostrar_todos: Si mostrar opción "Todos" (default: false)
 */

$anioActual = $anio_actual ?? date('Y');
$urlBase = $url_base ?? current_url();
$anioInicio = $anio_inicio ?? 2024;
$mostrarTodos = $mostrar_todos ?? true; // Por defecto mostrar opción "Todos"
$anioFin = 2030; // Proyectar hasta 2030

// Generar lista de años
$anios = range($anioFin, $anioInicio);
?>

<div class="filtro-anio-container d-inline-block">
    <div class="btn-group" role="group" aria-label="Filtro por año">
        <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-calendar3"></i>
        </span>
        <select class="form-select form-select-sm border-start-0"
                id="filtroAnio"
                onchange="filtrarPorAnio(this.value)"
                style="min-width: 100px;">
            <?php if ($mostrarTodos): ?>
                <option value="todos" <?= $anioActual === 'todos' ? 'selected' : '' ?>>Todos</option>
            <?php endif; ?>
            <?php foreach ($anios as $anio): ?>
                <option value="<?= $anio ?>" <?= (string)$anioActual === (string)$anio ? 'selected' : '' ?>>
                    <?= $anio ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<script>
function filtrarPorAnio(anio) {
    // Usar la URL actual sin parámetros de búsqueda
    const url = new URL(window.location.href);

    // Limpiar el pathname si contiene index.php duplicado
    let pathname = url.pathname;

    // Actualizar o agregar el parámetro de año
    url.searchParams.set('anio', anio);

    // Redirigir a la nueva URL
    window.location.href = url.toString();
}
</script>

<style>
.filtro-anio-container .input-group-text {
    border-radius: 0.375rem 0 0 0.375rem;
}
.filtro-anio-container .form-select {
    border-radius: 0 0.375rem 0.375rem 0;
}
.filtro-anio-container .btn-group {
    display: flex;
    align-items: center;
}
</style>
