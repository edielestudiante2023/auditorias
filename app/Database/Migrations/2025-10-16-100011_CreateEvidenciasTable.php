<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEvidenciasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_evidencia' => [
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
            'path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'nombre_original' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'mime' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'size' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Tamaño en bytes',
            ],
            'hash' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
                'comment' => 'SHA256 del archivo',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_evidencia', true);
        $this->forge->addForeignKey('id_auditoria_item', 'auditoria_items', 'id_auditoria_item', 'CASCADE', 'CASCADE');
        $this->forge->createTable('evidencias');
    }

    public function down()
    {
        $this->forge->dropTable('evidencias');
    }
}
