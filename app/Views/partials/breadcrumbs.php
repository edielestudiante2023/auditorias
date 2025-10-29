<?php
/**
 * Breadcrumbs Partial
 *
 * Uso en controladores:
 * $data['breadcrumbs'] = [
 *     ['title' => 'Inicio', 'url' => '/dashboard'],
 *     ['title' => 'Auditorías', 'url' => '/consultor/auditorias'],
 *     ['title' => 'Detalle', 'url' => null] // null para elemento activo
 * ];
 */

// Si no se pasaron breadcrumbs, no mostrar nada
if (!isset($breadcrumbs) || empty($breadcrumbs)) {
    return;
}
?>

<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <?php foreach ($breadcrumbs as $index => $crumb): ?>
            <?php
            $isLast = ($index === count($breadcrumbs) - 1);
            $title = $crumb['title'] ?? '';
            $url = $crumb['url'] ?? null;
            ?>

            <?php if ($isLast || !$url): ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= esc($title) ?>
                </li>
            <?php else: ?>
                <li class="breadcrumb-item">
                    <a href="<?= base_url($url) ?>">
                        <?= esc($title) ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
