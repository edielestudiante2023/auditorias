<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Contraseña: "password123" para todos los usuarios demo
        $passwordHash = password_hash('password123', PASSWORD_BCRYPT);

        $data = [
            [
                'email' => 'admin@cycloidtalent.com',
                'password_hash' => $passwordHash,
                'nombre' => 'Administrador Sistema',
                'id_roles' => 1, // super_admin
                'estado' => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email' => 'consultor@cycloidtalent.com',
                'password_hash' => $passwordHash,
                'nombre' => 'Consultor Demo',
                'id_roles' => 2, // consultor
                'estado' => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email' => 'proveedor@empresa.com',
                'password_hash' => $passwordHash,
                'nombre' => 'Proveedor Demo',
                'id_roles' => 3, // proveedor
                'estado' => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Limpiar tabla antes de insertar (solo en desarrollo)
        $this->db->table('users')->truncate();

        $this->db->table('users')->insertBatch($data);

        echo "✓ Usuarios demo creados exitosamente\n";
        echo "  - admin@cycloidtalent.com (password123) [super_admin]\n";
        echo "  - consultor@cycloidtalent.com (password123) [consultor]\n";
        echo "  - proveedor@empresa.com (password123) [proveedor]\n";
    }
}
