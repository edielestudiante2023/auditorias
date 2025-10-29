<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder incremental para actualizar el campo 'alcance' en items_banco
 *
 * Este seeder asigna valores de alcance basándose en la naturaleza del ítem:
 * - 'por_cliente': Ítems que requieren evidencia/respuesta específica por cada cliente
 * - 'global': Ítems corporativos que aplican igual para todos los clientes
 * - 'mixto': Ítems que pueden tener componente global y específico por cliente
 *
 * Uso: php spark db:seed UpdateItemsBancoAlcanceSeeder
 */
class UpdateItemsBancoAlcanceSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        /**
         * Mapeo de alcances por tipo de ítem
         *
         * CRITERIOS DE CLASIFICACIÓN:
         *
         * POR_CLIENTE: Cuando el ítem requiere evidencia específica de cada cliente
         * - Pagos a seguridad social (cada cliente tiene sus propias planillas)
         * - Entrega de dotación (cada cliente tiene su propio personal)
         * - Capacitaciones específicas (cada cliente capacita a su personal)
         * - Exámenes médicos (cada cliente gestiona sus trabajadores)
         * - Elementos de protección personal (cada cliente suministra a su gente)
         * - Afiliaciones y contratos (cada cliente maneja sus propios afiliados)
         *
         * GLOBAL: Cuando el ítem es corporativo y aplica por igual a todos
         * - Políticas corporativas del proveedor
         * - Sistema de Gestión SST del proveedor
         * - Programas corporativos del proveedor
         * - Estructura organizacional del proveedor
         * - Certificaciones corporativas
         *
         * MIXTO: Cuando tiene componente global y específico
         * - Matriz de riesgos (global del proveedor + específica por cliente)
         * - Plan de emergencias (general + específico por sede/cliente)
         * - Comité SST (corporativo + representantes por cliente si aplica)
         */

        $alcancesPorTitulo = [
            // ============================================================
            // ÍTEMS POR_CLIENTE (20 ejemplos)
            // ============================================================

            // Pagos y afiliaciones (cada cliente gestiona su personal)
            'Pago seguridad social' => 'por_cliente',
            'Afiliación ARL' => 'por_cliente',
            'Afiliación EPS' => 'por_cliente',
            'Afiliación pensión' => 'por_cliente',
            'Afiliación caja compensación' => 'por_cliente',

            // Dotación y EPP (cada cliente equipa a su personal)
            'Entrega de dotación' => 'por_cliente',
            'Suministro EPP' => 'por_cliente',
            'Reposición elementos de protección' => 'por_cliente',
            'Registro entrega EPP' => 'por_cliente',

            // Capacitaciones específicas (cada cliente capacita su personal)
            'Capacitación en SST' => 'por_cliente',
            'Inducción y reinducción' => 'por_cliente',
            'Capacitación en alturas' => 'por_cliente',
            'Capacitación uso EPP' => 'por_cliente',
            'Capacitación en riesgos específicos' => 'por_cliente',

            // Salud ocupacional (cada cliente gestiona su personal)
            'Exámenes médicos ocupacionales' => 'por_cliente',
            'Examen de ingreso' => 'por_cliente',
            'Examen periódico' => 'por_cliente',
            'Examen de retiro' => 'por_cliente',
            'Seguimiento médico' => 'por_cliente',
            'Perfil sociodemográfico' => 'por_cliente',

            // ============================================================
            // ÍTEMS GLOBALES (ejemplos comunes)
            // ============================================================

            // Políticas corporativas del proveedor
            'Política de seguridad y salud en el trabajo' => 'global',
            'Política de alcohol y drogas' => 'global',
            'Política de no fumadores' => 'global',
            'Reglamento de higiene y seguridad' => 'global',

            // Sistema de gestión corporativo
            'Sistema de Gestión SST' => 'global',
            'Manual del SG-SST' => 'global',
            'Procedimientos corporativos SST' => 'global',
            'Objetivos del SG-SST' => 'global',
            'Indicadores del SG-SST' => 'global',

            // Estructura organizacional
            'Departamentalización SST' => 'global',
            'Organigrama corporativo' => 'global',
            'Asignación de responsabilidades SST' => 'global',
            'Profesional SST dedicado' => 'global',

            // Programas corporativos
            'Programa de vigilancia epidemiológica' => 'global',
            'Programa de medicina preventiva' => 'global',
            'Programa de higiene industrial' => 'global',
            'Programa de prevención consumo SPA' => 'global',

            // Certificaciones y licencias
            'Licencia SST vigente' => 'global',
            'Certificación ISO 45001' => 'global',
            'RUC vigente' => 'global',

            // ============================================================
            // ÍTEMS MIXTOS (combinan global y específico)
            // ============================================================

            // Estos requieren componente global del proveedor Y específico por cliente
            'Matriz de identificación de peligros' => 'mixto',
            'Matriz de requisitos legales' => 'mixto',
            'Plan de emergencias' => 'mixto',
            'Comité paritario SST' => 'mixto',
            'Brigada de emergencias' => 'mixto',
            'Programa de orden y aseo' => 'mixto',
        ];

        // Contador de actualizaciones
        $updated = 0;
        $notFound = [];

        // Actualizar cada ítem según el mapeo
        foreach ($alcancesPorTitulo as $titulo => $alcance) {
            $result = $db->table('items_banco')
                ->where('titulo', $titulo)
                ->update(['alcance' => $alcance]);

            if ($result) {
                $updated++;
                echo "✓ '{$titulo}' → alcance='{$alcance}'\n";
            } else {
                $notFound[] = $titulo;
            }
        }

        // Resumen de la ejecución
        echo "\n";
        echo "==================================================\n";
        echo "RESUMEN DE ACTUALIZACIÓN DE ALCANCES\n";
        echo "==================================================\n";
        echo "Total procesados: " . count($alcancesPorTitulo) . "\n";
        echo "Actualizados exitosamente: {$updated}\n";
        echo "No encontrados: " . count($notFound) . "\n";

        if (!empty($notFound)) {
            echo "\nÍtems no encontrados en la base de datos:\n";
            foreach ($notFound as $item) {
                echo "  - {$item}\n";
            }
            echo "\nNOTA: Estos ítems no existen en items_banco.\n";
            echo "Si necesitas agregarlos, actualiza el ItemsBancoSeeder principal.\n";
        }

        echo "\n";
        echo "==================================================\n";
        echo "ÍTEMS QUE QUEDARON COMO 'global' (sin modificar):\n";
        echo "==================================================\n";

        $itemsGlobalesSinModificar = $db->table('items_banco')
            ->select('codigo_item, titulo')
            ->where('alcance', 'global')
            ->whereNotIn('titulo', array_keys($alcancesPorTitulo))
            ->get()
            ->getResultArray();

        if (!empty($itemsGlobalesSinModificar)) {
            foreach ($itemsGlobalesSinModificar as $item) {
                echo "  - [{$item['codigo_item']}] {$item['titulo']}\n";
            }
            echo "\nEstos ítems mantuvieron el valor por defecto 'global'.\n";
            echo "Revisa si algunos deberían ser 'por_cliente' o 'mixto' y ajusta manualmente.\n";
        } else {
            echo "  (Todos los ítems fueron procesados)\n";
        }

        echo "\n";
        echo "==================================================\n";
        echo "DISTRIBUCIÓN FINAL DE ALCANCES\n";
        echo "==================================================\n";

        $distribucion = $db->query("
            SELECT alcance, COUNT(*) as total
            FROM items_banco
            GROUP BY alcance
            ORDER BY alcance
        ")->getResultArray();

        foreach ($distribucion as $row) {
            echo "  {$row['alcance']}: {$row['total']} ítem(s)\n";
        }

        echo "\n✅ Seeder ejecutado exitosamente.\n";
        echo "Puedes ejecutarlo nuevamente sin problemas (es idempotente).\n\n";
    }
}
