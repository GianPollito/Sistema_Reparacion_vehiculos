<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/modules/vehiculos/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT v.*, c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, 
        c.telefono as cliente_telefono, c.email as cliente_email, c.dni_ruc as cliente_dni
    FROM vehiculos v
    JOIN clientes c ON v.cliente_id = c.id
    WHERE v.id = ?");
$stmt->execute([$id]);
$vehiculo = $stmt->fetch();

if (!$vehiculo) {
    setFlash('danger', 'Vehículo no encontrado.');
    header('Location: ' . BASE_URL . '/modules/vehiculos/index.php');
    exit;
}

$stmtOrd = $pdo->prepare("SELECT ot.*, m.nombre_completo as mecanico_nombre
    FROM ordenes_trabajo ot
    LEFT JOIN mecanicos m ON ot.mecanico_id = m.id
    WHERE ot.vehiculo_id = ?
    ORDER BY ot.id DESC");
$stmtOrd->execute([$id]);
$ordenes = $stmtOrd->fetchAll();

$stmtDiag = $pdo->prepare("SELECT d.*, m.nombre_completo as mecanico_nombre
    FROM diagnosticos d
    JOIN mecanicos m ON d.mecanico_id = m.id
    WHERE d.vehiculo_id = ?
    ORDER BY d.id DESC");
$stmtDiag->execute([$id]);
$diagnosticos = $stmtDiag->fetchAll();

$pageTitle = 'Historial Técnico: ' . $vehiculo['placa'] . ' (' . $vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ')';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <span class="badge bg-warning text-dark font-monospace fs-4 me-2"><?= htmlspecialchars($vehiculo['placa']) ?></span>
            <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?> (<?= htmlspecialchars($vehiculo['anio']) ?>)
        </h2>
        <p class="text-muted small mb-0">Historial completo de reparaciones, diagnósticos y servicios de mantenimiento.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/modules/ordenes/crear.php?vehiculo_id=<?= $vehiculo['id'] ?>" class="btn btn-motor-accent btn-sm">
            <i class="fas fa-plus me-1"></i> Nueva Orden de Trabajo
        </a>
        <a href="editar.php?id=<?= $vehiculo['id'] ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i> Editar Vehículo
        </a>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-car text-primary me-2"></i> Ficha Técnica del Automóvil
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Marca:</small>
                        <strong class="fs-6"><?= htmlspecialchars($vehiculo['marca']) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Modelo:</small>
                        <strong class="fs-6"><?= htmlspecialchars($vehiculo['modelo']) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Año de Fabricación:</small>
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($vehiculo['anio']) ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Color:</small>
                        <span><?= htmlspecialchars($vehiculo['color'] ?? '-') ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Kilometraje Registrado:</small>
                        <span class="fw-bold text-primary"><i class="fas fa-tachometer-alt me-1"></i> <?= number_format($vehiculo['kilometraje']) ?> km</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Registrado en Sistema:</small>
                        <span class="text-secondary"><?= formatDate($vehiculo['creado_en']) ?></span>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Observaciones:</small>
                        <p class="mb-0 small text-secondary bg-light p-2 rounded"><?= nl2br(htmlspecialchars($vehiculo['observaciones'] ?? 'Sin observaciones iniciales.')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-user text-primary me-2"></i> Datos del Propietario
                </h6>
            </div>
            <div class="card-body p-3">
                <ul class="list-unstyled mb-0">
                    <li class="py-2 border-bottom">
                        <small class="text-muted d-block">Cliente:</small>
                        <a href="<?= BASE_URL ?>/modules/clientes/ver.php?id=<?= $vehiculo['cliente_id'] ?>" class="fw-bold text-decoration-none fs-6 text-primary">
                            <?= htmlspecialchars($vehiculo['cliente_nombres'] . ' ' . $vehiculo['cliente_apellidos']) ?>
                        </a>
                    </li>
                    <li class="py-2 border-bottom">
                        <small class="text-muted d-block">DNI / RUC:</small>
                        <span class="font-monospace fw-semibold"><?= htmlspecialchars($vehiculo['cliente_dni']) ?></span>
                    </li>
                    <li class="py-2 border-bottom">
                        <small class="text-muted d-block">Teléfono:</small>
                        <span><i class="fas fa-phone text-success me-1"></i> <?= htmlspecialchars($vehiculo['cliente_telefono'] ?? '-') ?></span>
                    </li>
                    <li class="py-2">
                        <small class="text-muted d-block">Correo:</small>
                        <span><i class="fas fa-envelope text-info me-1"></i> <?= htmlspecialchars($vehiculo['cliente_email'] ?? '-') ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fas fa-history text-primary me-2"></i> Historial de Órdenes de Trabajo (<?= count($ordenes) ?>)
        </h5>
        <a href="<?= BASE_URL ?>/modules/ordenes/crear.php?vehiculo_id=<?= $vehiculo['id'] ?>" class="btn btn-sm btn-motor-accent">
            <i class="fas fa-plus me-1"></i> Nueva Orden
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($ordenes)): ?>
            <div class="p-4 text-center text-muted">
                Este vehículo aún no cuenta con órdenes de trabajo registradas.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Orden</th>
                            <th>Fecha Ingreso</th>
                            <th>Mecánico Asignado</th>
                            <th>Problema Reportado</th>
                            <th>Estado</th>
                            <th class="text-end">Costo Total</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordenes as $o): ?>
                            <tr>
                                <td>
                                    <strong class="font-monospace text-primary"><?= htmlspecialchars($o['numero_orden']) ?></strong>
                                </td>
                                <td><?= formatDate($o['fecha_ingreso'], true) ?></td>
                                <td><?= htmlspecialchars($o['mecanico_nombre'] ?? 'Sin asignar') ?></td>
                                <td style="max-width: 250px;">
                                    <span class="text-truncate d-block small"><?= htmlspecialchars($o['problema_reportado']) ?></span>
                                </td>
                                <td><?= statusBadge($o['estado']) ?></td>
                                <td class="text-end fw-bold text-success"><?= formatCurrency($o['total_general']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/modules/ordenes/ver.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> Ver Detalle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fas fa-stethoscope text-danger me-2"></i> Diagnósticos de Fallas Mecánicas (<?= count($diagnosticos) ?>)
        </h5>
        <a href="<?= BASE_URL ?>/modules/diagnosticos/crear.php?vehiculo_id=<?= $vehiculo['id'] ?>" class="btn btn-sm btn-outline-danger">
            <i class="fas fa-plus me-1"></i> Registrar Diagnóstico
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($diagnosticos)): ?>
            <div class="p-4 text-center text-muted">
                No hay diagnósticos técnicos registrados para este vehículo.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Mecánico</th>
                            <th>Falla Reportada</th>
                            <th>Diagnóstico Técnico</th>
                            <th>Gravedad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($diagnosticos as $d): ?>
                            <tr>
                                <td><?= formatDate($d['fecha_diagnostico'], true) ?></td>
                                <td><strong><?= htmlspecialchars($d['mecanico_nombre']) ?></strong></td>
                                <td class="fw-semibold text-danger"><?= htmlspecialchars($d['descripcion_falla']) ?></td>
                                <td class="small"><?= htmlspecialchars($d['diagnostico_tecnico']) ?></td>
                                <td><?= statusBadge($d['gravedad']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
