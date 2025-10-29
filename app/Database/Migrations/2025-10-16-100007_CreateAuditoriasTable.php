<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoriasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_auditoria' => [
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
            'codigo_formato' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'version_formato' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'estado' => [
                'type' => 'ENUM',
                'constraint' => ['borrador', 'en_proveedor', 'en_revision', 'cerrada', 'anulada'],
                'default' => 'borrador',
            ],
            'porcentaje_cumplimiento' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'creado_por' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'fecha_programada' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'fecha_envio_proveedor' => [
                'type' => 'DATETIME',
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

        $this->forge->addKey('id_auditoria', true);
        $this->forge->addForeignKey('id_proveedor', 'proveedores', 'id_proveedor', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('creado_por', 'users', 'id_users', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auditorias');
    }

    public function down()
    {
        $this->forge->dropTable('auditorias');
    }
}
