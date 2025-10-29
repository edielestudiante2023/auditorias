<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filtro de autenticación
 * Verifica que el usuario tenga sesión activa
 */
class AuthFilter implements FilterInterface
{
    /**
     * Verifica si el usuario está autenticado
     *
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verificar si existe sesión activa
        if (!session()->get('logged_in')) {
            return redirect()
                ->to('/login')
                ->with('error', 'Debe iniciar sesión para acceder.');
        }
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
