<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

checkAuth();
$pdo = getDBConnection();

$tipo = $_GET['tipo'] ?? 'general';
$filename = "motorexpress_reporte_{$tipo}_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

if ($tipo === 'ingresos') {
    fputcsv($output, ['Comprobante', 'Tipo', 'Orden', 'Cliente', 'DNI/RUC', 'Metodo de Pago', 'Operacion', 'Fecha', 'Monto']);
    $stmt = $pdo->query("SELECT p.numero_comprobante, p.tipo_comprobante, ot.numero_orden, 
        c.nombres || ' ' || c.apellidos as cliente, c.dni_ruc, p.metodo_pago, p.numero_operacion, p.fecha_pago, p.monto 
        FROM pagos p 
        JOIN clientes c ON p.cliente_id = c.id 
        JOIN ordenes_trabajo ot ON p.orden_id = ot.id 
        ORDER BY p.id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} elseif ($tipo === 'vehiculos') {
    fputcsv($output, ['Placa', 'Marca', 'Modelo', 'Anio', 'Color', 'Kilometraje', 'Cliente', 'DNI', 'Telefono']);
    $stmt = $pdo->query("SELECT v.placa, v.marca, v.modelo, v.anio, v.color, v.kilometraje, 
        c.nombres || ' ' || c.apellidos as cliente, c.dni_ruc, c.telefono 
        FROM vehiculos v 
        JOIN clientes c ON v.cliente_id = c.id 
        ORDER BY v.id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} elseif ($tipo === 'servicios') {
    fputcsv($output, ['Fecha', 'Orden', 'Servicio', 'Categoria', 'Placa', 'Cliente', 'Cantidad', 'Tarifa', 'Subtotal']);
    $stmt = $pdo->query("SELECT ot.fecha_ingreso, ot.numero_orden, s.nombre, s.categoria, v.placa, 
        c.nombres || ' ' || c.apellidos as cliente, os.cantidad, os.precio_unitario, os.subtotal 
        FROM orden_servicios os 
        JOIN servicios s ON os.servicio_id = s.id 
        JOIN ordenes_trabajo ot ON os.orden_id = ot.id 
        JOIN vehiculos v ON ot.vehiculo_id = v.id 
        JOIN clientes c ON ot.cliente_id = c.id 
        ORDER BY ot.id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} else {
    fputcsv($output, ['Nro Orden', 'Placa', 'Marca', 'Modelo', 'Cliente', 'Mecanico', 'Fecha Ingreso', 'Estado', 'Total Servicios', 'Total Repuestos', 'Total General']);
    $stmt = $pdo->query("SELECT ot.numero_orden, v.placa, v.marca, v.modelo, 
        c.nombres || ' ' || c.apellidos as cliente, COALESCE(m.nombre_completo, 'Sin asignar'), 
        ot.fecha_ingreso, ot.estado, ot.total_servicios, ot.total_repuestos, ot.total_general 
        FROM ordenes_trabajo ot 
        JOIN vehiculos v ON ot.vehiculo_id = v.id 
        JOIN clientes c ON ot.cliente_id = c.id 
        LEFT JOIN mecanicos m ON ot.mecanico_id = m.id 
        ORDER BY ot.id DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
