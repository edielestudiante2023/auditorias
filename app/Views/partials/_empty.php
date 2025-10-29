<?php
/**
 * Empty State Partial Component
 *
 * Uso en vistas:
 * <?= view('partials/_empty', [
 *     'icon' => 'bi-inbox',              // Clase de Bootstrap Icon
 *     'title' => 'No hay datos',         // Título principal
 *     'message' => 'Mensaje opcional',   // Mensaje adicional (opcional)
 *     'button_text' => 'Crear Nuevo',    // Texto del botón (opcional)
 *     'button_url' => '/admin/clientes/create', // URL del botón (opcional)
 * ]) ?>
 */

$icon = $icon ?? 'bi-inbox';
$title = $title ?? 'No hay registros';
$message = $message ?? null;
$button_text = $button_text ?? null;
$button_url = $button_url ?? null;
?>

<div class="card">
    <div class="card-body text-center py-5">
        <i class="<?= esc($icon) ?> text-muted" style="font-size: 4rem;"></i>
        <h5 class="text-muted mt-3"><?= esc($title) ?></h5>
        <?php if ($message): ?>
            <p class="text-muted"><?= esc($message) ?></p>
        <?php endif; ?>
        <?php if ($button_text && $button_url): ?>
            <a href="<?= base_url($button_url) ?>" class="btn btn-primary mt-2">
                <i class="bi bi-plus-circle"></i> <?= esc($button_text) ?>
            </a>
        <?php endif; ?>
    </div>
</div>
