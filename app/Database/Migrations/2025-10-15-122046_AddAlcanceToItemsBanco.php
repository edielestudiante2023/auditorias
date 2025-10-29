<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAlcanceToItemsBanco extends Migration
{
    public function up()
    {
        $this->forge->addColumn('items_banco', [
            'alcance' => [
                'type'       => 'ENUM',
                'constraint' => ['global', 'por_cliente', 'mixto'],
                'default'    => 'global',
                'null'       => false,
                'after'      => 'titulo',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('items_banco', 'alcance');
    }
}
