<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="text-center py-5">
    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 5rem;"></i>
    <h2 class="mt-4">Sin Proveedor Asignado</h2>
    <p class="text-muted">
        Tu usuario no está vinculado a ningún proveedor en el sistema.
        <br>
        Por favor contacta al administrador para que vincule tu cuenta.
    </p>
    <a href="<?= site_url('logout') ?>" class="btn btn-primary mt-3">
        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
    </a>
</div>

<?= $this->endSection() ?>
