<?php
/**
 * Flash Messages Partial
 *
 * Muestra mensajes flash de sesión con estilos de Bootstrap 5
 * Tipos soportados: success, error, warning, info
 */

$session = session();
$messageTypes = ['success', 'error', 'warning', 'info'];
?>

<?php foreach ($messageTypes as $type): ?>
    <?php if ($session->has($type)): ?>
        <?php
        // Mapear tipos a clases de Bootstrap
        $alertClass = match($type) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info',
            default => 'alert-secondary'
        };

        // Iconos según tipo
        $icon = match($type) {
            'success' => '<i class="bi bi-check-circle-fill me-2"></i>',
            'error' => '<i class="bi bi-exclamation-triangle-fill me-2"></i>',
            'warning' => '<i class="bi bi-exclamation-circle-fill me-2"></i>',
            'info' => '<i class="bi bi-info-circle-fill me-2"></i>',
            default => ''
        };
        ?>

        <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
            <?= $icon ?>
            <?php
            // Para mensajes de success, permitir HTML (ya que son controlados por el sistema)
            // Para otros tipos, escapar por seguridad
            $message = $session->getFlashdata($type);
            echo $type === 'success' ? $message : esc($message);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php
// Limpiar mensajes después de mostrarlos
foreach ($messageTypes as $type) {
    if ($session->has($type)) {
        $session->remove($type);
    }
}
?>
