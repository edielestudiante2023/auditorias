<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Services\EmailService;

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

    // ================================================================
    // Recuperación de contraseña
    // ================================================================

    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    public function doForgotPassword()
    {
        $email = trim($this->request->getPost('email'));

        if (empty($email)) {
            return redirect()->back()->with('error', 'Ingresa tu correo electrónico.')->withInput();
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->where('estado', 'activo')->first();

        // Siempre mostrar mensaje genérico por seguridad (no revelar si el email existe)
        $mensajeExito = 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.';

        if (!$user) {
            return redirect()->back()->with('success', $mensajeExito);
        }

        // Generar token seguro
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar token en BD
        $userModel->update($user['id_users'], [
            'reset_token'         => hash('sha256', $token),
            'reset_token_expires' => $expira,
        ]);

        // Enviar email con enlace de recuperación
        $urlReset = site_url("reset-password?token={$token}&email=" . urlencode($email));

        $emailService = new EmailService();
        $resultado = $emailService->enviarRecuperacionPassword(
            $email,
            $user['nombre'],
            $urlReset
        );

        if (!$resultado['ok']) {
            log_message('error', "Error enviando email de recuperación a {$email}: " . ($resultado['error'] ?? ''));
        }

        return redirect()->back()->with('success', $mensajeExito);
    }

    public function resetPassword()
    {
        $token = $this->request->getGet('token');
        $email = $this->request->getGet('email');

        if (empty($token) || empty($email)) {
            return redirect()->to('/login')->with('error', 'Enlace de recuperación inválido.');
        }

        return view('auth/reset_password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function doResetPassword()
    {
        $token    = $this->request->getPost('token');
        $email    = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');
        $confirm  = $this->request->getPost('password_confirm');

        // Validaciones básicas
        if (empty($token) || empty($email) || empty($password)) {
            return redirect()->back()->with('error', 'Todos los campos son obligatorios.')->withInput();
        }

        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'La contraseña debe tener al menos 8 caracteres.')->withInput();
        }

        if ($password !== $confirm) {
            return redirect()->back()->with('error', 'Las contraseñas no coinciden.')->withInput();
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->where('estado', 'activo')->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Enlace de recuperación inválido.');
        }

        // Verificar token
        $tokenHash = hash('sha256', $token);
        if (
            empty($user['reset_token']) ||
            !hash_equals($user['reset_token'], $tokenHash)
        ) {
            return redirect()->to('/login')->with('error', 'El enlace de recuperación es inválido o ya fue utilizado.');
        }

        // Verificar expiración
        if (strtotime($user['reset_token_expires']) < time()) {
            return redirect()->to('/forgot-password')->with('error', 'El enlace ha expirado. Solicita uno nuevo.');
        }

        // Actualizar contraseña y limpiar token
        $userModel->update($user['id_users'], [
            'password_hash'       => password_hash($password, PASSWORD_DEFAULT),
            'reset_token'         => null,
            'reset_token_expires' => null,
        ]);

        return redirect()->to('/login')->with('success', 'Tu contraseña ha sido restablecida exitosamente. Ya puedes iniciar sesión.');
    }
}
