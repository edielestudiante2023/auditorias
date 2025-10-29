<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function doLogin()
    {
        $email = trim($this->request->getPost('email'));
        $pass  = (string) $this->request->getPost('password');

        $user = (new UserModel())->where('email', $email)->where('estado','activo')->first();
        if (!$user || !password_verify($pass, $user['password_hash'])) {
            return redirect()->back()->with('error', 'Credenciales inválidas')->withInput();
        }

        session()->set([
            'logged_in' => true,
            'id_users'  => $user['id_users'],
            'email'     => $user['email'],
            'nombre'    => $user['nombre'],
            'id_roles'  => $user['id_roles'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
