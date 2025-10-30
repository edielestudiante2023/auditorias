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

        // Datos de ejemplo de ítems
        $itemsEjemplo = [
            [
                'titulo' => 'Política de Seguridad y Salud en el Trabajo',
                'descripcion' => 'Evidenciar la existencia de una política de SST firmada por el representante legal, actualizada y comunicada a todos los niveles de la organización.',
                'alcance' => 'global'
            ],
            [
                'titulo' => 'Matriz de Identificación de Peligros',
                'descripcion' => 'Presentar matriz IPEVR actualizada con la identificación de peligros, evaluación y valoración de riesgos por procesos.',
                'alcance' => 'por_cliente'
            ],
            [
                'titulo' => 'Plan de Trabajo Anual',
                'descripcion' => 'Presentar el plan de trabajo anual del SG-SST con objetivos, metas, responsables y recursos asignados.',
                'alcance' => 'global'
            ]
        ];

        // Datos de ejemplo de clientes
        $clientesEjemplo = [
            [
                'id_cliente' => 1,
                'razon_social' => 'Empresa Ejemplo S.A.S.',
                'nit' => '900123456-1'
            ],
            [
                'id_cliente' => 2,
                'razon_social' => 'Corporación Demo Ltda.',
                'nit' => '800987654-3'
            ],
            [
                'id_cliente' => 3,
                'razon_social' => 'Industrias Test S.A.',
                'nit' => '700555444-9'
            ]
        ];

        $result = $this->emailService->sendInviteProveedor(
            $to,
            $usuario,
            $clave,
            $urlLogin,
            $urlAuditoria,
            'Proveedor de Prueba',
            $itemsEjemplo,
            $clientesEjemplo
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
