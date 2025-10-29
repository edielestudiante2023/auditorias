<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEvidenciasClienteTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_evidencia_cliente' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_auditoria_item_cliente' => [
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

        $this->forge->addKey('id_evidencia_cliente', true);
        $this->forge->addForeignKey('id_auditoria_item_cliente', 'auditoria_item_cliente', 'id_auditoria_item_cliente', 'CASCADE', 'CASCADE');
        $this->forge->createTable('evidencias_cliente');
    }

    public function down()
    {
        $this->forge->dropTable('evidencias_cliente');
    }
}
