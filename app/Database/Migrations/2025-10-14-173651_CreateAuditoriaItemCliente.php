<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoriaItemCliente extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_auditoria_item_cliente' => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'id_auditoria_item'         => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'id_cliente'                => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'calificacion_ajustada'     => ['type'=>"ENUM('sin_revision','cumple','parcial','no_cumple','no_aplica')",'default'=>'sin_revision'],
            'comentario_cliente'        => ['type'=>'TEXT','null'=>true],
            'created_at'                => ['type'=>'DATETIME','null'=>true],
            'updated_at'                => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id_auditoria_item_cliente', true);
        $this->forge->addUniqueKey(['id_auditoria_item','id_cliente']);
        $this->forge->addForeignKey('id_auditoria_item','auditoria_items','id_auditoria_item','CASCADE','RESTRICT');
        $this->forge->addForeignKey('id_cliente','clientes','id_cliente','CASCADE','RESTRICT');
        $this->forge->createTable('auditoria_item_cliente');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria_item_cliente');
    }
}
