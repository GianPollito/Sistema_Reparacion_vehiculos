<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
$metodo = $_GET['metodo'] ?? '';

$query = "SELECT p.*, c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, c.dni_ruc as cliente_dni,
    ot.numero_orden, v.placa, v.marca
    FROM pagos p
    JOIN clientes c ON p.cliente_id = c.id
    JOIN ordenes_trabajo ot ON p.orden_id = ot.id
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    WHERE p.estado = 'Completado' AND DATE(p.fecha_pago) BETWEEN ? AND ?";
$params = [$fechaInicio, $fechaFin];

if (!empty($metodo)) {
    $query .= " AND p.metodo_pago = ?";
    $params[] = $metodo;
}

$query .= " ORDER BY p.fecha_pago DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pagos = $stmt->fetchAll();

$totalIngresos = 0;
$conteoPorMetodo = [];
foreach ($pagos as $p) {
    $totalIngresos += floatval($p['monto']);
    $m = $p['metodo_pago'];
    $conteoPorMetodo[$m] = ($conteoPorMetodo[$m] ?? 0) + floatval($p['monto']);
}

$pageTitle = 'Reporte de Ingresos Obtenidos';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-coins text-success me-2"></i> Reporte: Ingresos Obtenidos
        </h2>
        <p class="text-muted small mb-0">Balance de recaudación por Efectivo, Tarjeta de Crédito, Débito y Yape.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="exportar.php?tipo=ingresos&fecha_inicio=<?= $fechaInicio ?>&fecha_fin=<?= $fechaFin ?>" class="btn btn-outline-success btn-sm">
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
                <label class="form-label small fw-bold text-muted mb-1">Filtrar por Método de Pago:</label>
                <select name="metodo" class="form-select">
                    <option value="">-- Todos los Métodos --</option>
                    <option value="Efectivo" <?= ($metodo === 'Efectivo') ? 'selected' : '' ?>>Efectivo</option>
                    <option value="Tarjeta de Crédito" <?= ($metodo === 'Tarjeta de Crédito') ? 'selected' : '' ?>>Tarjeta de Crédito</option>
                    <option value="Tarjeta de Débito" <?= ($metodo === 'Tarjeta de Débito') ? 'selected' : '' ?>>Tarjeta de Débito</option>
                    <option value="Yape" <?= ($metodo === 'Yape') ? 'selected' : '' ?>>Yape</option>
                    <option value="Plin" <?= ($metodo === 'Plin') ? 'selected' : '' ?>>Plin</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-motor-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filtrar Ingresos
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <span class="text-muted small text-uppercase fw-bold">TOTAL RECAUDADO</span>
            <h2 class="fw-bold text-success mb-1"><?= formatCurrency($totalIngresos) ?></h2>
            <small class="text-muted"><?= count($pagos) ?> transacciones cobradas</small>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <span class="text-muted small text-uppercase fw-bold mb-2 d-block">Desglose por Canal de Cobro:</span>
            <div class="row g-2">
                <div class="col-sm-3 col-6">
                    <div class="p-2 border rounded bg-light text-center">
                        <small class="text-muted d-block" style="font-size:0.7rem;">EFECTIVO</small>
                        <strong class="text-dark small"><?= formatCurrency($conteoPorMetodo['Efectivo'] ?? 0) ?></strong>
                    </div>
                </div>
                <div class="col-sm-3 col-6">
                    <div class="p-2 border rounded bg-light text-center">
                        <small class="text-muted d-block" style="font-size:0.7rem;">TARJ. CRÉDITO</small>
                        <strong class="text-primary small"><?= formatCurrency($conteoPorMetodo['Tarjeta de Crédito'] ?? 0) ?></strong>
                    </div>
                </div>
                <div class="col-sm-3 col-6">
                    <div class="p-2 border rounded bg-light text-center">
                        <small class="text-muted d-block" style="font-size:0.7rem;">TARJ. DÉBITO</small>
                        <strong class="text-info small"><?= formatCurrency($conteoPorMetodo['Tarjeta de Débito'] ?? 0) ?></strong>
                    </div>
                </div>
                <div class="col-sm-3 col-6">
                    <div class="p-2 border rounded bg-light text-center">
                        <small class="text-muted d-block" style="font-size:0.7rem;">YAPE / PLIN</small>
                        <strong class="text-purple small" style="color:#742284;"><?= formatCurrency(($conteoPorMetodo['Yape'] ?? 0) + ($conteoPorMetodo['Plin'] ?? 0)) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Fecha de Pago</th>
                    <th>N° Comprobante</th>
                    <th>Tipo</th>
                    <th>N° Orden</th>
                    <th>Cliente</th>
                    <th>Método de Pago</th>
                    <th>N° Operación / Ref</th>
                    <th class="text-end">Monto Cobrado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagos)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted">No se registraron ingresos en el período seleccionado.</td></tr>
                <?php else: ?>
                    <?php foreach ($pagos as $p): ?>
                        <tr>
                            <td><?= formatDate($p['fecha_pago'], true) ?></td>
                            <td><strong class="font-monospace text-primary"><?= htmlspecialchars($p['numero_comprobante']) ?></strong></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p['tipo_comprobante']) ?></span></td>
                            <td>
                                <a href="<?= BASE_URL ?>/modules/ordenes/ver.php?id=<?= $p['orden_id'] ?>" class="text-decoration-none fw-semibold">
                                    <?= htmlspecialchars($p['numero_orden']) ?>
                                </a>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($p['cliente_nombres'] . ' ' . $p['cliente_apellidos']) ?></strong>
                                <small class="text-muted font-monospace">DNI: <?= htmlspecialchars($p['cliente_dni']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-dark border px-2 py-1">
                                    <?= htmlspecialchars($p['metodo_pago']) ?>
                                </span>
                            </td>
                            <td><span class="font-monospace small text-muted"><?= htmlspecialchars($p['numero_operacion'] ?? '-') ?></span></td>
                            <td class="text-end fw-bold text-success fs-6"><?= formatCurrency($p['monto']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
