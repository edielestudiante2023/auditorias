<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', static function() {
    return redirect()->to('/login');
});

// ============================================================
// Health Check Endpoint (for server configuration verification)
// ============================================================
$routes->get('/health', function() {
    return json_encode([
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'app' => 'Cycloid Auditorías',
        'version' => '1.0.0',
        'environment' => ENVIRONMENT,
        'base_url' => config('App')->baseURL
    ]);
});

// ============================================================
// Test Email/SendGrid Configuration (TEMPORAL - BORRAR EN PRODUCCIÓN)
// ============================================================
$routes->get('/test-email-config', function() {
    $html = '<h1>Test de Configuración de Email</h1>';

    // Test cURL
    $html .= '<h2>1. Test de cURL</h2>';
    if (function_exists('curl_version')) {
        $version = curl_version();
        $html .= '<p style="color: green;"><strong>✓ cURL está habilitado</strong></p>';
        $html .= '<ul>';
        $html .= '<li><strong>Versión cURL:</strong> ' . $version['version'] . '</li>';
        $html .= '<li><strong>SSL Versión:</strong> ' . $version['ssl_version'] . '</li>';
        $html .= '</ul>';
    } else {
        $html .= '<p style="color: red;"><strong>✗ cURL NO está habilitado</strong></p>';
    }

    // Test PHP
    $html .= '<hr><h2>2. Información de PHP</h2>';
    $html .= '<ul>';
    $html .= '<li><strong>Versión PHP:</strong> ' . phpversion() . '</li>';
    $html .= '<li><strong>Sistema Operativo:</strong> ' . PHP_OS . '</li>';
    $html .= '</ul>';

    // Test Variables de Entorno
    $html .= '<hr><h2>3. Variables de Entorno (SendGrid)</h2>';
    $html .= '<ul>';
    $fromEmail = getenv('email.fromEmail');
    $fromName = getenv('email.fromName');
    $apiKey = getenv('sendgrid.apiKey');

    $html .= '<li><strong>email.fromEmail:</strong> ' . ($fromEmail ?: '<span style="color:red;">NO CONFIGURADO</span>') . '</li>';
    $html .= '<li><strong>email.fromName:</strong> ' . ($fromName ?: '<span style="color:red;">NO CONFIGURADO</span>') . '</li>';
    $html .= '<li><strong>sendgrid.apiKey:</strong> ' . ($apiKey ? '<span style="color:green;">CONFIGURADO (' . strlen($apiKey) . ' caracteres)</span>' : '<span style="color:red;">NO CONFIGURADO</span>') . '</li>';
    $html .= '</ul>';

    // Test Composer SendGrid
    $html .= '<hr><h2>4. Test de SendGrid Package</h2>';
    if (class_exists('SendGrid')) {
        $html .= '<p style="color: green;"><strong>✓ Clase SendGrid encontrada</strong></p>';
    } else {
        $html .= '<p style="color: red;"><strong>✗ Clase SendGrid NO encontrada</strong></p>';
        $html .= '<p>Ejecuta: <code>composer require sendgrid/sendgrid</code></p>';
    }

    return $html;
});

// Rutas de autenticación (sin protección)
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::doLogin');
$routes->get('/logout', 'AuthController::logout');

// ============================================================
// Rutas para servir archivos subidos (requiere autenticación)
// ============================================================
$routes->group('files', ['filter' => 'auth'], function ($routes) {
    $routes->get('firma/(:segment)', 'FileController::servirFirma/$1');
    $routes->get('logo/(:segment)', 'FileController::servirLogo/$1');
    $routes->get('soporte/(:segment)', 'FileController::servirSoporte/$1');
    $routes->get('pdf/(:num)/(:num)', 'FileController::servirPdf/$1/$2'); // auditoría/cliente
});

// Dashboard general (requiere autenticación)
$routes->get('/dashboard', 'Home::dashboard', ['filter' => 'auth']);

