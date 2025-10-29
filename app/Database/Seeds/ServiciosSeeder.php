<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServiciosSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombre' => 'Vigilancia y Seguridad Física',
                'codigo' => 'SRV-VSF-001',
                'descripcion' => 'Servicio de vigilancia y seguridad física en instalaciones',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nombre' => 'Seguridad Industrial',
                'codigo' => 'SRV-SI-002',
                'descripcion' => 'Implementación y seguimiento de normas de seguridad industrial',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'nombre' => 'Gestión de SST',
                'codigo' => 'SRV-SST-003',
                'descripcion' => 'Sistema de Gestión de Seguridad y Salud en el Trabajo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('servicios')->truncate();
        $this->db->table('servicios')->insertBatch($data);

        echo "✓ Servicios demo creados exitosamente\n";
    }
}
