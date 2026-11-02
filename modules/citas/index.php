<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$estado = trim($_GET['estado'] ?? '');
$fecha = trim($_GET['fecha'] ?? '');

$query = "SELECT c.*, cl.nombres as cliente_nombres, cl.apellidos as cliente_apellidos, cl.telefono as cliente_telefono,
    v.placa, v.marca, v.modelo
    FROM citas c
    JOIN clientes cl ON c.cliente_id = cl.id
    JOIN vehiculos v ON c.vehiculo_id = v.id
    WHERE 1=1";
$params = [];

if (!empty($estado)) {
    $query .= " AND c.estado = ?";
    $params[] = $estado;
}
if (!empty($fecha)) {
    $query .= " AND c.fecha_cita = ?";
    $params[] = $fecha;
}

$query .= " ORDER BY c.fecha_cita ASC, c.hora_cita ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$citas = $stmt->fetchAll();

$pageTitle = 'Citas de Mantenimiento';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-calendar-alt text-primary me-2"></i> Agenda de Citas de Mantenimiento
        </h2>
        <p class="text-muted small mb-0">Programación de atenciones preventivas y diagnósticos mecánicos de clientes.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/modules/citas/crear.php" class="btn btn-motor-accent shadow-sm">
            <i class="fas fa-calendar-plus me-1"></i> Programar Nueva Cita
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small text-muted fw-bold mb-1">Filtrar por Estado:</label>
                <select name="estado" class="form-select">
                    <option value="">-- Todos los Estados --</option>
                    <option value="Pendiente" <?= ($estado === 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                    <option value="Confirmada" <?= ($estado === 'Confirmada') ? 'selected' : '' ?>>Confirmada</option>
                    <option value="En Atención" <?= ($estado === 'En Atención') ? 'selected' : '' ?>>En Atención</option>
                    <option value="Completada" <?= ($estado === 'Completada') ? 'selected' : '' ?>>Completada</option>
                    <option value="Cancelada" <?= ($estado === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted fw-bold mb-1">Filtrar por Fecha:</label>
                <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($fecha) ?>">
            </div>
            <div class="col-md-4 d-flex gap-2 align-self-end mt-2 mt-md-0">
                <button type="submit" class="btn btn-motor-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Filtrar Agenda
                </button>
                <?php if (!empty($estado) || !empty($fecha)): ?>
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
                    <th>Código</th>
                    <th>Fecha &amp; Hora</th>
                    <th>Cliente</th>
                    <th>Vehículo</th>
                    <th>Motivo de Visita</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($citas)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times fs-1 text-secondary mb-2 d-block"></i>
                            No se encontraron citas agendadas con los criterios seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($citas as $c): ?>
                        <tr>
                            <td>
                                <strong class="font-monospace text-primary"><?= htmlspecialchars($c['codigo']) ?></strong>
                            </td>
                            <td>
                                <div><i class="far fa-calendar-alt text-primary me-1"></i> <strong><?= formatDate($c['fecha_cita']) ?></strong></div>
                                <small class="text-muted"><i class="far fa-clock text-warning me-1"></i> <?= date('h:i A', strtotime($c['hora_cita'])) ?></small>
                            </td>
                            <td>
                                <strong class="d-block text-dark"><?= htmlspecialchars($c['cliente_nombres'] . ' ' . $c['cliente_apellidos']) ?></strong>
                                <small class="text-muted"><i class="fas fa-phone text-success me-1"></i> <?= htmlspecialchars($c['cliente_telefono'] ?? '-') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark font-monospace"><?= htmlspecialchars($c['placa']) ?></span>
                                <small class="d-block text-muted"><?= htmlspecialchars($c['marca'] . ' ' . $c['modelo']) ?></small>
                            </td>
                            <td style="max-width: 280px;">
                                <span class="small"><?= htmlspecialchars($c['motivo']) ?></span>
                            </td>
                            <td><?= statusBadge($c['estado']) ?></td>
                            <td class="text-end">
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Estado
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                        <li><a class="dropdown-item" href="cambiar_estado.php?id=<?= $c['id'] ?>&estado=Pendiente"><i class="fas fa-clock text-warning me-2"></i> Pendiente</a></li>
                                        <li><a class="dropdown-item" href="cambiar_estado.php?id=<?= $c['id'] ?>&estado=Confirmada"><i class="fas fa-check-circle text-primary me-2"></i> Confirmada</a></li>
                                        <li><a class="dropdown-item" href="cambiar_estado.php?id=<?= $c['id'] ?>&estado=En Atención"><i class="fas fa-tools text-info me-2"></i> En Atención</a></li>
                                        <li><a class="dropdown-item" href="cambiar_estado.php?id=<?= $c['id'] ?>&estado=Completada"><i class="fas fa-flag-checkered text-success me-2"></i> Completada</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="cambiar_estado.php?id=<?= $c['id'] ?>&estado=Cancelada"><i class="fas fa-times-circle text-danger me-2"></i> Cancelada</a></li>
                                    </ul>
                                </div>
                                
                                <a href="<?= BASE_URL ?>/modules/ordenes/crear.php?vehiculo_id=<?= $c['vehiculo_id'] ?>&cita_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-success" title="Generar Orden de Trabajo">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                                <a href="editar.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar Cita">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="eliminar.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" title="Eliminar Cita" onclick="return confirmDelete('¿Desea eliminar esta cita?');">
                                    <i class="fas fa-trash-alt"></i>
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
