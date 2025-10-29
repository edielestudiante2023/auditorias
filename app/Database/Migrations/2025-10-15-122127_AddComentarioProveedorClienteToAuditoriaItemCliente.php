<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddComentarioProveedorClienteToAuditoriaItemCliente extends Migration
{
    public function up()
    {
        $this->forge->addColumn('auditoria_item_cliente', [
            'comentario_proveedor_cliente' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'calificacion_ajustada',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('auditoria_item_cliente', 'comentario_proveedor_cliente');
    }
}
