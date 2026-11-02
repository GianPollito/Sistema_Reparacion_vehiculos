CREATE DATABASE IF NOT EXISTS `motorexpress_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `motorexpress_db`;

CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `rol` ENUM('Administrador', 'Recepcionista', 'Mecanico') DEFAULT 'Administrador',
    `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clientes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `dni_ruc` VARCHAR(20) NOT NULL,
    `nombres` VARCHAR(100) NOT NULL,
    `apellidos` VARCHAR(100) NOT NULL,
    `telefono` VARCHAR(20),
    `email` VARCHAR(100),
    `direccion` VARCHAR(200),
    `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `vehiculos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cliente_id` INT NOT NULL,
    `placa` VARCHAR(20) NOT NULL,
    `marca` VARCHAR(50) NOT NULL,
    `modelo` VARCHAR(50) NOT NULL,
    `anio` INT NOT NULL,
    `color` VARCHAR(30),
    `kilometraje` INT DEFAULT 0,
    `observaciones` TEXT,
    `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_vehiculos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mecanicos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `dni` VARCHAR(20) NOT NULL,
    `nombre_completo` VARCHAR(120) NOT NULL,
    `especialidad` VARCHAR(100) NOT NULL,
    `telefono` VARCHAR(20),
    `email` VARCHAR(100),
    `estado` ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `citas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(20) NOT NULL UNIQUE,
    `cliente_id` INT NOT NULL,
    `vehiculo_id` INT NOT NULL,
    `fecha_cita` DATE NOT NULL,
    `hora_cita` TIME NOT NULL,
    `motivo` TEXT NOT NULL,
    `estado` ENUM('Pendiente', 'Confirmada', 'En Atención', 'Completada', 'Cancelada') DEFAULT 'Pendiente',
    `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_citas_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_citas_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `servicios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(20) NOT NULL UNIQUE,
    `nombre` VARCHAR(150) NOT NULL,
    `categoria` VARCHAR(50) NOT NULL,
    `descripcion` TEXT,
    `precio` DECIMAL(10,2) NOT NULL,
    `tiempo_estimado` VARCHAR(50),
    `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `repuestos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(30) NOT NULL UNIQUE,
    `nombre` VARCHAR(150) NOT NULL,
    `categoria` VARCHAR(50) NOT NULL,
    `marca` VARCHAR(50),
    `precio_unitario` DECIMAL(10,2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `stock_minimo` INT NOT NULL DEFAULT 5,
    `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ordenes_trabajo` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `numero_orden` VARCHAR(30) NOT NULL UNIQUE,
    `vehiculo_id` INT NOT NULL,
    `cliente_id` INT NOT NULL,
    `mecanico_id` INT NULL,
    `fecha_ingreso` DATETIME NOT NULL,
    `fecha_estimada_entrega` DATETIME,
    `kilometraje_ingreso` INT,
    `nivel_combustible` VARCHAR(20),
    `estado` ENUM('Pendiente', 'En Diagnóstico', 'En Proceso', 'Esperando Repuestos', 'Finalizada', 'Entregada', 'Cancelada') DEFAULT 'Pendiente',
    `problema_reportado` TEXT NOT NULL,
    `notas_tecnicas` TEXT,
    `total_servicios` DECIMAL(10,2) DEFAULT 0.00,
    `total_repuestos` DECIMAL(10,2) DEFAULT 0.00,
    `total_general` DECIMAL(10,2) DEFAULT 0.00,
    `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ot_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ot_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ot_mecanico` FOREIGN KEY (`mecanico_id`) REFERENCES `mecanicos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `repuestos_utilizados` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `orden_id` INT NOT NULL,
    `repuesto_id` INT NOT NULL,
    `cantidad` INT NOT NULL,
    `precio_unitario` DECIMAL(10,2) NOT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL,
    `fecha_asignacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ru_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_trabajo` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ru_repuesto` FOREIGN KEY (`repuesto_id`) REFERENCES `repuestos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `diagnosticos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `orden_id` INT NULL,
    `vehiculo_id` INT NOT NULL,
    `mecanico_id` INT NOT NULL,
    `fecha_diagnostico` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `descripcion_falla` TEXT NOT NULL,
    `diagnostico_tecnico` TEXT NOT NULL,
    `gravedad` ENUM('Baja', 'Media', 'Alta', 'Crítica') DEFAULT 'Media',
    `recomendaciones` TEXT,
    `creado_en` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_diag_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_trabajo` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_diag_vehiculo` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_diag_mecanico` FOREIGN KEY (`mecanico_id`) REFERENCES `mecanicos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `pagos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `numero_comprobante` VARCHAR(30) NOT NULL UNIQUE,
    `tipo_comprobante` ENUM('Boleta', 'Factura', 'Recibo de Caja') DEFAULT 'Boleta',
    `orden_id` INT NOT NULL,
    `cliente_id` INT NOT NULL,
    `monto` DECIMAL(10,2) NOT NULL,
    `metodo_pago` ENUM('Efectivo', 'Tarjeta de Crédito', 'Tarjeta de Débito', 'Yape', 'Plin') NOT NULL,
    `numero_operacion` VARCHAR(50),
    `fecha_pago` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `notas` TEXT,
    `estado` ENUM('Completado', 'Pendiente', 'Anulado') DEFAULT 'Completado',
    CONSTRAINT `fk_pagos_orden` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_trabajo` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pagos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `usuarios` (`nombre`, `email`, `password`, `rol`) VALUES
('Administrador Principal', 'admin@motorexpress.pe', '$2y$10$w8uQeE9lI6K3U9B17gq/eej2fH9M6E4N3Yq0g8PzB.4c.JtUoHqY2', 'Administrador'),
('Carlos Mendoza (Mecánico)', 'carlos@motorexpress.pe', '$2y$10$w8uQeE9lI6K3U9B17gq/eej2fH9M6E4N3Yq0g8PzB.4c.JtUoHqY2', 'Mecanico'),
('Valeria Ríos (Recepcionista)', 'valeria@motorexpress.pe', '$2y$10$w8uQeE9lI6K3U9B17gq/eej2fH9M6E4N3Yq0g8PzB.4c.JtUoHqY2', 'Recepcionista');

INSERT INTO `clientes` (`dni_ruc`, `nombres`, `apellidos`, `telefono`, `email`, `direccion`) VALUES
('47895214', 'Juan Carlos', 'Pérez Quispe', '987112233', 'juan.perez@gmail.com', 'Av. Los Próceres 450, Santiago de Surco'),
('10458796', 'María Elena', 'Gómez Salcedo', '974556677', 'maria.gomez@outlook.com', 'Calle Las Palmeras 124, San Borja'),
('71236548', 'Roberto', 'López Morales', '998443322', 'roberto.lopez@yahoo.com', 'Jr. Junín 890, Miraflores'),
('45698712', 'Ana Lucía', 'Torres Benítez', '955664411', 'ana.torres@gmail.com', 'Av. La Marina 2340, San Miguel'),
('20547896', 'Transportes Veloz SAC', 'RUC 20547896321', '944112288', 'logistica@transveloz.pe', 'Av. Nicolás Arriola 1500, La Victoria');

INSERT INTO `vehiculos` (`cliente_id`, `placa`, `marca`, `modelo`, `anio`, `color`, `kilometraje`, `observaciones`) VALUES
(1, 'ABC-123', 'Toyota', 'Corolla', 2021, 'Plata Metálico', 45000, 'Sedán de uso personal, revisiones al día'),
(2, 'BDX-845', 'Hyundai', 'Tucson', 2022, 'Gris Grafito', 32000, 'SUV familiar, presenta ruidos en frenos'),
(3, 'F5T-912', 'Nissan', 'Sentra', 2019, 'Negro', 68000, 'Ligera pérdida de potencia y consumo de aceite'),
(4, 'C2P-741', 'Kia', 'Rio', 2020, 'Rojo Pasión', 51000, 'Mantenimiento preventivo regular'),
(5, 'W4E-369', 'Toyota', 'Hilux', 2023, 'Blanco', 28000, 'Camioneta de carga ligera, chequeo de suspensión');

INSERT INTO `mecanicos` (`dni`, `nombre_completo`, `especialidad`, `telefono`, `email`, `estado`) VALUES
('41258963', 'Carlos Mendoza Vargas', 'Diagnóstico y Electrónica Automotriz', '981234567', 'carlos.m@motorexpress.pe', 'Activo'),
('43987412', 'Miguel Ángel Castro', 'Motores y Cajas de Cambio', '972345678', 'miguel.c@motorexpress.pe', 'Activo'),
('46521478', 'José Luis Flores', 'Frenos, Dirección y Suspensión', '963456789', 'jose.f@motorexpress.pe', 'Activo'),
('48965213', 'Eduardo Ramos Silva', 'Mantenimiento Preventivo y Afinamiento', '954567890', 'eduardo.r@motorexpress.pe', 'Activo');

INSERT INTO `servicios` (`codigo`, `nombre`, `categoria`, `descripcion`, `precio`, `tiempo_estimado`) VALUES
('SRV-001', 'Afinamiento Electrónico Completo', 'Motor', 'Limpieza y calibración de sensores, limpieza de cuerpo de aceleración, escaneo general OBD-II', 180.00, '2 horas'),
('SRV-002', 'Cambio de Aceite Sintético y Filtro', 'Mantenimiento', 'Drenado de aceite usado, colocación de aceite 100% sintético y cambio de filtro de aceite', 120.00, '45 min'),
('SRV-003', 'Mantenimiento del Sistema de Frenos', 'Frenos', 'Limpieza y purga del circuito hidráulico, rectificación o cambio de pastillas y discos', 150.00, '1.5 horas'),
('SRV-004', 'Limpieza de Inyectores por Ultrasonido', 'Motor', 'Desmontaje de rampa, prueba en banco, limpieza ultrasónica y reemplazo de microfiltros', 160.00, '2 horas'),
('SRV-005', 'Alineamiento y Balanceo Computarizado', 'Suspensión', 'Alineación láser 3D en 4 ruedas y balanceo dinámico de llantas', 90.00, '1 hora'),
('SRV-006', 'Diagnóstico Computarizado Completo', 'Eléctrico', 'Escaneo multimarca con osciloscopio, lectura de códigos DTC y diagnóstico en tiempo real', 80.00, '45 min'),
('SRV-007', 'Recarga y Mantenimiento de Aire Acondicionado', 'Aire Acondicionado', 'Vacío de circuito, detección de fugas, reposición de aceite y gas ecológico R134a', 140.00, '1 hora');

INSERT INTO `repuestos` (`codigo`, `nombre`, `categoria`, `marca`, `precio_unitario`, `stock`, `stock_minimo`) VALUES
('REP-001', 'Filtro de Aceite Premium', 'Filtros', 'Mann Filter', 35.00, 24, 6),
('REP-002', 'Pastillas de Freno Delanteras Cerámicas', 'Frenos', 'Brembo', 185.00, 12, 4),
('REP-003', 'Aceite Sintético 5W-30 (Galón)', 'Lubricantes', 'Mobil 1', 160.00, 18, 5),
('REP-004', 'Juego de Bujías de Iridio (x4)', 'Encendido', 'NGK Laser Iridium', 140.00, 15, 4),
('REP-005', 'Filtro de Aire Motor', 'Filtros', 'Bosch', 45.00, 20, 5),
('REP-006', 'Líquido de Frenos DOT 4 (1L)', 'Frenos', 'Motul', 40.00, 25, 6),
('REP-007', 'Amortiguador Delantero Reforzado', 'Suspensión', 'KYB Excel-G', 260.00, 8, 2),
('REP-008', 'Batería 13 Placas 65Ah', 'Eléctrico', 'Etna Platinum', 320.00, 7, 3);
