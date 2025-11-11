<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePersonalAsignadoTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_personal' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_proveedor' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_cliente' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tipo_documento' => [
                'type'       => 'ENUM',
                'constraint' => ['CC', 'CE', 'PA', 'TI', 'NIT'],
                'default'    => 'CC',
            ],
            'numero_documento' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nombres' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'apellidos' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'cargo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'fecha_ingreso' => [
                'type' => 'DATE',
            ],
            'estado' => [
                'type'       => 'ENUM',
                'constraint' => ['activo', 'inactivo'],
                'default'    => 'activo',
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

        $this->forge->addKey('id_personal', true);
        $this->forge->addForeignKey('id_proveedor', 'proveedores', 'id_proveedor', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_cliente', 'clientes', 'id_cliente', 'CASCADE', 'CASCADE');

        // Índice único para evitar duplicados de documento por proveedor-cliente
        $this->forge->addKey(['id_proveedor', 'id_cliente', 'numero_documento'], false, true);

        $this->forge->createTable('personal_asignado');
    }

    public function down()
    {
        $this->forge->dropTable('personal_asignado');
    }
}
