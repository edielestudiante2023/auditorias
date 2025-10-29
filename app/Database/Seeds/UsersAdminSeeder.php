<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersAdminSeeder extends Seeder
{
    public function run()
    {
        $hash = password_hash('Admin123*', PASSWORD_DEFAULT);

        $this->db->table('users')->insert([
            'email' => 'superadmin@cycloidtalent.com',
            'password_hash' => $hash,
            'nombre' => 'Super Admin',
            'id_roles' => 1,           // super_admin
            'estado' => 'activo',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
