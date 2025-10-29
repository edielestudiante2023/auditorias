<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Security\Exceptions\SecurityException;

class CsrfExceptionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // No se hace nada antes
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Capturar excepciones CSRF y redirigir con mensaje amigable
        $exception = service('exceptions')->getException();

        if ($exception instanceof SecurityException) {
            return redirect()->back()
                ->with('error', 'Tu sesión ha expirado o el formulario no es válido. Por favor, intenta nuevamente.')
                ->withInput();
        }

        return $response;
    }
}
