<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro de roles
 * Verifica que el usuario tenga el rol adecuado para acceder a la ruta
 *
 * Roles:
 * 1 = super_admin
 * 2 = consultor
 * 3 = proveedor
 */
class RoleFilter implements FilterInterface
{
    /**
     * Verifica si el usuario tiene el rol requerido
     *
     * @param RequestInterface $request
     * @param array|null $arguments Array de IDs de roles permitidos
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verificar que el usuario esté autenticado primero
        if (!session()->get('logged_in')) {
            return redirect()
                ->to('/login')
                ->with('error', 'Debe iniciar sesión para acceder.');
        }

        // Obtener el rol del usuario desde la sesión
        $userRole = session()->get('id_roles');

        // Si no se especificaron roles permitidos, denegar acceso
        if (empty($arguments)) {
            return redirect()
                ->to('/login')
                ->with('error', 'Acceso denegado.');
        }

        // Verificar si el rol del usuario está en la lista de roles permitidos
        if (!in_array($userRole, $arguments)) {
            // Redirigir al dashboard según el rol
            $dashboardUrl = match($userRole) {
                1 => '/admin/dashboard',
                2 => '/consultor/dashboard',
                3 => '/proveedor/dashboard',
                default => '/login'
            };

            return redirect()
                ->to($dashboardUrl)
                ->with('error', 'No autorizado');
        }

        // El usuario tiene el rol adecuado, permitir acceso
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere procesamiento después de la respuesta
    }
}
