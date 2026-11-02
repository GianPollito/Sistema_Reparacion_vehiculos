<?php

require_once __DIR__ . '/config.php';

function getDBConnection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    try {
        if (DB_TYPE === 'sqlite') {
            $dbDir = dirname(DB_SQLITE_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0777, true);
            }

            $firstTime = !file_exists(DB_SQLITE_PATH) || filesize(DB_SQLITE_PATH) === 0;
            $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA foreign_keys = ON;');

            if ($firstTime) {
                initializeDatabase($pdo);
            }
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return $pdo;
    } catch (PDOException $e) {
        if (DB_TYPE === 'mysql') {
            try {
                $dbDir = dirname(DB_SQLITE_PATH);
                if (!is_dir($dbDir)) {
                    mkdir($dbDir, 0777, true);
                }
                $firstTime = !file_exists(DB_SQLITE_PATH) || filesize(DB_SQLITE_PATH) === 0;
                $pdo = new PDO('sqlite:' . DB_SQLITE_PATH);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $pdo->exec('PRAGMA foreign_keys = ON;');
                if ($firstTime) {
                    initializeDatabase($pdo);
                }
                return $pdo;
            } catch (Exception $ex) {
                die('Error al conectar a la base de datos: ' . htmlspecialchars($ex->getMessage()));
            }
        }
        die('Error al conectar a la base de datos: ' . htmlspecialchars($e->getMessage()));
    }
}

function initializeDatabase(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        rol VARCHAR(50) DEFAULT 'Administrador',
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS clientes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        dni_ruc VARCHAR(20) NOT NULL,
        nombres VARCHAR(100) NOT NULL,
        apellidos VARCHAR(100) NOT NULL,
        telefono VARCHAR(20),
        email VARCHAR(100),
        direccion VARCHAR(200),
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS vehiculos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cliente_id INTEGER NOT NULL,
        placa VARCHAR(20) NOT NULL,
        marca VARCHAR(50) NOT NULL,
        modelo VARCHAR(50) NOT NULL,
        anio INTEGER NOT NULL,
        color VARCHAR(30),
        kilometraje INTEGER DEFAULT 0,
        observaciones TEXT,
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS mecanicos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        dni VARCHAR(20) NOT NULL,
        nombre_completo VARCHAR(120) NOT NULL,
        especialidad VARCHAR(100) NOT NULL,
        telefono VARCHAR(20),
        email VARCHAR(100),
        estado VARCHAR(20) DEFAULT 'Activo',
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS citas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo VARCHAR(20) UNIQUE NOT NULL,
        cliente_id INTEGER NOT NULL,
        vehiculo_id INTEGER NOT NULL,
        fecha_cita DATE NOT NULL,
        hora_cita TIME NOT NULL,
        motivo TEXT NOT NULL,
        estado VARCHAR(30) DEFAULT 'Pendiente',
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
        FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS servicios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo VARCHAR(20) UNIQUE NOT NULL,
        nombre VARCHAR(150) NOT NULL,
        categoria VARCHAR(50) NOT NULL,
        descripcion TEXT,
        precio DECIMAL(10,2) NOT NULL,
        tiempo_estimado VARCHAR(50),
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS repuestos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        codigo VARCHAR(30) UNIQUE NOT NULL,
        nombre VARCHAR(150) NOT NULL,
        categoria VARCHAR(50) NOT NULL,
        marca VARCHAR(50),
        precio_unitario DECIMAL(10,2) NOT NULL,
        stock INTEGER NOT NULL DEFAULT 0,
        stock_minimo INTEGER NOT NULL DEFAULT 5,
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS ordenes_trabajo (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        numero_orden VARCHAR(30) UNIQUE NOT NULL,
        vehiculo_id INTEGER NOT NULL,
        cliente_id INTEGER NOT NULL,
        mecanico_id INTEGER,
        fecha_ingreso DATETIME NOT NULL,
        fecha_estimada_entrega DATETIME,
        kilometraje_ingreso INTEGER,
        nivel_combustible VARCHAR(20),
        estado VARCHAR(30) DEFAULT 'Pendiente',
        problema_reportado TEXT NOT NULL,
        notas_tecnicas TEXT,
        total_servicios DECIMAL(10,2) DEFAULT 0.00,
        total_repuestos DECIMAL(10,2) DEFAULT 0.00,
        total_general DECIMAL(10,2) DEFAULT 0.00,
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
        FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
        FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id) ON DELETE SET NULL
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS orden_servicios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        orden_id INTEGER NOT NULL,
        servicio_id INTEGER NOT NULL,
        cantidad INTEGER DEFAULT 1,
        precio_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (orden_id) REFERENCES ordenes_trabajo(id) ON DELETE CASCADE,
        FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS repuestos_utilizados (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        orden_id INTEGER NOT NULL,
        repuesto_id INTEGER NOT NULL,
        cantidad INTEGER NOT NULL,
        precio_unitario DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (orden_id) REFERENCES ordenes_trabajo(id) ON DELETE CASCADE,
        FOREIGN KEY (repuesto_id) REFERENCES repuestos(id) ON DELETE CASCADE
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS diagnosticos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        orden_id INTEGER,
        vehiculo_id INTEGER NOT NULL,
        mecanico_id INTEGER NOT NULL,
        fecha_diagnostico DATETIME DEFAULT CURRENT_TIMESTAMP,
        descripcion_falla TEXT NOT NULL,
        diagnostico_tecnico TEXT NOT NULL,
        gravedad VARCHAR(20) DEFAULT 'Media',
        recomendaciones TEXT,
        creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (orden_id) REFERENCES ordenes_trabajo(id) ON DELETE SET NULL,
        FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
        FOREIGN KEY (mecanico_id) REFERENCES mecanicos(id) ON DELETE CASCADE
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pagos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        numero_comprobante VARCHAR(30) UNIQUE NOT NULL,
        tipo_comprobante VARCHAR(20) DEFAULT 'Boleta',
        orden_id INTEGER NOT NULL,
        cliente_id INTEGER NOT NULL,
        monto DECIMAL(10,2) NOT NULL,
        metodo_pago VARCHAR(50) NOT NULL,
        numero_operacion VARCHAR(50),
        fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
        notas TEXT,
        estado VARCHAR(20) DEFAULT 'Completado',
        FOREIGN KEY (orden_id) REFERENCES ordenes_trabajo(id) ON DELETE CASCADE,
        FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
    );");

    require_once __DIR__ . '/../database/seed_data.php';
    seedInitialData($pdo);
}
