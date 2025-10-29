<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    /**
     * Dashboard general - redirige según el rol del usuario
     */
    public function dashboard()
    {
        helper('auth');

        $role = userRole();

        // Redirigir al dashboard específico según el rol
        switch ($role) {
            case 1: // super_admin
                return redirect()->to('/admin/dashboard');
            case 2: // consultor
                return redirect()->to('/consultor/dashboard');
            case 3: // proveedor
                return redirect()->to('/proveedor/dashboard');
            default:
                return redirect()->to('/login')->with('error', 'Rol no válido.');
        }
    }
}
