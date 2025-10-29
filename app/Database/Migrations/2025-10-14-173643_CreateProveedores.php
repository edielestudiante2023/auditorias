<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProveedores extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_proveedor'    => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'auto_increment'=>true],
            'razon_social'    => ['type'=>'VARCHAR','constraint'=>200],
            'nit'             => ['type'=>'VARCHAR','constraint'=>50],
            'email_contacto'  => ['type'=>'VARCHAR','constraint'=>150,'null'=>true],
            'telefono_contacto'=>['type'=>'VARCHAR','constraint'=>50,'null'=>true],
            'id_users'        => ['type'=>'INT','constraint'=>11,'unsigned'=>true,'null'=>true], // usuario proveedor opcional
            'created_at'      => ['type'=>'DATETIME','null'=>true],
            'updated_at'      => ['type'=>'DATETIME','null'=>true],
        ]);
        $this->forge->addKey('id_proveedor', true);
        $this->forge->addForeignKey('id_users','users','id_users','SET NULL','SET NULL');
        $this->forge->createTable('proveedores');
    }

    public function down()
    {
        $this->forge->dropTable('proveedores');
    }
}
