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

    <!-- Sistema de inactividad para proveedores -->
    <?php if (isLogged() && userRole() == 3): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            const TIEMPO_INACTIVIDAD = 5 * 60 * 1000; // 5 minutos en milisegundos
            let ultimaActividad = Date.now();
            let intervalo;
            let alertaMostrada = false;
            let timerInterval;

            console.log('[INACTIVIDAD] Sistema de inactividad iniciado para proveedor');
            console.log('[INACTIVIDAD] ⚠️ La sesión se cerrará después de 5 minutos SIN interacción en esta pestaña');

            function reiniciarTemporizador() {
                if (alertaMostrada) return; // No reiniciar si ya se mostró la alerta

                // SOLO reiniciar si la pestaña está visible (usuario está activamente en la pestaña)
                if (document.hidden) {
                    return; // Si está en otra pestaña/ventana, NO reiniciar (sigue contando inactividad)
                }

                ultimaActividad = Date.now();
            }

            function verificarInactividad() {
                const tiempoTranscurrido = Date.now() - ultimaActividad;
                const minutosInactivo = Math.floor(tiempoTranscurrido / 60000);
                const segundosInactivo = Math.floor((tiempoTranscurrido % 60000) / 1000);

                // Log cada minuto
                if (segundosInactivo === 0 && minutosInactivo > 0) {
                    console.log(`[INACTIVIDAD] Tiempo sin interacción en pestaña: ${minutosInactivo} minuto(s)`);
                }

                // Si han pasado 5 minutos SIN INTERACCIÓN en la pestaña
                if (tiempoTranscurrido >= TIEMPO_INACTIVIDAD && !alertaMostrada) {
                    alertaMostrada = true;
                    console.log('[INACTIVIDAD] ⚠️ 5 minutos sin interacción en la pestaña. Mostrando alerta...');

                    // Detener el intervalo de verificación
                    clearInterval(intervalo);

                    // Mostrar alerta (incluso si la pestaña no está visible)
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesión Inactiva',
                        html: '<p>Llevas <strong>5 minutos sin actividad</strong>.</p><p>Por seguridad, tu sesión será cerrada en <b>5</b> segundos.</p>',
                        showConfirmButton: true,
                        confirmButtonText: 'Entendido',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        confirmButtonColor: '#ffc107',
                        timer: 5000,
                        timerProgressBar: true,
                        didOpen: () => {
                            const b = Swal.getHtmlContainer().querySelector('b');
                            timerInterval = setInterval(() => {
                                const tiempoRestante = Math.ceil(Swal.getTimerLeft() / 1000);
                                if (b && tiempoRestante > 0) {
                                    b.textContent = tiempoRestante;
                                }
                            }, 100);
                        },
                        willClose: () => {
                            if (timerInterval) {
                                clearInterval(timerInterval);
                            }
                        }
                    }).then(() => {
                        console.log('[INACTIVIDAD] Redirigiendo al logout...');
                        window.location.href = '<?= site_url('logout') ?>';
                    });
                }
            }

            // Detectar cuando la pestaña se muestra (para logging)
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    console.log('[INACTIVIDAD] 🔴 Pestaña oculta - el temporizador SIGUE corriendo');
                } else {
                    const tiempoTranscurrido = Date.now() - ultimaActividad;
                    const minutosInactivo = Math.floor(tiempoTranscurrido / 60000);
                    console.log(`[INACTIVIDAD] 🟢 Pestaña visible - tiempo sin interacción: ${minutosInactivo} minuto(s)`);
                }
            });

            // Eventos que detectan actividad del usuario
            // SOLO cuenta como actividad si está ACTIVAMENTE en la pestaña visible
            let mouseMoveTimeout;
            document.addEventListener('mousemove', function() {
                if (document.hidden) return; // Ignorar si la pestaña no está visible
                clearTimeout(mouseMoveTimeout);
                mouseMoveTimeout = setTimeout(reiniciarTemporizador, 1000);
            }, true);

            // Otros eventos reinician inmediatamente (solo si pestaña visible)
            const eventosInmediatos = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
            eventosInmediatos.forEach(evento => {
                document.addEventListener(evento, reiniciarTemporizador, true);
            });

            // Iniciar el temporizador de inactividad (verificar cada segundo)
            // SIEMPRE corre, incluso si la pestaña está oculta
            intervalo = setInterval(verificarInactividad, 1000);

            // Limpiar al cerrar la página
            window.addEventListener('beforeunload', function() {
                clearInterval(intervalo);
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
            });
        })();
    </script>
    <?php endif; ?>

    <!-- Custom Scripts -->
    <?= $this->renderSection('scripts') ?>
</body>
</html>
