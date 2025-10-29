<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConsultores extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_consultor' => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],

            // Relación con users (login)
            'id_users'     => ['type'=>'INT','constraint'=>11,'unsigned'=>true],

            // NUEVO: datos civiles del consultor
            'nombre_completo'  => ['type'=>'VARCHAR','constraint'=>150],   // redundante a users.nombre, pero aquí queda congelado “para auditoría”
            'tipo_documento'   => ['type'=>"ENUM('CC','CE','PAS','OTRO')",'default'=>'CC'],
            'numero_documento' => ['type'=>'VARCHAR','constraint'=>30],

            'licencia_sst' => ['type'=>'VARCHAR','constraint'=>100,'null'=>true],

            // NUEVO: firma (imagen) -> guardamos RUTA/URL, no BLOB
            'firma_path'   => ['type'=>'VARCHAR','constraint'=>500,'null'=>true],

            'created_at'   => ['type'=>'DATETIME','null'=>true],
            'updated_at'   => ['type'=>'DATETIME','null'=>true],
        ]);

        $this->forge->addKey('id_consultor', true);
        $this->forge->addForeignKey('id_users','users','id_users','CASCADE','RESTRICT');
        $this->forge->createTable('consultores');
    }

    public function down()
    {
        $this->forge->dropTable('consultores');
    }
}
