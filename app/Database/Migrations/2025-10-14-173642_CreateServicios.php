<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServicios extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_servicio' => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'nombre'      => ['type'=>'VARCHAR','constraint'=>80,'unique'=>true], // Aseo, Vigilancia, etc.
            'activo'      => ['type'=>'TINYINT','constraint'=>1,'default'=>1],
        ]);
        $this->forge->addKey('id_servicio', true);
        $this->forge->createTable('servicios');
    }

    public function down()
    {
        $this->forge->dropTable('servicios');
    }
}
