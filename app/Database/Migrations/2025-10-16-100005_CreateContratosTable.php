<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContratosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_contrato' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_proveedor' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_cliente' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_servicio' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'estado' => [
                'type' => 'ENUM',
                'constraint' => ['activo', 'suspendido', 'finalizado'],
                'default' => 'activo',
            ],
            'numero_contrato' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'fecha_inicio' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'fecha_fin' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_contrato', true);
        $this->forge->addForeignKey('id_proveedor', 'proveedores', 'id_proveedor', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_cliente', 'clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_servicio', 'servicios', 'id_servicio', 'CASCADE', 'CASCADE');
        $this->forge->createTable('contratos');
    }

    public function down()
    {
        $this->forge->dropTable('contratos');
    }
}
