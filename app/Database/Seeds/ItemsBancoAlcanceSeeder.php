<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ItemsBancoAlcanceSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Ítems con alcance 'por_cliente'
        $itemsPorCliente = [
            'Pago seguridad social',
            'Entrega de dotación',
        ];

        // Ítems con alcance 'global'
        $itemsGlobal = [
            'Política SG-SST',
            'Programa de capacitación',
            'Investigación de incidentes',
            'Brigada de emergencia',
            'Reporte de peligros',
            'Plan de trabajo SST',
        ];

        // Actualizar ítems por_cliente
        foreach ($itemsPorCliente as $titulo) {
            $db->table('items_banco')
                ->where('titulo', $titulo)
                ->update(['alcance' => 'por_cliente']);
        }

        // Actualizar ítems global
        foreach ($itemsGlobal as $titulo) {
            $db->table('items_banco')
                ->where('titulo', $titulo)
                ->update(['alcance' => 'global']);
        }

        // Asegurar que los demás también sean 'global' (por si acaso)
        $db->table('items_banco')
            ->whereNotIn('titulo', array_merge($itemsPorCliente, $itemsGlobal))
            ->update(['alcance' => 'global']);

        echo "✓ Alcance actualizado para "
            . count($itemsPorCliente) . " ítems por_cliente\n";
        echo "✓ Alcance actualizado para "
            . count($itemsGlobal) . " ítems global\n";
        echo "✓ Resto de ítems marcados como global por defecto\n";
    }
}
