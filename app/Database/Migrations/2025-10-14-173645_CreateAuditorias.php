<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditorias extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_auditoria'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'id_proveedor'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'id_consultor'          => ['type'=>'INT','constraint'=>11,'unsigned'=>true],
            'codigo_formato'        => ['type'=>'VARCHAR','constraint'=>50,'null'=>true],
            'version_formato'       => ['type'=>'VARCHAR','constraint'=>20,'null'=>true],
            'fecha_programada'      => ['type'=>'DATE','null'=>true],
            'fecha_envio_proveedor' => ['type'=>'DATETIME','null'=>true],
            'fecha_envio_consultor' => ['type'=>'DATETIME','null'=>true],
            'estado'                => ['type'=>"ENUM('borrador','en_proveedor','en_revision_consultor','cerrada','anulada')",'default'=>'borrador'],
            'porcentaje_cumplimiento'=>['type'=>'DECIMAL','constraint'=>'5,2','null'=>true],
            'created_at'            => ['type'=>'DATETIME','null'=>true],
            'updated_at'            => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id_auditoria', true);
        $this->forge->addForeignKey('id_proveedor','proveedores','id_proveedor','CASCADE','RESTRICT');
        $this->forge->addForeignKey('id_consultor','consultores','id_consultor','CASCADE','RESTRICT');
        $this->forge->createTable('auditorias');
    }

    public function down()
    {
        $this->forge->dropTable('auditorias');
    }
}
