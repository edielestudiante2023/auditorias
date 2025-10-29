-- Agregar columna porcentaje_cumplimiento a la tabla auditoria_clientes

ALTER TABLE `auditoria_clientes`
ADD COLUMN `porcentaje_cumplimiento` DECIMAL(5,2) NULL DEFAULT 0.00
COMMENT 'Porcentaje de cumplimiento del cliente en esta auditoría'
AFTER `id_cliente`;
