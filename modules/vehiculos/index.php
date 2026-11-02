<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$search = trim($_GET['search'] ?? '');

if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT v.*, c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, c.telefono as cliente_telefono,
        (SELECT COUNT(*) FROM ordenes_trabajo ot WHERE ot.vehiculo_id = v.id) as total_ordenes,
        (SELECT COUNT(*) FROM diagnosticos d WHERE d.vehiculo_id = v.id) as total_diagnosticos
        FROM vehiculos v
        JOIN clientes c ON v.cliente_id = c.id
        WHERE v.placa LIKE ? OR v.marca LIKE ? OR v.modelo LIKE ? OR c.nombres LIKE ? OR c.apellidos LIKE ?
        ORDER BY v.id DESC");
    $term = "%$search%";
    $stmt->execute([$term, $term, $term, $term, $term]);
} else {
    $stmt = $pdo->prepare("SELECT v.*, c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, c.telefono as cliente_telefono,
        (SELECT COUNT(*) FROM ordenes_trabajo ot WHERE ot.vehiculo_id = v.id) as total_ordenes,
        (SELECT COUNT(*) FROM diagnosticos d WHERE d.vehiculo_id = v.id) as total_diagnosticos
        FROM vehiculos v
        JOIN clientes c ON v.cliente_id = c.id
        ORDER BY v.id DESC");
    $stmt->execute();
}
$vehiculos = $stmt->fetchAll();

$pageTitle = 'Registro de Vehículos';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-car-side text-primary me-2"></i> Vehículos Atendidos
        </h2>
        <p class="text-muted small mb-0">Parque automotor registrado en el taller: placa, marca, modelo y año de fabricación.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/modules/vehiculos/crear.php" class="btn btn-motor-accent shadow-sm">
            <i class="fas fa-plus-circle me-1"></i> Registrar Vehículo
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2">
            <div class="col-md-9 col-sm-8">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por placa (ej. ABC-123), marca, modelo o propietario..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-2">
                <button type="submit" class="btn btn-motor-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Buscar
                </button>
                <?php if (!empty($search)): ?>
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
                    <th>Placa</th>
                    <th>Marca &amp; Modelo</th>
                    <th>Año</th>
                    <th>Color</th>
                    <th>Kilometraje</th>
                    <th>Propietario / Cliente</th>
                    <th class="text-center">Historial</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vehiculos)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-car-crash fs-1 text-secondary mb-2 d-block"></i>
                            No se encontraron vehículos registrados en el taller.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($vehiculos as $v): ?>
                        <tr>
                            <td>
                                <span class="badge bg-warning text-dark font-monospace fs-6 px-3 py-2 shadow-sm border border-dark border-opacity-10">
                                    <i class="fas fa-car text-danger me-1"></i> <?= htmlspecialchars($v['placa']) ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($v['marca']) ?></strong>
                                <span class="text-secondary"><?= htmlspecialchars($v['modelo']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($v['anio']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($v['color'] ?? '-') ?></td>
                            <td>
                                <span class="fw-semibold text-secondary">
                                    <i class="fas fa-tachometer-alt me-1 text-info"></i> <?= number_format($v['kilometraje']) ?> km
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/modules/clientes/ver.php?id=<?= $v['cliente_id'] ?>" class="text-decoration-none fw-bold text-primary d-block">
                                    <?= htmlspecialchars($v['cliente_nombres'] . ' ' . $v['cliente_apellidos']) ?>
                                </a>
                                <small class="text-muted"><i class="fas fa-phone text-success me-1"></i> <?= htmlspecialchars($v['cliente_telefono'] ?? '-') ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-1" title="Órdenes de Trabajo">
                                    <i class="fas fa-clipboard-list me-1"></i> <?= $v['total_ordenes'] ?> OT
                                </span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="Diagnósticos de Falla">
                                    <i class="fas fa-stethoscope me-1"></i> <?= $v['total_diagnosticos'] ?> Diag
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="ver.php?id=<?= $v['id'] ?>" class="btn btn-outline-info" title="Ver Historial Completo">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="editar.php?id=<?= $v['id'] ?>" class="btn btn-outline-primary" title="Editar Vehículo">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $v['id'] ?>" class="btn btn-outline-danger" title="Eliminar" onclick="return confirmDelete('¿Está seguro de eliminar este vehículo? Se perderán sus órdenes asociadas.');">
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
