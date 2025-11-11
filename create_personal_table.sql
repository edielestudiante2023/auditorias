CREATE TABLE personal_asignado (
    id_personal INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    id_proveedor INT(11) UNSIGNED NOT NULL,
    id_cliente INT(11) UNSIGNED NOT NULL,
    tipo_documento ENUM('CC', 'CE', 'PA', 'TI', 'NIT') DEFAULT 'CC',
    numero_documento VARCHAR(20) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    fecha_ingreso DATE NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id_personal),
    UNIQUE KEY unique_documento_proveedor_cliente (id_proveedor, id_cliente, numero_documento),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
