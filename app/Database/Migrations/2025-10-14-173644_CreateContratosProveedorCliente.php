<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContratosProveedorCliente extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_contrato'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],

            // Relaciones principales
            'id_proveedor'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_cliente'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_servicio'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],

            // Información de contexto útil para auditorías
            'numero_personas' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'observaciones'   => ['type' => 'TEXT', 'null' => true],

            // NUEVO: campo opcional para cargar el contrato firmado (PDF, imagen, etc.)
            'soporte_contrato_path' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],

            // NUEVO: correo de contacto del administrador del cliente (para notificaciones)
            'email_admin_cliente'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],

            // Estado básico
            'estado'   => ['type' => "ENUM('activo','inactivo')", 'default' => 'activo'],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id_contrato', true);
        $this->forge->addForeignKey('id_proveedor', 'proveedores', 'id_proveedor', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_cliente', 'clientes', 'id_cliente', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('id_servicio', 'servicios', 'id_servicio', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('contratos_proveedor_cliente');
    }

    public function down()
    {
        $this->forge->dropTable('contratos_proveedor_cliente');
    }
}
