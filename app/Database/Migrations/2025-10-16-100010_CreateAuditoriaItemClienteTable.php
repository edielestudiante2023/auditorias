<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoriaItemClienteTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_auditoria_item_cliente' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_auditoria_item' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_cliente' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'comentario_proveedor_cliente' => [
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

        $this->forge->addKey('id_auditoria_item_cliente', true);
        $this->forge->addForeignKey('id_auditoria_item', 'auditoria_items', 'id_auditoria_item', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_cliente', 'clientes', 'id_cliente', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auditoria_item_cliente');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria_item_cliente');
    }
}
