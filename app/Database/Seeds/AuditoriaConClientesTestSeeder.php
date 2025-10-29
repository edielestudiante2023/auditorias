<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder de prueba para auditorías con clientes desde contratos
 *
 * IMPORTANTE: Este seeder asume que ya existen:
 * - Proveedores (id_proveedor = 1, 2)
 * - Clientes (id_cliente = 1, 2, 3)
 * - Servicios (id_servicio = 1, 2)
 * - Consultores (id_consultor = 1)
 * - Items en items_banco (al menos 5 items con diferentes alcances)
 *
 * Para ejecutar:
 * php spark db:seed AuditoriaConClientesTestSeeder
 */
class AuditoriaConClientesTestSeeder extends Seeder
{
    public function run()
    {
        $db = $this->db;

        // ===============================================
        // 1. CREAR CONTRATOS ACTIVOS
        // ===============================================
        echo "Creando contratos de prueba...\n";

        $contratos = [
            [
                'id_proveedor' => 1,
                'id_cliente' => 1,
                'id_servicio' => 1,
                'numero_personas' => 50,
                'estado' => 'activo',
                'observaciones' => 'Contrato de vigilancia',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_proveedor' => 1,
                'id_cliente' => 2,
                'id_servicio' => 1,
                'numero_personas' => 30,
                'estado' => 'activo',
                'observaciones' => 'Contrato de vigilancia',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_proveedor' => 1,
                'id_cliente' => 3,
                'id_servicio' => 2,
                'numero_personas' => 20,
                'estado' => 'activo',
                'observaciones' => 'Contrato de aseo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($contratos as $contrato) {
            $db->table('contratos_proveedor_cliente')->insert($contrato);
        }

        $idContrato1 = $db->insertID();
        $idContrato2 = $idContrato1 + 1;
        $idContrato3 = $idContrato1 + 2;

        echo "Contratos creados: IDs {$idContrato1}, {$idContrato2}, {$idContrato3}\n";

        // ===============================================
        // 2. CREAR AUDITORÍA EN ESTADO 'en_proveedor'
        // ===============================================
        echo "Creando auditoría de prueba...\n";

        $auditoria = [
            'id_proveedor' => 1,
            'id_consultor' => 1,
            'codigo_formato' => 'AUD-TEST-001',
            'version_formato' => 'v1.0',
            'fecha_programada' => date('Y-m-d', strtotime('+7 days')),
            'estado' => 'en_proveedor',
            'fecha_envio_proveedor' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $db->table('auditorias')->insert($auditoria);
        $idAuditoria = $db->insertID();

        echo "Auditoría creada: ID {$idAuditoria}\n";

        // ===============================================
        // 3. INSERTAR SNAPSHOT EN auditoria_clientes
        // ===============================================
        echo "Insertando snapshot de clientes en auditoria_clientes...\n";

        $auditoriaClientes = [
            [
                'id_auditoria' => $idAuditoria,
                'id_cliente' => 1,
                'id_contrato' => $idContrato1,
                'id_servicio' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_auditoria' => $idAuditoria,
                'id_cliente' => 2,
                'id_contrato' => $idContrato2,
                'id_servicio' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_auditoria' => $idAuditoria,
                'id_cliente' => 3,
                'id_contrato' => $idContrato3,
                'id_servicio' => 2,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($auditoriaClientes as $ac) {
            $db->table('auditoria_clientes')->insert($ac);
        }

        echo "Snapshot de 3 clientes insertado\n";

        // ===============================================
        // 4. CREAR ÍTEMS GLOBALES EN auditoria_items
        // ===============================================
        echo "Creando ítems globales...\n";

        $itemsGlobales = $db->table('items_banco')
            ->whereIn('alcance', ['global', 'mixto'])
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->limit(3)
            ->get()
            ->getResultArray();

        foreach ($itemsGlobales as $item) {
            $db->table('auditoria_items')->insert([
                'id_auditoria' => $idAuditoria,
                'id_item' => $item['id_item'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo count($itemsGlobales) . " ítems globales creados\n";

        // ===============================================
        // 5. CREAR ÍTEMS POR CLIENTE
        // ===============================================
        echo "Creando ítems por cliente...\n";

        $itemsPorCliente = $db->table('items_banco')
            ->where('alcance', 'por_cliente')
            ->where('activo', 1)
            ->orderBy('orden', 'ASC')
            ->limit(2)
            ->get()
            ->getResultArray();

        $clientesIds = [1, 2, 3];

        foreach ($itemsPorCliente as $item) {
            // Crear registro en auditoria_items
            $db->table('auditoria_items')->insert([
                'id_auditoria' => $idAuditoria,
                'id_item' => $item['id_item'],
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $idAuditoriaItem = $db->insertID();

            // Crear un registro por cada cliente en auditoria_item_cliente
            foreach ($clientesIds as $idCliente) {
                $db->table('auditoria_item_cliente')->insert([
                    'id_auditoria_item' => $idAuditoriaItem,
                    'id_cliente' => $idCliente,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        echo count($itemsPorCliente) . " ítems por cliente creados (x3 clientes cada uno)\n";

        // ===============================================
        // 6. RESUMEN
        // ===============================================
        echo "\n========================================\n";
        echo "SEEDER COMPLETADO EXITOSAMENTE\n";
        echo "========================================\n";
        echo "Auditoría ID: {$idAuditoria}\n";
        echo "Estado: en_proveedor\n";
        echo "Contratos: 3 (IDs {$idContrato1}, {$idContrato2}, {$idContrato3})\n";
        echo "Clientes en snapshot: 3\n";
        echo "Ítems globales: " . count($itemsGlobales) . "\n";
        echo "Ítems por cliente: " . count($itemsPorCliente) . " (cada uno con 3 clientes)\n";
        echo "\nPuedes acceder al wizard del proveedor en:\n";
        echo "/proveedor/auditoria/{$idAuditoria}\n";
        echo "========================================\n";
    }
}
