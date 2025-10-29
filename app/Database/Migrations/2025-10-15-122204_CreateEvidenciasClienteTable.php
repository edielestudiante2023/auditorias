<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEvidenciasClienteTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_evidencia_cliente' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_auditoria_item_cliente' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nombre_archivo_original' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'ruta_archivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'tipo_mime' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'tamanio_bytes' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'hash_archivo' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_evidencia_cliente', true);
        $this->forge->addKey('id_auditoria_item_cliente');

        $this->forge->addForeignKey(
            'id_auditoria_item_cliente',
            'auditoria_item_cliente',
            'id_auditoria_item_cliente',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('evidencias_cliente', true);
    }

    public function down()
    {
        $this->forge->dropTable('evidencias_cliente', true);
    }
}
