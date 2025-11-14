<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ItemsBancoModel;
use App\Models\ClienteModel;
use App\Models\ProveedorModel;
use App\Models\ConsultorModel;
use App\Models\ContratoModel;
use App\Models\UserModel;
use App\Models\ServicioModel;

class DashboardController extends BaseController
{
    public function index()
    {
        helper('auth');

        // Obtener conteos de cada módulo
        $itemsBancoModel = new ItemsBancoModel();
        $clienteModel = new ClienteModel();
        $proveedorModel = new ProveedorModel();
        $consultorModel = new ConsultorModel();
        $contratoModel = new ContratoModel();
        $userModel = new UserModel();
        $servicioModel = new ServicioModel();

        $data = [
            'title' => 'Panel de Administración',
            'nombre' => userName(),
            'rol' => currentRoleName(),
            'rol_id' => userRole(),

            // Contadores
            'total_items_banco' => $itemsBancoModel->countAll(),
            'total_clientes' => $clienteModel->countAll(),
            'total_proveedores' => $proveedorModel->countAll(),
            'total_consultores' => $consultorModel->countAll(),
            'total_contratos' => $contratoModel->countAll(),
            'total_usuarios' => $userModel->countAll(),
            'total_servicios' => $servicioModel->countAll(),
        ];

        return view('admin/dashboard', $data);
    }
}
