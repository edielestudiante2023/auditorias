<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoriaLogTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_auditoria_log' => [
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
            'id_usuario' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'accion' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'reapertura, cierre, anulacion, etc',
            ],
            'estado_anterior' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'estado_nuevo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'motivo' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_auditoria_log', true);
        $this->forge->addForeignKey('id_auditoria', 'auditorias', 'id_auditoria', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_usuario', 'users', 'id_users', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auditoria_log');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria_log');
    }
}
