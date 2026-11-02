<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$mecanicoId = intval($_GET['mecanico_id'] ?? 0);
$estado = $_GET['estado'] ?? '';

$query = "SELECT ot.*, v.placa, v.marca, v.modelo, v.color,
    c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, c.telefono as cliente_telefono,
    m.nombre_completo as mecanico_nombre, m.especialidad as mecanico_especialidad
    FROM ordenes_trabajo ot
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    JOIN clientes c ON ot.cliente_id = c.id
    LEFT JOIN mecanicos m ON ot.mecanico_id = m.id
    WHERE ot.estado IN ('Pendiente', 'En Diagnóstico', 'En Proceso', 'Esperando Repuestos')";
$params = [];

if ($mecanicoId > 0) {
    $query .= " AND ot.mecanico_id = ?";
    $params[] = $mecanicoId;
}
if (!empty($estado)) {
    $query .= " AND ot.estado = ?";
    $params[] = $estado;
}

$query .= " ORDER BY ot.fecha_ingreso ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$reparacionesPendientes = $stmt->fetchAll();

$mecanicos = $pdo->query("SELECT id, nombre_completo FROM mecanicos WHERE estado = 'Activo'")->fetchAll();

$pageTitle = 'Reporte de Reparaciones Pendientes';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-tasks text-warning me-2"></i> Reporte: Reparaciones Pendientes y Activas
        </h2>
        <p class="text-muted small mb-0">Monitoreo de autos en bahía, reparaciones en proceso, demoras y asignaciones mecánicas.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="exportar.php?tipo=pendientes" class="btn btn-outline-success btn-sm">
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
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted mb-1">Filtrar por Mecánico:</label>
                <select name="mecanico_id" class="form-select">
                    <option value="">-- Todos los Mecánicos --</option>
                    <?php foreach ($mecanicos as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= ($mecanicoId == $m['id']) ? 'selected' : '' ?>><?= htmlspecialchars($m['nombre_completo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Estado de Reparación:</label>
                <select name="estado" class="form-select">
                    <option value="">-- Todos los Pendientes --</option>
                    <option value="Pendiente" <?= ($estado === 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                    <option value="En Diagnóstico" <?= ($estado === 'En Diagnóstico') ? 'selected' : '' ?>>En Diagnóstico</option>
                    <option value="En Proceso" <?= ($estado === 'En Proceso') ? 'selected' : '' ?>>En Proceso</option>
                    <option value="Esperando Repuestos" <?= ($estado === 'Esperando Repuestos') ? 'selected' : '' ?>>Esperando Repuestos</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-motor-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filtrar Reparaciones
                </button>
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
                    <th>Cliente / Contacto</th>
                    <th>Mecánico Asignado</th>
                    <th>Fecha de Ingreso</th>
                    <th>Entrega Estimada</th>
                    <th>Estado</th>
                    <th class="text-end">Presupuesto</th>
                    <th class="text-end">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reparacionesPendientes)): ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted">
                        <i class="fas fa-check-circle fs-1 text-success mb-2 d-block"></i>
                        ¡Excelente! No hay reparaciones pendientes en el taller en este momento.
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($reparacionesPendientes as $r): ?>
                        <tr>
                            <td><strong class="font-monospace text-primary"><?= htmlspecialchars($r['numero_orden']) ?></strong></td>
                            <td>
                                <span class="badge bg-warning text-dark font-monospace"><?= htmlspecialchars($r['placa']) ?></span>
                                <small class="d-block text-muted"><?= htmlspecialchars($r['marca'] . ' ' . $r['modelo']) ?></small>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= htmlspecialchars($r['cliente_nombres'] . ' ' . $r['cliente_apellidos']) ?></strong>
                                <small class="text-muted"><i class="fas fa-phone text-success me-1"></i> <?= htmlspecialchars($r['cliente_telefono'] ?? '-') ?></small>
                            </td>
                            <td>
                                <?php if (!empty($r['mecanico_nombre'])): ?>
                                    <strong><?= htmlspecialchars($r['mecanico_nombre']) ?></strong>
                                    <small class="d-block text-muted"><?= htmlspecialchars($r['mecanico_especialidad']) ?></small>
                                <?php else: ?>
                                    <span class="badge bg-danger">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= formatDate($r['fecha_ingreso'], true) ?></small></td>
                            <td>
                                <strong class="text-danger small"><?= formatDate($r['fecha_estimada_entrega'], true) ?></strong>
                            </td>
                            <td><?= statusBadge($r['estado']) ?></td>
                            <td class="text-end fw-bold text-dark fs-6">
                                <?= formatCurrency($r['total_general']) ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/modules/ordenes/ver.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-tools me-1"></i> Atender Orden
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
