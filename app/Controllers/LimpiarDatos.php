<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class LimpiarDatos extends Controller
{
    public function index()
    {
        $db = Database::connect();

        $output = "<pre>";
        $output .= "=== LIMPIEZA DE DATOS DE PRUEBA ===\n\n";

        // Lista de planetas (proveedores de prueba)
        $planetas = ['JUPITER', 'MARTE', 'SATURNO', 'VENUS', 'MERCURIO', 'NEPTUNO', 'URANO', 'PLUTON', 'TIERRA'];

        // Lista de animales (consultores de prueba)
        $animales = ['NUTRIA', 'DELFIN', 'BALLENA', 'TIBURON', 'AGUILA', 'LEON', 'TIGRE', 'OSO', 'LOBO', 'ZORRO', 'CONEJO', 'GATO', 'PERRO'];

        // Lista de gemas (usuarios de prueba)
        $gemas = ['AMATISTA', 'ESMERALDA', 'RUBI', 'ZAFIRO', 'DIAMANTE', 'TOPACIO', 'OPALO', 'PERLA', 'JADE', 'ONIX', 'CUARZO', 'TURQUESA'];

        // Deshabilitar foreign key checks temporalmente
        $db->query('SET FOREIGN_KEY_CHECKS = 0');

        // ============================================
        // 1. BUSCAR Y ELIMINAR PROVEEDORES DE PRUEBA
        // ============================================
        $output .= "1. BUSCANDO PROVEEDORES DE PRUEBA (Planetas)...\n";

        $proveedoresConditions = [];
        foreach ($planetas as $planeta) {
            $proveedoresConditions[] = "nombre LIKE '%{$planeta}%'";
        }
        $whereProveedores = implode(' OR ', $proveedoresConditions);

        $proveedoresPrueba = $db->query("SELECT id_proveedor, nombre, nit FROM proveedores WHERE {$whereProveedores}")->getResultArray();

        if (count($proveedoresPrueba) > 0) {
            $output .= "   Encontrados " . count($proveedoresPrueba) . " proveedores de prueba:\n";
            foreach ($proveedoresPrueba as $p) {
                $output .= "   - [{$p['id_proveedor']}] {$p['nombre']} (NIT: {$p['nit']})\n";
            }

            $idsProveedores = array_column($proveedoresPrueba, 'id_proveedor');
            $idsProveedoresStr = implode(',', $idsProveedores);

            // Obtener auditorías de estos proveedores
            $auditoriasProv = $db->query("SELECT id_auditoria FROM auditorias WHERE id_proveedor IN ({$idsProveedoresStr})")->getResultArray();
            $idsAuditoriasProv = array_column($auditoriasProv, 'id_auditoria');

            if (count($idsAuditoriasProv) > 0) {
                $idsAuditoriasProvStr = implode(',', $idsAuditoriasProv);

                $db->query("DELETE FROM evidencias_cliente WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
                $output .= "   - Eliminadas evidencias_cliente\n";

                $db->query("DELETE FROM auditoria_item_cliente WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
                $output .= "   - Eliminadas auditoria_item_cliente\n";

                $db->query("DELETE FROM auditoria_clientes WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
                $output .= "   - Eliminadas auditoria_clientes\n";

                $db->query("DELETE FROM evidencias WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
                $output .= "   - Eliminadas evidencias\n";

                $db->query("DELETE FROM auditoria_items WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
                $output .= "   - Eliminadas auditoria_items\n";

                $db->query("DELETE FROM auditoria_log WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
                $output .= "   - Eliminados auditoria_log\n";

                $db->query("DELETE FROM auditorias WHERE id_auditoria IN ({$idsAuditoriasProvStr})");
                $output .= "   - Eliminadas " . count($idsAuditoriasProv) . " auditorías\n";
            }

            $db->query("DELETE FROM usuarios_proveedores WHERE id_proveedor IN ({$idsProveedoresStr})");
            $output .= "   - Eliminados usuarios_proveedores\n";

            $db->query("DELETE FROM contratos_proveedor_cliente WHERE id_proveedor IN ({$idsProveedoresStr})");
            $output .= "   - Eliminados contratos\n";

            $db->query("DELETE FROM proveedores WHERE id_proveedor IN ({$idsProveedoresStr})");
            $output .= "   - Eliminados " . count($proveedoresPrueba) . " proveedores\n";
        } else {
            $output .= "   No se encontraron proveedores de prueba.\n";
        }

        $output .= "\n";

        // ============================================
        // 2. BUSCAR Y ELIMINAR CONSULTORES DE PRUEBA
        // ============================================
        $output .= "2. BUSCANDO CONSULTORES DE PRUEBA (Animales)...\n";

        $consultoresConditions = [];
        foreach ($animales as $animal) {
            $consultoresConditions[] = "nombre LIKE '%{$animal}%'";
        }
        $whereConsultores = implode(' OR ', $consultoresConditions);

        $consultoresPrueba = $db->query("SELECT id_consultor, nombre, email FROM consultores WHERE {$whereConsultores}")->getResultArray();

        if (count($consultoresPrueba) > 0) {
            $output .= "   Encontrados " . count($consultoresPrueba) . " consultores de prueba:\n";
            foreach ($consultoresPrueba as $c) {
                $output .= "   - [{$c['id_consultor']}] {$c['nombre']} ({$c['email']})\n";
            }

            $idsConsultores = array_column($consultoresPrueba, 'id_consultor');
            $idsConsultoresStr = implode(',', $idsConsultores);

            $auditoriasConsultor = $db->query("SELECT id_auditoria FROM auditorias WHERE id_consultor IN ({$idsConsultoresStr})")->getResultArray();
            $idsAuditoriasConsultor = array_column($auditoriasConsultor, 'id_auditoria');

            if (count($idsAuditoriasConsultor) > 0) {
                $idsAuditoriasConsultorStr = implode(',', $idsAuditoriasConsultor);

                $db->query("DELETE FROM evidencias_cliente WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
                $db->query("DELETE FROM auditoria_item_cliente WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
                $db->query("DELETE FROM auditoria_clientes WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
                $db->query("DELETE FROM evidencias WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
                $db->query("DELETE FROM auditoria_items WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
                $db->query("DELETE FROM auditoria_log WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
                $db->query("DELETE FROM auditorias WHERE id_auditoria IN ({$idsAuditoriasConsultorStr})");
                $output .= "   - Eliminadas " . count($idsAuditoriasConsultor) . " auditorías relacionadas\n";
            }

            $db->query("DELETE FROM personal_asignado WHERE id_consultor IN ({$idsConsultoresStr})");
            $output .= "   - Eliminado personal_asignado\n";

            $db->query("DELETE FROM consultores WHERE id_consultor IN ({$idsConsultoresStr})");
            $output .= "   - Eliminados " . count($consultoresPrueba) . " consultores\n";
        } else {
            $output .= "   No se encontraron consultores de prueba.\n";
        }

        $output .= "\n";

        // ============================================
        // 3. BUSCAR Y ELIMINAR USUARIOS DE PRUEBA
        // ============================================
        $output .= "3. BUSCANDO USUARIOS DE PRUEBA (Gemas)...\n";

        $usersConditions = [];
        foreach ($gemas as $gema) {
            $usersConditions[] = "nombre LIKE '%{$gema}%'";
            $usersConditions[] = "username LIKE '%{$gema}%'";
            $usersConditions[] = "email LIKE '%{$gema}%'";
        }
        $whereUsers = implode(' OR ', $usersConditions);

        $usersPrueba = $db->query("SELECT id, nombre, username, email, rol FROM users WHERE {$whereUsers}")->getResultArray();

        if (count($usersPrueba) > 0) {
            $output .= "   Encontrados " . count($usersPrueba) . " usuarios de prueba:\n";
            foreach ($usersPrueba as $u) {
                $output .= "   - [{$u['id']}] {$u['nombre']} ({$u['username']}) - {$u['rol']}\n";
            }

            $idsUsers = array_column($usersPrueba, 'id');
            $idsUsersStr = implode(',', $idsUsers);

            $db->query("DELETE FROM notificaciones WHERE id_usuario IN ({$idsUsersStr})");
            $output .= "   - Eliminadas notificaciones\n";

            $db->query("DELETE FROM users WHERE id IN ({$idsUsersStr})");
            $output .= "   - Eliminados " . count($usersPrueba) . " usuarios\n";
        } else {
            $output .= "   No se encontraron usuarios de prueba.\n";
        }

        // Rehabilitar foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        $output .= "\n=== LIMPIEZA COMPLETADA ===\n";
        $output .= "</pre>";

        return $output;
    }
}