// ============================================================
// Test Upload Service (ELIMINAR EN PRODUCCIÓN)
// ============================================================
$routes->group('test-upload', function ($routes) {
    $routes->get('/', 'TestUploadController::index');
    $routes->post('test-firma-consultor', 'TestUploadController::testFirmaConsultor');
    $routes->post('test-logo-cliente', 'TestUploadController::testLogoCliente');
    $routes->post('test-soporte-contrato', 'TestUploadController::testSoporteContrato');
    $routes->post('test-evidencia', 'TestUploadController::testEvidencia');
    $routes->post('test-helpers', 'TestUploadController::testWithHelpers');
    $routes->get('list-uploads', 'TestUploadController::listUploads');
});

// ============================================================
// Limpieza de datos de prueba (ELIMINAR DESPUÉS DE USAR)
// ============================================================
$routes->get('limpiar-datos-prueba', 'LimpiarDatos::index');

// ============================================================
// Test Email Service (ELIMINAR EN PRODUCCIÓN)
// ============================================================
$routes->group('test-email', function ($routes) {
    $routes->get('/', 'TestEmailController::index');
    $routes->post('invite-proveedor', 'TestEmailController::testInviteProveedor');
    $routes->post('proveedor-finalizo', 'TestEmailController::testProveedorFinalizo');
    $routes->post('cierre-auditoria', 'TestEmailController::testCierreAuditoria');
    $routes->get('ver-ultimo-log', 'TestEmailController::verUltimoLog');
});

