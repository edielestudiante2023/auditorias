<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * AdminQuickSeed
 *
 * Seeder rápido para crear datos de prueba del módulo Admin:
 * - 3 Clientes
 * - 2 Proveedores
 * - 1 Servicio "Auditoría SST"
 * - 2 Consultores
 * - 2 Contratos (proveedor A ↔ clientes 1 y 2, servicio Auditoría SST, activos)
 * - 1 Usuario admin (si no existe)
 *
 * Uso: php spark db:seed AdminQuickSeed
 */
class AdminQuickSeed extends Seeder
{
    public function run()
    {
        echo "\n=== AdminQuickSeed: Iniciando carga de datos de prueba ===\n\n";

        // 1. Crear usuario admin si no existe
        $this->createAdminUser();

        // 2. Crear usuarios para consultores y proveedores
        $userConsultor1 = $this->createUser('consultor1@cycloid.com', 'Consultor123*', 'Ana García', 2);
        $userConsultor2 = $this->createUser('consultor2@cycloid.com', 'Consultor123*', 'Carlos López', 2);
        $userProveedor1 = $this->createUser('proveedor1@empresa.com', 'Proveedor123*', 'María Rodríguez', 3);

        // 3. Crear 3 clientes
        echo "→ Creando clientes...\n";
        $clientesData = [
            [
                'razon_social' => 'Empresa Demo ABC S.A.S',
                'nit' => '900123456-1',
                'email_contacto' => 'contacto@empresaabc.com',
                'telefono_contacto' => '3101234567',
                'direccion' => 'Calle 100 # 10-20, Bogotá',
                'estado' => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'razon_social' => 'Corporación XYZ LTDA',
                'nit' => '800987654-3',
                'email_contacto' => 'info@corpxyz.com',
                'telefono_contacto' => '3209876543',
                'direccion' => 'Carrera 50 # 80-30, Medellín',
                'estado' => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'razon_social' => 'Industrias DEF S.A',
                'nit' => '900555777-9',
                'email_contacto' => 'admin@industriasdef.com',
                'telefono_contacto' => '3155554321',
                'direccion' => 'Avenida 68 # 45-12, Cali',
                'estado' => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('clientes')->insertBatch($clientesData);
        $idCliente1 = $this->db->insertID();
        $idCliente2 = $idCliente1 + 1;
        $idCliente3 = $idCliente1 + 2;
        echo "  ✓ 3 clientes creados (IDs: {$idCliente1}, {$idCliente2}, {$idCliente3})\n";

        // 4. Crear 2 proveedores
        echo "→ Creando proveedores...\n";
        $proveedoresData = [
            [
                'razon_social' => 'Proveedor Alpha S.A.S',
                'nit' => '901234567-8',
                'email_contacto' => 'ventas@proveedoralpha.com',
                'telefono_contacto' => '3201112233',
                'id_users' => $userProveedor1,
                'observaciones' => 'Proveedor principal de servicios SST',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'razon_social' => 'Servicios Beta LTDA',
                'nit' => '800888999-4',
                'email_contacto' => 'contacto@serviciosbeta.com',
                'telefono_contacto' => '3154445566',
                'id_users' => null,
                'observaciones' => 'Proveedor secundario sin usuario asignado',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('proveedores')->insertBatch($proveedoresData);
        $idProveedor1 = $this->db->insertID();
        $idProveedor2 = $idProveedor1 + 1;
        echo "  ✓ 2 proveedores creados (IDs: {$idProveedor1}, {$idProveedor2})\n";

        // 5. Crear servicio "Auditoría SST"
        echo "→ Creando servicio...\n";
        $servicioData = [
            'nombre' => 'Auditoría SST',
            'activo' => 1,
        ];

        $this->db->table('servicios')->insert($servicioData);
        $idServicio = $this->db->insertID();
        echo "  ✓ Servicio 'Auditoría SST' creado (ID: {$idServicio})\n";

        // 6. Crear 2 consultores
        echo "→ Creando consultores...\n";
        $consultoresData = [
            [
                'id_users' => $userConsultor1,
                'nombre_completo' => 'Ana García Pérez',
                'tipo_documento' => 'CC',
                'numero_documento' => '52123456',
                'licencia_sst' => 'SST-2024-001',
                'firma_path' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_users' => $userConsultor2,
                'nombre_completo' => 'Carlos López Martínez',
                'tipo_documento' => 'CC',
                'numero_documento' => '79987654',
                'licencia_sst' => 'SST-2024-002',
                'firma_path' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('consultores')->insertBatch($consultoresData);
        $idConsultor1 = $this->db->insertID();
        $idConsultor2 = $idConsultor1 + 1;
        echo "  ✓ 2 consultores creados (IDs: {$idConsultor1}, {$idConsultor2})\n";

        // 7. Crear 2 contratos (Proveedor A ↔ Clientes 1 y 2, Servicio Auditoría SST)
        echo "→ Creando contratos...\n";
        $contratosData = [
            [
                'id_proveedor' => $idProveedor1,
                'id_cliente' => $idCliente1,
                'id_servicio' => $idServicio,
                'fecha_inicio' => date('Y-01-01'),
                'fecha_fin' => date('Y-12-31'),
                'numero_personas' => 50,
                'observaciones' => 'Contrato anual de auditoría SST - Cliente 1',
                'soporte_contrato_path' => null,
                'email_admin_cliente' => 'admin.cliente1@empresaabc.com',
                'estado' => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id_proveedor' => $idProveedor1,
                'id_cliente' => $idCliente2,
                'id_servicio' => $idServicio,
                'fecha_inicio' => date('Y-02-01'),
                'fecha_fin' => date('Y-12-31'),
                'numero_personas' => 75,
                'observaciones' => 'Contrato anual de auditoría SST - Cliente 2',
                'soporte_contrato_path' => null,
                'email_admin_cliente' => 'admin.cliente2@corpxyz.com',
                'estado' => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('contratos_proveedor_cliente')->insertBatch($contratosData);
        $idContrato1 = $this->db->insertID();
        $idContrato2 = $idContrato1 + 1;
        echo "  ✓ 2 contratos creados (IDs: {$idContrato1}, {$idContrato2})\n";

        echo "\n=== AdminQuickSeed: Completado exitosamente ===\n";
        echo "\nCredenciales de acceso:\n";
        echo "  Admin:       superadmin@cycloidtalent.com / Admin123*\n";
        echo "  Consultor 1: consultor1@cycloid.com / Consultor123*\n";
        echo "  Consultor 2: consultor2@cycloid.com / Consultor123*\n";
        echo "  Proveedor 1: proveedor1@empresa.com / Proveedor123*\n";
        echo "\nDatos creados:\n";
        echo "  • 3 Clientes\n";
        echo "  • 2 Proveedores\n";
        echo "  • 1 Servicio (Auditoría SST)\n";
        echo "  • 2 Consultores\n";
        echo "  • 2 Contratos activos\n";
        echo "\n";
    }

    /**
     * Crea el usuario admin si no existe
     */
    private function createAdminUser(): void
    {
        echo "→ Verificando usuario admin...\n";

        $existingAdmin = $this->db->table('users')
            ->where('email', 'superadmin@cycloidtalent.com')
            ->get()
            ->getRowArray();

        if ($existingAdmin) {
            echo "  ℹ Usuario admin ya existe\n";
            return;
        }

        $hash = password_hash('Admin123*', PASSWORD_DEFAULT);
        $this->db->table('users')->insert([
            'email' => 'superadmin@cycloidtalent.com',
            'password_hash' => $hash,
            'nombre' => 'Super Admin',
            'id_roles' => 1,
            'estado' => 'activo',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        echo "  ✓ Usuario admin creado\n";
    }

    /**
     * Crea un usuario y retorna su ID
     */
    private function createUser(string $email, string $password, string $nombre, int $rol): int
    {
        // Verificar si el usuario ya existe
        $existing = $this->db->table('users')
            ->where('email', $email)
            ->get()
            ->getRowArray();

        if ($existing) {
            return $existing['id_users'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->table('users')->insert([
            'email' => $email,
            'password_hash' => $hash,
            'nombre' => $nombre,
            'id_roles' => $rol,
            'estado' => 'activo',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insertID();
    }
}
