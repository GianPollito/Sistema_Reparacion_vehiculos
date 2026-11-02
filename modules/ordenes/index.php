<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$estado = trim($_GET['estado'] ?? '');
$search = trim($_GET['search'] ?? '');

$query = "SELECT ot.*, v.placa, v.marca, v.modelo, 
    c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, c.telefono as cliente_telefono,
    m.nombre_completo as mecanico_nombre,
    (SELECT COALESCE(SUM(p.monto), 0) FROM pagos p WHERE p.orden_id = ot.id AND p.estado = 'Completado') as total_pagado
    FROM ordenes_trabajo ot
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    JOIN clientes c ON ot.cliente_id = c.id
    LEFT JOIN mecanicos m ON ot.mecanico_id = m.id
    WHERE 1=1";
$params = [];

if (!empty($estado)) {
    $query .= " AND ot.estado = ?";
    $params[] = $estado;
}
if (!empty($search)) {
    $query .= " AND (ot.numero_orden LIKE ? OR v.placa LIKE ? OR c.nombres LIKE ? OR c.apellidos LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$query .= " ORDER BY ot.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$ordenes = $stmt->fetchAll();

$pageTitle = 'Órdenes de Trabajo';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-clipboard-list text-primary me-2"></i> Órdenes de Trabajo
        </h2>
        <p class="text-muted small mb-0">Seguimiento en tiempo real de reparaciones, servicios mecánicos, repuestos y entregas.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/modules/ordenes/crear.php" class="btn btn-motor-accent shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Generar Nueva Orden
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por N° Orden (OT-2026-...), placa o cliente..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="estado" class="form-select">
                    <option value="">-- Todos los Estados de Reparación --</option>
                    <option value="Pendiente" <?= ($estado === 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                    <option value="En Diagnóstico" <?= ($estado === 'En Diagnóstico') ? 'selected' : '' ?>>En Diagnóstico</option>
                    <option value="En Proceso" <?= ($estado === 'En Proceso') ? 'selected' : '' ?>>En Proceso</option>
                    <option value="Esperando Repuestos" <?= ($estado === 'Esperando Repuestos') ? 'selected' : '' ?>>Esperando Repuestos</option>
                    <option value="Finalizada" <?= ($estado === 'Finalizada') ? 'selected' : '' ?>>Finalizada</option>
                    <option value="Entregada" <?= ($estado === 'Entregada') ? 'selected' : '' ?>>Entregada</option>
                    <option value="Cancelada" <?= ($estado === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-motor-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                <?php if (!empty($search) || !empty($estado)): ?>
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
                    <th>N° Orden</th>
                    <th>Vehículo / Placa</th>
                    <th>Cliente / Teléfono</th>
                    <th>Mecánico Asignado</th>
                    <th>Ingreso / Estimado</th>
                    <th>Estado</th>
                    <th class="text-end">Total Presupuesto</th>
                    <th class="text-end">Pago</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ordenes)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-file-excel fs-1 text-secondary mb-2 d-block"></i>
                            No se encontraron órdenes de trabajo registradas con los filtros indicados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ordenes as $o): ?>
                        <tr>
                            <td>
                                <strong class="font-monospace text-primary fs-6 d-block"><?= htmlspecialchars($o['numero_orden']) ?></strong>
                                <small class="text-muted"><i class="fas fa-gas-pump me-1"></i> Comb: <?= htmlspecialchars($o['nivel_combustible'] ?? '-') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark font-monospace"><?= htmlspecialchars($o['placa']) ?></span>
                                <small class="d-block text-muted"><?= htmlspecialchars($o['marca'] . ' ' . $o['modelo']) ?></small>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= htmlspecialchars($o['cliente_nombres'] . ' ' . $o['cliente_apellidos']) ?></strong>
                                <small class="text-muted"><i class="fas fa-phone text-success me-1"></i> <?= htmlspecialchars($o['cliente_telefono'] ?? '-') ?></small>
                            </td>
                            <td>
                                <?php if (!empty($o['mecanico_nombre'])): ?>
                                    <span class="text-dark fw-semibold small"><i class="fas fa-user-cog text-primary me-1"></i> <?= htmlspecialchars($o['mecanico_nombre']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-dark d-block">In: <?= formatDate($o['fecha_ingreso']) ?></small>
                                <small class="text-muted">Fin: <?= formatDate($o['fecha_estimada_entrega']) ?></small>
                            </td>
                            <td><?= statusBadge($o['estado']) ?></td>
                            <td class="text-end fw-bold text-dark fs-6">
                                <?= formatCurrency($o['total_general']) ?>
                            </td>
                            <td class="text-end">
                                <?php if ($o['total_pagado'] >= $o['total_general'] && $o['total_general'] > 0): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fas fa-check-circle me-1"></i> Pagado</span>
                                <?php elseif ($o['total_pagado'] > 0): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="fas fa-hourglass-half me-1"></i> <?= formatCurrency($o['total_pagado']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fas fa-clock me-1"></i> Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="ver.php?id=<?= $o['id'] ?>" class="btn btn-outline-info" title="Ver Detalle, Repuestos y Diagnósticos">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="imprimir.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="Imprimir Orden de Trabajo">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="editar.php?id=<?= $o['id'] ?>" class="btn btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $o['id'] ?>" class="btn btn-outline-danger" title="Eliminar" onclick="return confirmDelete('¿Está seguro de eliminar esta orden de trabajo?');">
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
