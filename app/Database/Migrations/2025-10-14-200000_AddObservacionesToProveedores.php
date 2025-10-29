<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddObservacionesToProveedores extends Migration
{
    public function up()
    {
        $this->forge->addColumn('proveedores', [
            'observaciones' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'id_users',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('proveedores', 'observaciones');
    }
}
