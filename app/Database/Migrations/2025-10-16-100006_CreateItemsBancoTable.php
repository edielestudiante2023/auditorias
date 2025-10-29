<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemsBancoTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_item_banco' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'codigo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'es_por_cliente' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0=global, 1=por_cliente',
            ],
            'orden' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'evidencia_requerida' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '0=opcional, 1=requerida',
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

        $this->forge->addKey('id_item_banco', true);
        $this->forge->createTable('items_banco');
    }

    public function down()
    {
        $this->forge->dropTable('items_banco');
    }
}
