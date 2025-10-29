<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificaciones extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_notificacion' => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'id_auditoria'    => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'tipo'            => ['type'=>'VARCHAR','constraint'=>50], // envio_proveedor, termino_proveedor, cerrada_consultor...
            'payload_json'    => ['type'=>'JSON','null'=>true],
            'fecha_envio'     => ['type'=>'DATETIME','null'=>true],
            'estado_envio'    => ['type'=>"ENUM('ok','error','pendiente')",'default'=>'pendiente'],
            'detalle_error'   => ['type'=>'TEXT','null'=>true],
        ]);
        $this->forge->addKey('id_notificacion', true);
        $this->forge->addForeignKey('id_auditoria','auditorias','id_auditoria','CASCADE','RESTRICT');
        $this->forge->createTable('notificaciones');
    }

    public function down()
    {
        $this->forge->dropTable('notificaciones');
    }
}
