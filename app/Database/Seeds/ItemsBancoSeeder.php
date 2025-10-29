<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemsBancoSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // ÍTEMS GLOBALES (es_por_cliente = 0)
            [
                'codigo' => 'ITEM-GLOB-001',
                'nombre' => 'Política de Seguridad y Salud en el Trabajo',
                'descripcion' => 'Verificar que el proveedor cuente con una política de SST documentada, actualizada y comunicada a todos los trabajadores',
                'es_por_cliente' => 0,
                'orden' => 1,
                'evidencia_requerida' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'codigo' => 'ITEM-GLOB-002',
                'nombre' => 'Certificaciones y Licencias Vigentes',
                'descripcion' => 'Validar que el proveedor mantenga todas las certificaciones y licencias requeridas para la prestación del servicio',
                'es_por_cliente' => 0,
                'orden' => 2,
                'evidencia_requerida' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],

            // ÍTEMS POR CLIENTE (es_por_cliente = 1)
            [
                'codigo' => 'ITEM-CLI-001',
                'nombre' => 'Cumplimiento de Protocolo Específico del Cliente',
                'descripcion' => 'Verificar el cumplimiento de los protocolos y procedimientos específicos definidos por el cliente',
                'es_por_cliente' => 1,
                'orden' => 3,
                'evidencia_requerida' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'codigo' => 'ITEM-CLI-002',
                'nombre' => 'Personal Asignado y Capacitación Cliente Específico',
                'descripcion' => 'Validar que el personal asignado al cliente haya recibido capacitación específica sobre las necesidades y requerimientos del mismo',
                'es_por_cliente' => 1,
                'orden' => 4,
                'evidencia_requerida' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'codigo' => 'ITEM-CLI-003',
                'nombre' => 'Registro de Incidentes en Sitio',
                'descripcion' => 'Revisar el registro y manejo de incidentes ocurridos en las instalaciones del cliente',
                'es_por_cliente' => 1,
                'orden' => 5,
                'evidencia_requerida' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('items_banco')->truncate();
        $this->db->table('items_banco')->insertBatch($data);

        echo "✓ Items banco creados exitosamente\n";
        echo "  - 2 ítems globales\n";
        echo "  - 3 ítems por cliente\n";
    }
}
