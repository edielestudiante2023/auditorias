<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClientesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'razon_social' => 'Industrias ABC SA',
                'nit' => '9005551234',
                'estado' => 'activo',
                'email_contacto' => 'contacto@industriasabc.com',
                'telefono_contacto' => '3201234567',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'razon_social' => 'Corporación XYZ LTDA',
                'nit' => '8005559876',
                'estado' => 'activo',
                'email_contacto' => 'info@corporacionxyz.com',
                'telefono_contacto' => '3109876543',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'razon_social' => 'Manufacturas DEF SAS',
                'nit' => '9005554321',
                'estado' => 'activo',
                'email_contacto' => 'contacto@manufacturasdef.com',
                'telefono_contacto' => '3154321098',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('clientes')->truncate();
        $this->db->table('clientes')->insertBatch($data);

        echo "✓ Clientes demo creados exitosamente\n";
    }
}
