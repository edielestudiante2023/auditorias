<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoriaClientesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_auditoria_cliente' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_auditoria' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_cliente' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_contrato' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'porcentaje_cumplimiento' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
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

        $this->forge->addKey('id_auditoria_cliente', true);
        $this->forge->addForeignKey('id_auditoria', 'auditorias', 'id_auditoria', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_cliente', 'clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_contrato', 'contratos', 'id_contrato', 'SET NULL', 'CASCADE');
        $this->forge->createTable('auditoria_clientes');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria_clientes');
    }
}
