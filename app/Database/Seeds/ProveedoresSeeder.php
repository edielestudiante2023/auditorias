<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProveedoresSeeder extends Seeder
{
    public function run()
    {
        // Obtener ID del usuario proveedor
        $userProveedor = $this->db->table('users')
            ->where('email', 'proveedor@empresa.com')
            ->get()
            ->getRow();

        $data = [
            [
                'id_users' => $userProveedor ? $userProveedor->id_users : null,
                'razon_social' => 'Seguridad Total SAS',
                'nit' => '9001234567',
                'estado' => 'activo',
                'email_contacto' => 'contacto@seguridadtotal.com',
                'telefono_contacto' => '3001234567',
                'observaciones' => 'Proveedor de servicios de seguridad industrial',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_users' => null,
                'razon_social' => 'Vigilancia Profesional LTDA',
                'nit' => '8009876543',
                'estado' => 'activo',
                'email_contacto' => 'info@vigilanciapro.com',
                'telefono_contacto' => '3109876543',
                'observaciones' => 'Servicios de vigilancia y seguridad privada',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('proveedores')->truncate();
        $this->db->table('proveedores')->insertBatch($data);

        echo "✓ Proveedores demo creados exitosamente\n";
    }
}
