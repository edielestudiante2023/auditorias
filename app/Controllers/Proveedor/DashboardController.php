<?php

namespace App\Controllers\Proveedor;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        helper('auth');

        $data = [
            'title' => 'Panel de Proveedor',
            'nombre' => userName(),
            'rol' => currentRoleName(),
        ];

        return view('proveedor/dashboard', $data);
    }
}
