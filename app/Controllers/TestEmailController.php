<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\EmailService;

/**
 * Controlador para probar el servicio de email
 * ELIMINAR EN PRODUCCIÓN
 */
class TestEmailController extends BaseController
{
    protected EmailService $emailService;

    public function __construct()
    {
        $this->emailService = new EmailService();
    }

    /**
     * Muestra formulario de prueba
     */
    public function index()
    {
        return view('test/email_test');
    }

    /**
     * Envía email de prueba de invitación a proveedor
     */
    public function testInviteProveedor()
    {
        $to = $this->request->getPost('email') ?: 'test@example.com';
        $usuario = $this->request->getPost('usuario') ?: 'proveedor_test';
        $clave = $this->request->getPost('clave') ?: 'Temporal123!';
        $urlLogin = site_url('login');
        $urlAuditoria = site_url('proveedor/auditoria/1');

        $result = $this->emailService->sendInviteProveedor(
            $to,
            $usuario,
            $clave,
            $urlLogin,
            $urlAuditoria
        );

        return $this->response->setJSON($result);
    }

    /**
     * Envía email de prueba de proveedor finalizó
     */
    public function testProveedorFinalizo()
    {
        $idAuditoria = $this->request->getPost('id_auditoria') ?: 1;

        $result = $this->emailService->notifyConsultorProveedorFinalizo($idAuditoria);

        return $this->response->setJSON($result);
    }

    /**
     * Envía email de prueba de auditoría cerrada
     */
    public function testCierreAuditoria()
    {
        $idAuditoria = $this->request->getPost('id_auditoria') ?: 1;
        $pathsPdfs = [
            1 => 'uploads/auditorias/1/cliente_1.pdf',
            2 => 'uploads/auditorias/1/cliente_2.pdf'
        ];

        $result = $this->emailService->notifyCierreAuditoria($idAuditoria, $pathsPdfs);

        return $this->response->setJSON($result);
    }

    /**
     * Muestra el último email guardado en logs
     */
    public function verUltimoLog()
    {
        $logDir = WRITEPATH . 'email_logs';

        if (!is_dir($logDir)) {
            return "No hay directorio de logs de email.";
        }

        $files = glob($logDir . '/*.html');

        if (empty($files)) {
            return "No hay emails en logs.";
        }

        // Ordenar por fecha de modificación descendente
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $ultimoArchivo = $files[0];
        $contenido = file_get_contents($ultimoArchivo);

        return $this->response->setBody($contenido);
    }
}
