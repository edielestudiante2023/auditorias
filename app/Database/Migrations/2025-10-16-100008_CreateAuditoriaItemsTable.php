<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoriaItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_auditoria_item' => [
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
            'id_item_banco' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'comentario_proveedor' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'comentario_consultor' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'calificacion' => [
                'type' => 'ENUM',
                'constraint' => ['cumple', 'parcial', 'no_cumple', 'no_aplica'],
                'null' => true,
            ],
            'completo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0=incompleto, 1=completo',
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

        $this->forge->addKey('id_auditoria_item', true);
        $this->forge->addForeignKey('id_auditoria', 'auditorias', 'id_auditoria', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_item_banco', 'items_banco', 'id_item_banco', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auditoria_items');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria_items');
    }
}
