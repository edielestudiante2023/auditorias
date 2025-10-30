<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateContratosProveedorClienteTable extends Migration
{
    public function up()
    {
        // Agregar columnas faltantes
        $fields = [
            'id_usuario_responsable' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'id_consultor',
            ],
            'tipo_auditoria' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'id_usuario_responsable',
            ],
            'numero_personas' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'id_cliente',
            ],
            'soporte_contrato_path' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
                'after' => 'observaciones',
            ],
            'email_admin_cliente' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
                'after' => 'soporte_contrato_path',
            ],
        ];

        $this->forge->addColumn('contratos_proveedor_cliente', $fields);

        // Agregar foreign key para id_usuario_responsable
        $this->forge->addForeignKey(
            'id_usuario_responsable',
            'users',
            'id_users',
            'CASCADE',
            'RESTRICT',
            'contratos_proveedor_cliente'
        );

        // Modificar el ENUM de estado para que coincida con local
        $this->db->query("ALTER TABLE contratos_proveedor_cliente
            MODIFY COLUMN estado ENUM('activo','inactivo') DEFAULT 'activo' NOT NULL");

        // Eliminar columnas que NO existen en local (opcional - comentado por seguridad)
        // $this->forge->dropColumn('contratos_proveedor_cliente', ['id_users', 'numero_contrato', 'fecha_inicio', 'fecha_fin']);
    }

    public function down()
    {
        // Eliminar foreign key
        $this->forge->dropForeignKey('contratos_proveedor_cliente', 'contratos_proveedor_cliente_id_usuario_responsable_foreign');

        // Eliminar columnas agregadas
        $this->forge->dropColumn('contratos_proveedor_cliente', [
            'id_usuario_responsable',
            'tipo_auditoria',
            'numero_personas',
            'soporte_contrato_path',
            'email_admin_cliente',
        ]);
    }
}