// ============================================================
// GRUPO: Super Admin (rol 1)
// ============================================================
$routes->group('admin', ['filter' => ['auth', 'role:1']], function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index');
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // ========== Banco de Ítems ==========
    $routes->get('items-banco', 'Admin\ItemsBancoController::index');
    $routes->get('items', 'Admin\ItemsBancoController::index');
    $routes->get('items/crear', 'Admin\ItemsBancoController::crear');
    $routes->post('items/store', 'Admin\ItemsBancoController::store');
    $routes->get('items/editar/(:num)', 'Admin\ItemsBancoController::editar/$1');
    $routes->post('items/update/(:num)', 'Admin\ItemsBancoController::update/$1');
    $routes->post('items/toggle/(:num)', 'Admin\ItemsBancoController::toggle/$1');
    $routes->post('items/eliminar/(:num)', 'Admin\ItemsBancoController::eliminar/$1');
    $routes->get('items/reordenar', 'Admin\ItemsBancoController::reordenar');
    $routes->post('items/updateOrden', 'Admin\ItemsBancoController::updateOrden');

    // ========== Consultores ==========
    $routes->get('consultores', 'Admin\ConsultoresController::index');
    $routes->get('consultores/crear', 'Admin\ConsultoresController::crear');
    $routes->post('consultores/store', 'Admin\ConsultoresController::store');
    $routes->get('consultores/editar/(:num)', 'Admin\ConsultoresController::editar/$1');
    $routes->post('consultores/update/(:num)', 'Admin\ConsultoresController::update/$1');
    $routes->post('consultores/eliminar/(:num)', 'Admin\ConsultoresController::eliminar/$1');
    $routes->post('consultores/eliminarFirma/(:num)', 'Admin\ConsultoresController::eliminarFirma/$1');

    // Rutas REST-like sencillas (independientes de users)
    $routes->get('consultores/create', 'Admin\ConsultoresController::create');
    $routes->post('consultores', 'Admin\ConsultoresController::storeSimple');
    $routes->get('consultores/(:num)/edit', 'Admin\ConsultoresController::edit/$1');
    $routes->post('consultores/(:num)', 'Admin\ConsultoresController::updateSimple/$1');
    $routes->post('consultores/(:num)/delete', 'Admin\ConsultoresController::delete/$1');

    // ========== Clientes ==========
    $routes->get('clientes', 'Admin\ClientesController::index');
    $routes->get('clientes/crear', 'Admin\ClientesController::crear');
    $routes->post('clientes/guardar', 'Admin\ClientesController::guardar');
    $routes->get('clientes/(:num)/editar', 'Admin\ClientesController::editar/$1');
    $routes->post('clientes/(:num)/update', 'Admin\ClientesController::update/$1');
    $routes->post('clientes/(:num)/eliminar', 'Admin\ClientesController::eliminar/$1');
    $routes->post('clientes/(:num)/toggle', 'Admin\ClientesController::toggle/$1');
    $routes->get('clientes/eliminar-logo/(:num)', 'Admin\ClientesController::eliminarLogo/$1');

    // ========== Proveedores ==========
    // DEBUG: Ruta temporal para diagnóstico
    $routes->get('proveedores/debug', 'Admin\\ProveedoresController::debug');

    // Rutas REST-like solicitadas (patrón Clientes)
    $routes->get('proveedores', 'Admin\\ProveedoresController::index2');
    $routes->get('proveedores/create', 'Admin\\ProveedoresController::create');
    $routes->post('proveedores', 'Admin\\ProveedoresController::storeNew');
    $routes->get('proveedores/(:num)/edit', 'Admin\\ProveedoresController::edit/$1');
    $routes->post('proveedores/(:num)', 'Admin\\ProveedoresController::updateNew/$1');
    $routes->post('proveedores/(:num)/delete', 'Admin\\ProveedoresController::delete/$1');
    $routes->get('proveedores', 'Admin\ProveedoresController::index');
    $routes->get('proveedores/crear', 'Admin\ProveedoresController::crear');
    $routes->post('proveedores/store', 'Admin\ProveedoresController::store');
    $routes->get('proveedores/editar/(:num)', 'Admin\ProveedoresController::editar/$1');
    $routes->post('proveedores/update/(:num)', 'Admin\ProveedoresController::update/$1');
    $routes->post('proveedores/eliminar/(:num)', 'Admin\ProveedoresController::eliminar/$1');

    // ========== Contratos ==========
    $routes->get('contratos', 'Admin\ContratosController::index');
    $routes->get('contratos/crear', 'Admin\ContratosController::crear');
    $routes->post('contratos/store', 'Admin\ContratosController::store');
    $routes->get('contratos/editar/(:num)', 'Admin\ContratosController::editar/$1');
    $routes->post('contratos/update/(:num)', 'Admin\ContratosController::update/$1');
    $routes->post('contratos/eliminar/(:num)', 'Admin\ContratosController::eliminar/$1');
    $routes->post('contratos/eliminar-soporte/(:num)', 'Admin\ContratosController::eliminarSoporte/$1');
    $routes->get('contratos/usuarios-proveedor/(:num)', 'Admin\ContratosController::getUsuariosByProveedor/$1');

    // ========== Usuarios ==========
    $routes->get('usuarios', 'Admin\\UsuariosController::index');
    $routes->get('usuarios/create', 'Admin\\UsuariosController::create');
    $routes->post('usuarios', 'Admin\\UsuariosController::store');
    $routes->get('usuarios/(:num)/edit', 'Admin\\UsuariosController::edit/$1');
    $routes->post('usuarios/(:num)', 'Admin\\UsuariosController::update/$1');
    $routes->post('usuarios/(:num)/reset-password', 'Admin\\UsuariosController::resetPassword/$1');
    $routes->post('usuarios/(:num)/delete', 'Admin\\UsuariosController::delete/$1');

    // ========== Servicios ==========
    $routes->get('servicios', 'Admin\\ServiciosController::index');
    $routes->get('servicios/create', 'Admin\\ServiciosController::create');
    $routes->post('servicios/store', 'Admin\\ServiciosController::store');
    $routes->get('servicios/edit/(:num)', 'Admin\\ServiciosController::edit/$1');
    $routes->post('servicios/update/(:num)', 'Admin\\ServiciosController::update/$1');
    $routes->post('servicios/delete/(:num)', 'Admin\\ServiciosController::delete/$1');

    // API: Validación de email
    $routes->get('api/check-email', 'Admin\\UsuariosController::checkEmail');
    $routes->post('api/check-email', 'Admin\\UsuariosController::checkEmail');

    // ========== Auditorías (vistas de supervisión) ==========
    $routes->get('auditorias/completadas-proveedores', 'Admin\\AuditoriasController::completadasProveedores');
    $routes->get('auditorias/pendientes-proveedores', 'Admin\\AuditoriasController::pendientesProveedores');
    $routes->get('auditorias/revision-consultores', 'Admin\\AuditoriasController::revisionConsultores');
    $routes->get('auditorias/reporte-progreso', 'Admin\\AuditoriasController::reporteProgreso');
    $routes->get('auditorias/reporte-clientes', 'Admin\\AuditoriasController::reporteClientes');
    $routes->get('auditorias/(:num)/clientes', 'Admin\\AuditoriasController::getClientes/$1');

    // ========== Auditorías Cerradas - Gestión y Reapertura ==========
    $routes->get('auditorias/cerradas', 'Admin\\AuditoriasController::cerradas');
    $routes->get('auditorias/historial-reaperturas', 'Admin\\AuditoriasController::historialReaperturas');
    $routes->post('auditorias/(:num)/reabrir', 'Admin\\AuditoriasController::reabrir/$1');
    $routes->get('auditorias/(:num)/adicionar-clientes', 'Admin\\AuditoriasController::adicionarClientes/$1');
    $routes->post('auditorias/(:num)/adicionar-clientes', 'Admin\\AuditoriasController::procesarAdicionClientes/$1');
    $routes->post('auditorias/(:num)/reenviar-credenciales', 'Admin\\AuditoriasController::reenviarCredenciales/$1');
    $routes->post('auditorias/(:num)/eliminar', 'Admin\\AuditoriasController::eliminar/$1');

    // ========== Reportes ==========
    $routes->get('reportes/emails-clientes', 'Admin\\ReportesController::emailsClientes');
});

