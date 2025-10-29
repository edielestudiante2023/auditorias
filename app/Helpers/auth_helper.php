<?php

/**
 * Auth Helper
 * Funciones auxiliares para verificar autenticación y roles
 */

if (!function_exists('isLogged')) {
    /**
     * Verifica si el usuario está autenticado
     *
     * @return bool
     */
    function isLogged(): bool
    {
        return (bool) session()->get('logged_in');
    }
}

if (!function_exists('userId')) {
    /**
     * Obtiene el ID del usuario autenticado
     *
     * @return int|null
     */
    function userId(): ?int
    {
        return session()->get('id_users');
    }
}

if (!function_exists('userRole')) {
    /**
     * Obtiene el ID del rol del usuario autenticado
     *
     * @return int|null
     */
    function userRole(): ?int
    {
        return session()->get('id_roles');
    }
}

if (!function_exists('userEmail')) {
    /**
     * Obtiene el email del usuario autenticado
     *
     * @return string|null
     */
    function userEmail(): ?string
    {
        return session()->get('email');
    }
}

if (!function_exists('userName')) {
    /**
     * Obtiene el nombre del usuario autenticado
     *
     * @return string|null
     */
    function userName(): ?string
    {
        return session()->get('nombre');
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Verifica si el usuario es super admin (rol 1)
     *
     * @return bool
     */
    function isSuperAdmin(): bool
    {
        return userRole() === 1;
    }
}

if (!function_exists('isConsultor')) {
    /**
     * Verifica si el usuario es consultor (rol 2)
     *
     * @return bool
     */
    function isConsultor(): bool
    {
        return userRole() === 2;
    }
}

if (!function_exists('isProveedor')) {
    /**
     * Verifica si el usuario es proveedor (rol 3)
     *
     * @return bool
     */
    function isProveedor(): bool
    {
        return userRole() === 3;
    }
}

if (!function_exists('hasRole')) {
    /**
     * Verifica si el usuario tiene uno de los roles especificados
     *
     * @param int|array $roles ID de rol o array de IDs de roles
     * @return bool
     */
    function hasRole($roles): bool
    {
        $userRole = userRole();

        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        return $userRole === $roles;
    }
}

if (!function_exists('roleNameById')) {
    /**
     * Obtiene el nombre del rol por su ID
     *
     * @param int $roleId
     * @return string
     */
    function roleNameById(int $roleId): string
    {
        $roles = [
            1 => 'Super Admin',
            2 => 'Consultor',
            3 => 'Proveedor',
        ];

        return $roles[$roleId] ?? 'Desconocido';
    }
}

if (!function_exists('currentRoleName')) {
    /**
     * Obtiene el nombre del rol del usuario actual
     *
     * @return string
     */
    function currentRoleName(): string
    {
        $roleId = userRole();
        return $roleId ? roleNameById($roleId) : 'Invitado';
    }
}

if (!function_exists('can')) {
    /**
     * Verifica si el usuario tiene un permiso específico
     * Por ahora, verifica si el usuario es super admin (rol 1)
     * En el futuro puede extenderse para verificar permisos específicos
     *
     * @param string $permission Nombre del permiso (ej: 'admin.clientes.create')
     * @return bool
     */
    function can(string $permission): bool
    {
        $userRole = userRole();

        // Super admin tiene todos los permisos
        if ($userRole === 1) {
            return true;
        }

        // Permisos específicos por rol pueden agregarse aquí
        // Ejemplo:
        // $permissions = [
        //     2 => ['consultor.auditorias.create', 'consultor.auditorias.edit'],
        //     3 => ['proveedor.auditorias.view']
        // ];
        // return in_array($permission, $permissions[$userRole] ?? []);

        return false;
    }
}

if (!function_exists('generateSecurePassword')) {
    /**
     * Genera una contraseña segura aleatoria
     *
     * @param int $length Longitud de la contraseña (mínimo 8)
     * @return string
     */
    function generateSecurePassword(int $length = 12): string
    {
        if ($length < 8) {
            $length = 8;
        }

        // Caracteres que se usarán
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $special = '!@#$%&*';

        // Asegurar que tenga al menos uno de cada tipo
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Completar el resto de la contraseña
        $allChars = $lowercase . $uppercase . $numbers . $special;
        $remaining = $length - 4;

        for ($i = 0; $i < $remaining; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Mezclar la contraseña
        $password = str_shuffle($password);

        return $password;
    }
}
