<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoriaItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_auditoria_item'  => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'id_auditoria'       => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'id_item'            => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'comentario_proveedor'=>['type'=>'TEXT','null'=>true],
            'comentario_consultor'=>['type'=>'TEXT','null'=>true],
            'calificacion_consultor'=>['type'=>"ENUM('sin_revision','cumple','parcial','no_cumple','no_aplica')",'default'=>'sin_revision'],
            'created_at'         => ['type'=>'DATETIME','null'=>true],
            'updated_at'         => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id_auditoria_item', true);
        $this->forge->addForeignKey('id_auditoria','auditorias','id_auditoria','CASCADE','RESTRICT');
        $this->forge->addForeignKey('id_item','items_banco','id_item','CASCADE','RESTRICT');
        $this->forge->createTable('auditoria_items');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria_items');
    }
}
