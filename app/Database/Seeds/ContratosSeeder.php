<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContratosSeeder extends Seeder
{
    public function run()
    {
        // Obtener IDs
        $proveedor1 = $this->db->table('proveedores')->where('nit', '9001234567')->get()->getRow();
        $proveedor2 = $this->db->table('proveedores')->where('nit', '8009876543')->get()->getRow();

        $cliente1 = $this->db->table('clientes')->where('nit', '9005551234')->get()->getRow();
        $cliente2 = $this->db->table('clientes')->where('nit', '8005559876')->get()->getRow();
        $cliente3 = $this->db->table('clientes')->where('nit', '9005554321')->get()->getRow();

        $servicio1 = $this->db->table('servicios')->where('codigo', 'SRV-VSF-001')->get()->getRow();
        $servicio2 = $this->db->table('servicios')->where('codigo', 'SRV-SI-002')->get()->getRow();

        $data = [
            [
                'id_proveedor' => $proveedor1->id_proveedor,
                'id_cliente' => $cliente1->id_cliente,
                'id_servicio' => $servicio1->id_servicio,
                'estado' => 'activo',
                'numero_contrato' => 'CONT-2025-001',
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_proveedor' => $proveedor1->id_proveedor,
                'id_cliente' => $cliente2->id_cliente,
                'id_servicio' => $servicio2->id_servicio,
                'estado' => 'activo',
                'numero_contrato' => 'CONT-2025-002',
                'fecha_inicio' => '2025-02-01',
                'fecha_fin' => '2025-12-31',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_proveedor' => $proveedor2->id_proveedor,
                'id_cliente' => $cliente3->id_cliente,
                'id_servicio' => $servicio1->id_servicio,
                'estado' => 'activo',
                'numero_contrato' => 'CONT-2025-003',
                'fecha_inicio' => '2025-03-01',
                'fecha_fin' => '2026-02-28',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('contratos')->truncate();
        $this->db->table('contratos')->insertBatch($data);

        echo "✓ Contratos demo creados exitosamente\n";
    }
}
