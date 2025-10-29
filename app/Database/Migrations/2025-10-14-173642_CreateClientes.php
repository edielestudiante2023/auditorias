<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClientes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_cliente'       => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'razon_social'     => ['type'=>'VARCHAR','constraint'=>200],
            'nit'              => ['type'=>'VARCHAR','constraint'=>50],
            'email_contacto'   => ['type'=>'VARCHAR','constraint'=>150,'null'=>true],
            'telefono_contacto'=> ['type'=>'VARCHAR','constraint'=>50,'null'=>true],
            'direccion'        => ['type'=>'VARCHAR','constraint'=>200,'null'=>true],
            'estado'           => ['type'=>"ENUM('activo','inactivo')",'default'=>'activo'],

            // NUEVO: logo del cliente (imagen) -> ruta/URL para el encabezado del PDF
            'logo_cliente_path'=> ['type'=>'VARCHAR','constraint'=>500,'null'=>true],

            'created_at'       => ['type'=>'DATETIME','null'=>true],
            'updated_at'       => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id_cliente', true);
        $this->forge->createTable('clientes');
    }

    public function down()
    {
        $this->forge->dropTable('clientes');
    }
}
