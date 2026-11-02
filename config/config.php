<?php
$sessionDir = __DIR__ . '/../sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0777, true);
}
@ini_set('session.save_path', $sessionDir);
@session_save_path($sessionDir);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Lima');

define('APP_NAME', 'Motor Express');
define('APP_TAGLINE', 'Taller Automotriz Especializado');
define('APP_PHONE', '+51 987 654 321');
define('APP_EMAIL', 'contacto@motorexpress.pe');
define('APP_ADDRESS', 'Av. Industrial 1240, Lima - Perú');
define('APP_HOURS', 'Lun - Sáb: 8:00 AM - 6:00 PM');
define('CURRENCY_SYMBOL', 'S/.');
define('DB_TYPE', 'sqlite'); 
define('DB_SQLITE_PATH', __DIR__ . '/../database/motorexpress.sqlite');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'motorexpress_db');
define('DB_USER', 'root');
define('DB_PASS', '');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8095';
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

$parts = explode('/', trim($scriptName, '/'));
$baseFolder = '';
if (!empty($parts[0]) && $parts[0] !== 'modules') {
    $baseFolder = '/' . $parts[0];
}

if ($baseFolder === '' || $baseFolder === '/') {
    define('BASE_URL', rtrim($protocol . $host, '/'));
} else {
    define('BASE_URL', rtrim($protocol . $host . $baseFolder, '/'));
}

$METODOS_PAGO = [
    'Efectivo' => 'Efectivo',
    'Tarjeta de Crédito' => 'Tarjeta de Crédito',
    'Tarjeta de Débito' => 'Tarjeta de Débito',
    'Yape' => 'Yape',
    'Plin' => 'Plin'
];

$ESTADOS_ORDEN = [
    'Pendiente' => ['label' => 'Pendiente', 'color' => 'warning', 'icon' => 'clock'],
    'En Diagnóstico' => ['label' => 'En Diagnóstico', 'color' => 'info', 'icon' => 'stethoscope'],
    'En Proceso' => ['label' => 'En Proceso', 'color' => 'primary', 'icon' => 'cogs'],
    'Esperando Repuestos' => ['label' => 'Esperando Repuestos', 'color' => 'secondary', 'icon' => 'box'],
    'Finalizada' => ['label' => 'Finalizada', 'color' => 'success', 'icon' => 'check-circle'],
    'Entregada' => ['label' => 'Entregada', 'color' => 'dark', 'icon' => 'flag-checkered'],
    'Cancelada' => ['label' => 'Cancelada', 'color' => 'danger', 'icon' => 'times-circle']
];

$ESTADOS_CITA = [
    'Pendiente' => 'badge-warning',
    'Confirmada' => 'badge-primary',
    'En Atención' => 'badge-info',
    'Completada' => 'badge-success',
    'Cancelada' => 'badge-danger'
];
