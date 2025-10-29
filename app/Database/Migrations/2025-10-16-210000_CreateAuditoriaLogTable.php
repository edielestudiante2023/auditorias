<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migración: Tabla auditoria_log
 *
 * Registra todas las acciones realizadas en una auditoría para trazabilidad completa:
 * - Guardado de comentarios (global y por cliente)
 * - Subida y eliminación de evidencias
 * - Cambios de estado (finalización, cierre)
 * - Asignación de clientes
 *
 * Autor: Sistema de Auditorías
 * Fecha: 2025-10-16
 */
class CreateAuditoriaLogTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_log' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_auditoria' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'FK a auditorias',
            ],
            'id_users' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Usuario que realizó la acción (puede ser NULL para acciones del sistema)',
            ],
            'accion' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'comment'    => 'Tipo de acción: comentario_global, comentario_cliente, evidencia_subida, evidencia_eliminada, auditoria_cerrada, etc.',
            ],
            'detalle' => [
                'type'    => 'TEXT',
                'null'    => true,
                'comment' => 'Detalles adicionales en formato JSON o texto',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id_log', true);
        $this->forge->addKey('id_auditoria');
        $this->forge->addKey('id_users');
        $this->forge->addKey('created_at');

        // Foreign keys
        $this->forge->addForeignKey('id_auditoria', 'auditorias', 'id_auditoria', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_users', 'users', 'id_users', 'SET NULL', 'CASCADE');

        $this->forge->createTable('auditoria_log');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria_log');
    }
}
