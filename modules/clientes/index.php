<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$search = trim($_GET['search'] ?? '');

if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT c.*, 
        (SELECT COUNT(*) FROM vehiculos v WHERE v.cliente_id = c.id) as total_vehiculos,
        (SELECT COUNT(*) FROM ordenes_trabajo ot WHERE ot.cliente_id = c.id) as total_ordenes
        FROM clientes c 
        WHERE c.dni_ruc LIKE ? OR c.nombres LIKE ? OR c.apellidos LIKE ? OR c.telefono LIKE ?
        ORDER BY c.id DESC");
    $term = "%$search%";
    $stmt->execute([$term, $term, $term, $term]);
} else {
    $stmt = $pdo->prepare("SELECT c.*, 
        (SELECT COUNT(*) FROM vehiculos v WHERE v.cliente_id = c.id) as total_vehiculos,
        (SELECT COUNT(*) FROM ordenes_trabajo ot WHERE ot.cliente_id = c.id) as total_ordenes
        FROM clientes c 
        ORDER BY c.id DESC");
    $stmt->execute();
}
$clientes = $stmt->fetchAll();

$pageTitle = 'Administración de Clientes';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-users text-primary me-2"></i> Clientes del Taller
        </h2>
        <p class="text-muted small mb-0">Gestión de propietarios de vehículos, datos de contacto e historial de servicio.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/modules/clientes/crear.php" class="btn btn-motor-accent shadow-sm">
            <i class="fas fa-user-plus me-1"></i> Nuevo Cliente
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2">
            <div class="col-md-9 col-sm-8">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por DNI/RUC, nombres, apellidos o teléfono..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-2">
                <button type="submit" class="btn btn-motor-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Filtrar
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
                    <th>#</th>
                    <th>DNI / RUC</th>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th>Dirección</th>
                    <th class="text-center">Vehículos</th>
                    <th class="text-center">Órdenes</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clientes)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-user-slash fs-1 text-secondary mb-2 d-block"></i>
                            No se encontraron clientes registrados con los criterios especificados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clientes as $idx => $c): ?>
                        <tr>
                            <td class="text-muted fw-bold"><?= $idx + 1 ?></td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">
                                    <i class="fas fa-id-card text-muted me-1"></i> <?= htmlspecialchars($c['dni_ruc']) ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?></strong>
                                <small class="text-muted">Registrado: <?= formatDate($c['creado_en']) ?></small>
                            </td>
                            <td>
                                <div><i class="fas fa-phone text-success me-1 small"></i> <?= htmlspecialchars($c['telefono'] ?? '-') ?></div>
                                <small class="text-muted"><i class="fas fa-envelope text-info me-1 small"></i> <?= htmlspecialchars($c['email'] ?? '-') ?></small>
                            </td>
                            <td>
                                <small class="text-secondary"><?= htmlspecialchars($c['direccion'] ?? 'No especificada') ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill px-2 py-1">
                                    <i class="fas fa-car me-1"></i> <?= $c['total_vehiculos'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary rounded-pill px-2 py-1">
                                    <i class="fas fa-wrench me-1"></i> <?= $c['total_ordenes'] ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="ver.php?id=<?= $c['id'] ?>" class="btn btn-outline-info" title="Ver Historial y Vehículos">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="editar.php?id=<?= $c['id'] ?>" class="btn btn-outline-primary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $c['id'] ?>" class="btn btn-outline-danger" title="Eliminar" onclick="return confirmDelete('¿Está seguro de eliminar a este cliente? Se eliminarán también sus vehículos asociados.');">
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
