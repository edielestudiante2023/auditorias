<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NotificacionModel;

class ReportesController extends BaseController
{
    protected $notificacionModel;

    public function __construct()
    {
        $this->notificacionModel = new NotificacionModel();
        helper('auth');
    }

    /**
     * Reporte de emails enviados a clientes con resultados de auditorías
     */
    public function emailsClientes()
    {
        $reporte = $this->notificacionModel->getReporteEmailsClientes();
        $estadisticas = $this->notificacionModel->getEstadisticasEnvio();

        return view('admin/reportes/emails_clientes', [
            'title' => 'Reporte de Emails Enviados a Clientes',
            'reporte' => $reporte,
            'estadisticas' => $estadisticas
        ]);
    }
}