// ============================================================
// GRUPO: Consultor (rol 2)
// ============================================================
$routes->group('consultor', ['filter' => 'role:2'], function ($routes) {
    $routes->get('/', 'Consultor\DashboardController::index');
    $routes->get('dashboard', 'Consultor\DashboardController::index');

    // ========== Auditorías Consultor ==========
    $routes->get('auditorias', 'Consultor\AuditoriasConsultorController::index');
    $routes->get('auditorias/pendientes', 'Consultor\AuditoriasConsultorController::pendientes');
    $routes->get('reportes', 'Consultor\AuditoriasConsultorController::reportes');
    $routes->get('perfil', 'Consultor\AuditoriasConsultorController::perfil');
    $routes->post('perfil/actualizar', 'Consultor\AuditoriasConsultorController::actualizarPerfil');

    // Setup de Auditoría (4 pasos)
    $routes->get('auditorias/crear', 'Consultor\AuditoriasSetupController::crear');
    $routes->post('auditorias/guardar', 'Consultor\AuditoriasSetupController::guardar');
    $routes->get('auditorias/(:num)/seleccionar-items', 'Consultor\AuditoriasSetupController::seleccionarItems/$1');
    $routes->post('auditorias/(:num)/guardar-items', 'Consultor\AuditoriasSetupController::guardarItems/$1');
    $routes->get('auditorias/(:num)/asignar-clientes-setup', 'Consultor\AuditoriasSetupController::asignarClientesSetup/$1');
    $routes->post('auditorias/(:num)/asignar-clientes-setup', 'Consultor\AuditoriasSetupController::guardarClientesSetup/$1');
    $routes->get('auditorias/(:num)/enviar-invitacion', 'Consultor\AuditoriasSetupController::formEnviarInvitacion/$1');
    $routes->post('auditorias/(:num)/enviar-invitacion', 'Consultor\AuditoriasSetupController::enviarInvitacion/$1');
    $routes->post('auditorias/(:num)/reenviar-email', 'Consultor\AuditoriasSetupController::reenviarEmail/$1');

    // Editar Auditoría (fecha de vencimiento)
    $routes->get('auditorias/(:num)/editar', 'Consultor\AuditoriasSetupController::editar/$1');
    $routes->post('auditorias/(:num)/actualizar', 'Consultor\AuditoriasSetupController::actualizar/$1');

    // Revisión de Auditoría
    $routes->get('auditoria/(:num)', 'Consultor\AuditoriasConsultorController::detalle/$1');
    $routes->post('auditoria/item/(:num)/calificar-global', 'Consultor\AuditoriasConsultorController::calificarItemGlobal/$1');
    $routes->post('auditoria/item/(:num)/calificar-por-cliente/(:num)', 'Consultor\AuditoriasConsultorController::calificarItemPorCliente/$1/$2');
    $routes->get('auditoria/(:num)/asignar-clientes', 'Consultor\AuditoriasConsultorController::asignarClientes/$1');
    $routes->post('auditoria/(:num)/asignar-clientes', 'Consultor\AuditoriasConsultorController::guardarClientes/$1');
    $routes->post('auditoria/(:num)/override', 'Consultor\AuditoriasConsultorController::override/$1');
    $routes->post('auditoria/(:num)/cerrar', 'Consultor\AuditoriasConsultorController::cerrar/$1');

    // Ver evidencias (globales y por cliente)
    $routes->get('evidencia/(:num)/ver', 'Consultor\AuditoriasConsultorController::verEvidencia/$1');
    $routes->get('evidencia-cliente/(:num)/ver', 'Consultor\AuditoriasConsultorController::verEvidenciaCliente/$1');

    // Descargar y enviar PDFs bajo demanda
    $routes->get('auditoria/(:num)/cliente/(:num)/descargar-pdf', 'Consultor\AuditoriasConsultorController::descargarPdfCliente/$1/$2');
    $routes->post('auditoria/(:num)/cliente/(:num)/enviar-pdf', 'Consultor\AuditoriasConsultorController::enviarPdfCliente/$1/$2');

    // Adicionar clientes a auditoría existente
    $routes->get('auditorias/(:num)/adicionar-clientes', 'Admin\\AuditoriasController::adicionarClientes/$1');
    $routes->post('auditorias/(:num)/adicionar-clientes', 'Admin\\AuditoriasController::procesarAdicionClientes/$1');
    $routes->post('auditorias/(:num)/reenviar-credenciales', 'Admin\\AuditoriasController::reenviarCredenciales/$1');

    // Eliminar auditoría (solo borrador o en_proveedor)
    $routes->post('auditorias/(:num)/eliminar', 'Consultor\AuditoriasConsultorController::eliminar/$1');
});

