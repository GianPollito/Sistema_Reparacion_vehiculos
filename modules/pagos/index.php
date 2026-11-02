<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$metodo = trim($_GET['metodo'] ?? '');
$search = trim($_GET['search'] ?? '');

$query = "SELECT p.*, c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, c.dni_ruc as cliente_dni,
    ot.numero_orden, v.placa, v.marca, v.modelo
    FROM pagos p
    JOIN clientes c ON p.cliente_id = c.id
    JOIN ordenes_trabajo ot ON p.orden_id = ot.id
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    WHERE 1=1";
$params = [];

if (!empty($metodo)) {
    $query .= " AND p.metodo_pago = ?";
    $params[] = $metodo;
}
if (!empty($search)) {
    $query .= " AND (p.numero_comprobante LIKE ? OR ot.numero_orden LIKE ? OR v.placa LIKE ? OR c.nombres LIKE ? OR c.apellidos LIKE ? OR p.numero_operacion LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$pagos = $stmt->fetchAll();

$stmtTotales = $pdo->prepare("SELECT metodo_pago, SUM(monto) as total FROM pagos WHERE estado = 'Completado' GROUP BY metodo_pago");
$stmtTotales->execute();
$totalesMetodos = $stmtTotales->fetchAll(PDO::FETCH_KEY_PAIR);
$totalGeneralIngresos = array_sum($totalesMetodos);

$pageTitle = 'Registro de Pagos y Cobranzas';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-cash-register text-success me-2"></i> Cobranzas y Registro de Pagos
        </h2>
        <p class="text-muted small mb-0">Recepción de pagos mediante Efectivo, Tarjeta de Crédito, Tarjeta de Débito o Yape.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/modules/pagos/crear.php" class="btn btn-motor-accent shadow-sm">
            <i class="fas fa-plus me-1"></i> Registrar Pago
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                    <i class="fas fa-money-bill-wave fs-4"></i>
                </div>
                <div>
                    <small class="text-muted d-block fw-bold" style="font-size:0.75rem;">EFECTIVO</small>
                    <strong class="text-dark fs-5"><?= formatCurrency($totalesMetodos['Efectivo'] ?? 0) ?></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                    <i class="fas fa-credit-card fs-4"></i>
                </div>
                <div>
                    <small class="text-muted d-block fw-bold" style="font-size:0.75rem;">TARJETA CRÉDITO</small>
                    <strong class="text-dark fs-5"><?= formatCurrency($totalesMetodos['Tarjeta de Crédito'] ?? 0) ?></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                    <i class="fas fa-credit-card fs-4"></i>
                </div>
                <div>
                    <small class="text-muted d-block fw-bold" style="font-size:0.75rem;">TARJETA DÉBITO</small>
                    <strong class="text-dark fs-5"><?= formatCurrency($totalesMetodos['Tarjeta de Débito'] ?? 0) ?></strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 text-white" style="background-color: #742284;">
                    <i class="fas fa-mobile-alt fs-4"></i>
                </div>
                <div>
                    <small class="text-muted d-block fw-bold" style="font-size:0.75rem;">YAPE / DIGITAL</small>
                    <strong class="text-dark fs-5"><?= formatCurrency(($totalesMetodos['Yape'] ?? 0) + ($totalesMetodos['Plin'] ?? 0)) ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por comprobante, OT, placa o cliente..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="metodo" class="form-select">
                    <option value="">-- Todos los Métodos de Pago --</option>
                    <option value="Efectivo" <?= ($metodo === 'Efectivo') ? 'selected' : '' ?>>Efectivo</option>
                    <option value="Tarjeta de Crédito" <?= ($metodo === 'Tarjeta de Crédito') ? 'selected' : '' ?>>Tarjeta de Crédito</option>
                    <option value="Tarjeta de Débito" <?= ($metodo === 'Tarjeta de Débito') ? 'selected' : '' ?>>Tarjeta de Débito</option>
                    <option value="Yape" <?= ($metodo === 'Yape') ? 'selected' : '' ?>>Yape</option>
                    <option value="Plin" <?= ($metodo === 'Plin') ? 'selected' : '' ?>>Plin</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-motor-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                <?php if (!empty($search) || !empty($metodo)): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Limpiar filtro">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>N° Comprobante</th>
                    <th>Tipo</th>
                    <th>Orden de Trabajo</th>
                    <th>Cliente / Propietario</th>
                    <th>Método de Pago</th>
                    <th>N° Operación</th>
                    <th>Fecha de Pago</th>
                    <th class="text-end">Monto Cobrado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pagos)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fs-1 text-secondary mb-2 d-block"></i>
                            No se encontraron pagos registrados con los criterios indicados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pagos as $p): ?>
                        <tr>
                            <td>
                                <strong class="font-monospace text-primary fs-6"><?= htmlspecialchars($p['numero_comprobante']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['tipo_comprobante']) ?></span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/modules/ordenes/ver.php?id=<?= $p['orden_id'] ?>" class="text-decoration-none fw-semibold">
                                    <?= htmlspecialchars($p['numero_orden']) ?>
                                </a>
                                <small class="d-block text-muted">[<?= htmlspecialchars($p['placa']) ?>] <?= htmlspecialchars($p['marca'] . ' ' . $p['modelo']) ?></small>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= htmlspecialchars($p['cliente_nombres'] . ' ' . $p['cliente_apellidos']) ?></strong>
                                <small class="text-muted font-monospace">DNI: <?= htmlspecialchars($p['cliente_dni']) ?></small>
                            </td>
                            <td>
                                <?php 
                                    $badgeClass = 'bg-secondary';
                                    $icon = 'money-bill';
                                    if ($p['metodo_pago'] === 'Efectivo') { $badgeClass = 'bg-success'; $icon = 'money-bill-wave'; }
                                    elseif ($p['metodo_pago'] === 'Tarjeta de Crédito') { $badgeClass = 'bg-primary'; $icon = 'credit-card'; }
                                    elseif ($p['metodo_pago'] === 'Tarjeta de Débito') { $badgeClass = 'bg-info text-dark'; $icon = 'credit-card'; }
                                    elseif ($p['metodo_pago'] === 'Yape') { $badgeClass = 'text-white" style="background-color: #742284;'; $icon = 'mobile-alt'; }
                                ?>
                                <span class="badge <?= $badgeClass ?> px-2 py-1">
                                    <i class="fas fa-<?= $icon ?> me-1"></i> <?= htmlspecialchars($p['metodo_pago']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="font-monospace small text-muted"><?= htmlspecialchars($p['numero_operacion'] ?? '-') ?></span>
                            </td>
                            <td>
                                <small class="text-dark d-block"><?= formatDate($p['fecha_pago'], true) ?></small>
                            </td>
                            <td class="text-end fw-bold text-success fs-6">
                                <?= formatCurrency($p['monto']) ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="ver.php?id=<?= $p['id'] ?>" class="btn btn-outline-info" title="Ver Recibo de Pago">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="imprimir.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="Imprimir Ticket">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $p['id'] ?>" class="btn btn-outline-danger" title="Anular/Eliminar Pago" onclick="return confirmDelete('¿Está seguro de anular este pago?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
