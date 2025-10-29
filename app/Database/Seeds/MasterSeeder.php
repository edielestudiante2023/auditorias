<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder maestro que ejecuta todos los seeders en el orden correcto
 */
class MasterSeeder extends Seeder
{
    public function run()
    {
        echo "\n=== INICIANDO SEEDERS ===\n\n";

        // 1. Roles (base)
        echo "1. Roles...\n";
        $this->call('RolesSeeder');

        // 2. Usuarios (depende de roles)
        echo "\n2. Usuarios...\n";
        $this->call('UsersSeeder');

        // 3. Proveedores (depende de users)
        echo "\n3. Proveedores...\n";
        $this->call('ProveedoresSeeder');

        // 4. Clientes (independiente)
        echo "\n4. Clientes...\n";
        $this->call('ClientesSeeder');

        // 5. Servicios (independiente)
        echo "\n5. Servicios...\n";
        $this->call('ServiciosSeeder');

        // 6. Contratos (depende de proveedores, clientes, servicios)
        echo "\n6. Contratos...\n";
        $this->call('ContratosSeeder');

        // 7. Items Banco (independiente)
        echo "\n7. Items Banco...\n";
        $this->call('ItemsBancoSeeder');

        echo "\n=== SEEDERS COMPLETADOS ===\n\n";
        echo "Base de datos poblada exitosamente con datos demo.\n";
        echo "Usuarios disponibles:\n";
        echo "  - admin@cycloidtalent.com / password123\n";
        echo "  - consultor@cycloidtalent.com / password123\n";
        echo "  - proveedor@empresa.com / password123\n\n";
    }
}