// ============================================================
// GRUPO: Proveedor (rol 3)
// ============================================================
$routes->group('proveedor', ['filter' => 'role:3'], function ($routes) {
    $routes->get('/', 'Proveedor\DashboardController::index');
    $routes->get('dashboard', 'Proveedor\DashboardController::index');

    // ========== Auditorías Proveedor ==========
    $routes->get('auditorias', 'Proveedor\AuditoriasProveedorController::index');
    $routes->get('auditorias/completadas', 'Proveedor\AuditoriasProveedorController::completadas');
    $routes->get('evidencias', 'Proveedor\AuditoriasProveedorController::evidencias');
    $routes->get('evidencia/(:num)/ver', 'Proveedor\AuditoriasProveedorController::verEvidencia/$1');
    $routes->get('evidencia-cliente/(:num)/ver', 'Proveedor\AuditoriasProveedorController::verEvidenciaCliente/$1');
    $routes->get('empresa', 'Proveedor\AuditoriasProveedorController::empresa');
    $routes->get('auditoria/(:num)', 'Proveedor\AuditoriasProveedorController::wizard/$1');
    $routes->post('auditoria/(:num)/item/(:num)/guardar', 'Proveedor\AuditoriasProveedorController::guardarItem/$1/$2');
    $routes->post('auditoria/(:num)/evidencia/(:num)/eliminar', 'Proveedor\AuditoriasProveedorController::deleteEvidencia/$1/$2');
    $routes->post('auditoria/(:num)/evidencia-cliente/(:num)/eliminar', 'Proveedor\AuditoriasProveedorController::deleteEvidenciaCliente/$1/$2');
    $routes->post('auditoria/(:num)/finalizar', 'Proveedor\AuditoriasProveedorController::finalizar/$1');

    // ========== Gestión de Personal Asignado ==========
    $routes->get('personal', 'Proveedor\PersonalController::index');
    $routes->get('personal/cliente/(:num)', 'Proveedor\PersonalController::gestionarCliente/$1');
    $routes->post('personal/guardar/(:num)', 'Proveedor\PersonalController::guardar/$1');
    $routes->get('personal/obtener/(:num)', 'Proveedor\PersonalController::obtener/$1');
    $routes->post('personal/cambiar-estado/(:num)', 'Proveedor\PersonalController::cambiarEstado/$1');
    $routes->post('personal/eliminar/(:num)', 'Proveedor\PersonalController::eliminar/$1');
});





