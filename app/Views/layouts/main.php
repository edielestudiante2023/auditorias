<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Auditorías' ?> | Cycloid Talent</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/brand/favicon.ico?v=2') ?>">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS Global -->
    <link rel="stylesheet" href="<?= base_url('css/custom.css') ?>">

    <!-- Custom CSS -->
    <?= $this->renderSection('styles') ?>

    <!-- Scroll Restoration Script - DEBE ejecutarse lo más pronto posible -->
    <script>
        // Prevenir que el navegador haga scroll automático al recargar
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        // Restaurar posición INMEDIATAMENTE
        (function() {
            const savedScrollPos = sessionStorage.getItem('scrollPosition');
            if (savedScrollPos) {
                // Restaurar en el próximo frame de renderizado
                requestAnimationFrame(function() {
                    window.scrollTo(0, parseInt(savedScrollPos));
                    sessionStorage.removeItem('scrollPosition');
                });
            }
        })();
    </script>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?= site_url('/') ?>">
                <img src="<?= base_url('assets/images/brand/logosst.png') ?>" alt="Cycloid Talent" style="height: 40px; margin-right: 10px;">
                <span class="fw-bold">Sistema de Auditorías</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLogged()): ?>
                        <?php
                        $rolId = userRole();
                        $userName = userName();
                        ?>

                        <?php if ($rolId == 1): ?>
                            <!-- Admin Menu -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('admin/dashboard') ?>">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </li>
                        <?php elseif ($rolId == 2): ?>
                            <!-- Consultor Menu -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('consultor/auditorias') ?>">
                                    <i class="bi bi-file-earmark-check"></i> Auditorías
                                </a>
                            </li>
                        <?php elseif ($rolId == 3): ?>
                            <!-- Proveedor Menu -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('proveedor/auditorias') ?>">
                                    <i class="bi bi-clipboard-check"></i> Mis Auditorías
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= esc($userName) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= site_url('logout') ?>">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= site_url('login') ?>">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid py-4">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="text-white text-center py-4 mt-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container">
            <div class="row">
                <div class="col-md-12 mb-2">
                    <img src="<?= base_url('assets/images/brand/logoenterprisesstdorado.jpg') ?>" alt="Cycloid Talent Enterprise SST" style="height: 50px; opacity: 0.9;">
                </div>
            </div>
            <p class="mb-1"><strong>Cycloid Talent SAS</strong></p>
            <p class="mb-0 small">Sistema de Gestión de Seguridad y Salud en el Trabajo &copy; <?= date('Y') ?></p>
            <p class="mb-0 small text-white-50">Todos los derechos reservados</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>
</html>
