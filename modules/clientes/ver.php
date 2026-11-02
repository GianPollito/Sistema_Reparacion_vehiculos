<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/modules/clientes/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    setFlash('danger', 'Cliente no encontrado.');
    header('Location: ' . BASE_URL . '/modules/clientes/index.php');
    exit;
}

$stmtVeh = $pdo->prepare("SELECT * FROM vehiculos WHERE cliente_id = ? ORDER BY id DESC");
$stmtVeh->execute([$id]);
$vehiculos = $stmtVeh->fetchAll();

$stmtOrd = $pdo->prepare("SELECT ot.*, v.placa, v.marca, v.modelo, m.nombre_completo as mecanico_nombre 
    FROM ordenes_trabajo ot
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    LEFT JOIN mecanicos m ON ot.mecanico_id = m.id
    WHERE ot.cliente_id = ?
    ORDER BY ot.id DESC");
$stmtOrd->execute([$id]);
$ordenes = $stmtOrd->fetchAll();

$pageTitle = 'Detalle de Cliente: ' . $cliente['nombres'] . ' ' . $cliente['apellidos'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-id-badge text-primary me-2"></i> <?= htmlspecialchars($cliente['nombres'] . ' ' . $cliente['apellidos']) ?>
        </h2>
        <span class="badge bg-secondary font-monospace"><i class="fas fa-id-card me-1"></i> DNI/RUC: <?= htmlspecialchars($cliente['dni_ruc']) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="editar.php?id=<?= $cliente['id'] ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i> Editar Cliente
        </a>
        <a href="<?= BASE_URL ?>/modules/vehiculos/crear.php?cliente_id=<?= $cliente['id'] ?>" class="btn btn-motor-accent btn-sm">
            <i class="fas fa-car me-1"></i> + Asociar Vehículo
        </a>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user text-primary me-2"></i> Ficha de Contacto</h6>
            </div>
            <div class="card-body p-3">
                <ul class="list-unstyled mb-0">
                    <li class="py-2 border-bottom">
                        <small class="text-muted d-block">Teléfono:</small>
                        <strong><i class="fas fa-phone text-success me-1"></i> <?= htmlspecialchars($cliente['telefono'] ?? '-') ?></strong>
                    </li>
                    <li class="py-2 border-bottom">
                        <small class="text-muted d-block">Correo Electrónico:</small>
                        <strong><i class="fas fa-envelope text-info me-1"></i> <?= htmlspecialchars($cliente['email'] ?? '-') ?></strong>
                    </li>
                    <li class="py-2 border-bottom">
                        <small class="text-muted d-block">Dirección:</small>
                        <span class="text-secondary"><?= htmlspecialchars($cliente['direccion'] ?? 'No registrada') ?></span>
                    </li>
                    <li class="py-2">
                        <small class="text-muted d-block">Fecha de Registro:</small>
                        <span class="text-dark"><?= formatDate($cliente['creado_en'], true) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-car text-primary me-2"></i> Vehículos Registrados (<?= count($vehiculos) ?>)
                </h6>
                <a href="<?= BASE_URL ?>/modules/vehiculos/crear.php?cliente_id=<?= $cliente['id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i> Agregar
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($vehiculos)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-car-crash fs-2 text-secondary mb-2 d-block"></i>
                        Este cliente aún no tiene vehículos registrados en el sistema.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Placa</th>
                                    <th>Marca y Modelo</th>
                                    <th>Año</th>
                                    <th>Color</th>
                                    <th>Kilometraje</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vehiculos as $v): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-warning text-dark font-monospace fs-6">
                                                <?= htmlspecialchars($v['placa']) ?>
                                            </span>
                                        </td>
                                        <td><strong><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></strong></td>
                                        <td><?= htmlspecialchars($v['anio']) ?></td>
                                        <td><?= htmlspecialchars($v['color'] ?? '-') ?></td>
                                        <td><?= number_format($v['kilometraje']) ?> km</td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/modules/vehiculos/ver.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver Historial del Auto">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/modules/vehiculos/editar.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar Vehículo">
                                                <i class="fas fa-edit"></i>
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
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fas fa-clipboard-list text-primary me-2"></i> Historial de Órdenes de Trabajo del Cliente
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($ordenes)): ?>
            <div class="p-4 text-center text-muted">
                No hay órdenes de trabajo registradas para este cliente.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Orden</th>
                            <th>Vehículo</th>
                            <th>Fecha Ingreso</th>
                            <th>Mecánico Asignado</th>
                            <th>Estado</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordenes as $o): ?>
                            <tr>
                                <td>
                                    <strong class="font-monospace text-primary"><?= htmlspecialchars($o['numero_orden']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($o['placa']) ?></span>
                                    <small class="ms-1"><?= htmlspecialchars($o['marca'] . ' ' . $o['modelo']) ?></small>
                                </td>
                                <td><?= formatDate($o['fecha_ingreso'], true) ?></td>
                                <td><?= htmlspecialchars($o['mecanico_nombre'] ?? 'Sin asignar') ?></td>
                                <td><?= statusBadge($o['estado']) ?></td>
                                <td class="text-end fw-bold text-success"><?= formatCurrency($o['total_general']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/modules/ordenes/ver.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i> Ver Orden
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
