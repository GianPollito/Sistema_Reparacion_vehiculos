<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$categoria = $_GET['categoria'] ?? '';

$query = "SELECT os.*, s.nombre as servicio_nombre, s.categoria as servicio_categoria, s.codigo as servicio_codigo,
    ot.numero_orden, ot.fecha_ingreso,
    v.placa, v.marca, v.modelo,
    c.nombres as cliente_nombres, c.apellidos as cliente_apellidos,
    m.nombre_completo as mecanico_nombre
    FROM orden_servicios os
    JOIN servicios s ON os.servicio_id = s.id
    JOIN ordenes_trabajo ot ON os.orden_id = ot.id
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    JOIN clientes c ON ot.cliente_id = c.id
    LEFT JOIN mecanicos m ON ot.mecanico_id = m.id
    WHERE DATE(ot.fecha_ingreso) BETWEEN ? AND ?";
$params = [$fechaInicio, $fechaFin];

if (!empty($categoria)) {
    $query .= " AND s.categoria = ?";
    $params[] = $categoria;
}

$query .= " ORDER BY ot.fecha_ingreso DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$serviciosRealizados = $stmt->fetchAll();

$totalCantidad = 0;
$totalMonto = 0;
foreach ($serviciosRealizados as $item) {
    $totalCantidad += $item['cantidad'];
    $totalMonto += floatval($item['subtotal']);
}

$categorias = $pdo->query("SELECT DISTINCT categoria FROM servicios")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Reporte de Servicios Realizados';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-wrench text-primary me-2"></i> Reporte: Servicios Realizados
        </h2>
        <p class="text-muted small mb-0">Detalle de todos los servicios de reparación ejecutados en el taller.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="exportar.php?tipo=servicios&fecha_inicio=<?= $fechaInicio ?>&fecha_fin=<?= $fechaFin ?>" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-excel me-1"></i> Exportar CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-print me-1"></i> Imprimir
        </button>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver a Reportes
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Fecha Desde:</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fechaInicio) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Fecha Hasta:</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Categoría:</label>
                <select name="categoria" class="form-select">
                    <option value="">-- Todas las Categorías --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($categoria === $cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-motor-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-muted small text-uppercase fw-bold">Total Servicios Ejecutados</span>
            <h3 class="fw-bold text-primary mb-0"><?= number_format($totalCantidad) ?> servicios</h3>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <span class="text-muted small text-uppercase fw-bold">Total Recaudado por Servicios</span>
            <h3 class="fw-bold text-success mb-0"><?= formatCurrency($totalMonto) ?></h3>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>N° Orden</th>
                    <th>Servicio Ejecutado</th>
                    <th>Categoría</th>
                    <th>Vehículo / Placa</th>
                    <th>Mecánico</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-end">Tarifa</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($serviciosRealizados)): ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted">No se encontraron servicios en el rango de fechas indicado.</td></tr>
                <?php else: ?>
                    <?php foreach ($serviciosRealizados as $item): ?>
                        <tr>
                            <td><?= formatDate($item['fecha_ingreso']) ?></td>
                            <td><strong class="font-monospace text-primary"><?= htmlspecialchars($item['numero_orden']) ?></strong></td>
                            <td><strong><?= htmlspecialchars($item['servicio_nombre']) ?></strong></td>
                            <td><span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($item['servicio_categoria']) ?></span></td>
                            <td>
                                <span class="badge bg-warning text-dark font-monospace"><?= htmlspecialchars($item['placa']) ?></span>
                                <small class="text-muted ms-1"><?= htmlspecialchars($item['marca']) ?></small>
                            </td>
                            <td><small><?= htmlspecialchars($item['mecanico_nombre'] ?? 'Sin asignar') ?></small></td>
                            <td class="text-center"><?= $item['cantidad'] ?></td>
                            <td class="text-end"><?= formatCurrency($item['precio_unitario']) ?></td>
                            <td class="text-end fw-bold text-success"><?= formatCurrency($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
