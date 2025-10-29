<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'id_roles' => 1,
                'nombre' => 'super_admin',
            ],
            [
                'id_roles' => 2,
                'nombre' => 'consultor',
            ],
            [
                'id_roles' => 3,
                'nombre' => 'proveedor',
            ],
        ];

        // Usar replace para evitar duplicados
        foreach ($data as $role) {
            $this->db->table('roles')->replace($role);
        }

        echo "✓ Roles creados exitosamente\n";
    }
}
