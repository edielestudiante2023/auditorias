<?php

namespace App\Controllers\Consultor;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        helper('auth');

        $data = [
            'title' => 'Panel de Consultor',
            'nombre' => userName(),
            'rol' => currentRoleName(),
        ];

        return view('consultor/dashboard', $data);
    }
}
