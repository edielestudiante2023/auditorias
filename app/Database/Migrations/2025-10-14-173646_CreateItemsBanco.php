<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemsBanco extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_item'           => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'codigo_item'       => ['type'=>'VARCHAR','constraint'=>10],
            'titulo'            => ['type'=>'VARCHAR','constraint'=>255],
            'descripcion'       => ['type'=>'TEXT','null'=>true],
            'aplica_alto_riesgo'=> ['type'=>'TINYINT','constraint'=>1,'default'=>0],
            'orden'             => ['type'=>'INT','constraint'=>11,'default'=>0],
            'activo'            => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
        ]);
        $this->forge->addKey('id_item', true);
        $this->forge->createTable('items_banco');
    }

    public function down()
    {
        $this->forge->dropTable('items_banco');
    }
}
