<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoriaClientes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_auditoria_cliente' => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'id_auditoria'         => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'id_cliente'           => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'id_contrato'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'id_servicio'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true],
            'created_at'           => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id_auditoria_cliente', true);
        $this->forge->addUniqueKey(['id_auditoria','id_cliente']);
        $this->forge->addForeignKey('id_auditoria','auditorias','id_auditoria','CASCADE','RESTRICT');
        $this->forge->addForeignKey('id_cliente','clientes','id_cliente','CASCADE','RESTRICT');
        $this->forge->addForeignKey('id_contrato','contratos_proveedor_cliente','id_contrato','SET NULL','SET NULL');
        $this->forge->addForeignKey('id_servicio','servicios','id_servicio','SET NULL','SET NULL');
        $this->forge->createTable('auditoria_clientes');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria_clientes');
    }
}
