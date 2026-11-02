<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$especialidad = trim($_GET['especialidad'] ?? '');
$search = trim($_GET['search'] ?? '');

$query = "SELECT m.*, 
    (SELECT COUNT(*) FROM ordenes_trabajo ot WHERE ot.mecanico_id = m.id AND ot.estado IN ('En Proceso', 'En Diagnóstico')) as ordenes_activas,
    (SELECT COUNT(*) FROM ordenes_trabajo ot WHERE ot.mecanico_id = m.id) as total_ordenes
    FROM mecanicos m WHERE 1=1";
$params = [];

if (!empty($especialidad)) {
    $query .= " AND m.especialidad LIKE ?";
    $params[] = "%$especialidad%";
}
if (!empty($search)) {
    $query .= " AND (m.nombre_completo LIKE ? OR m.dni LIKE ? OR m.telefono LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}
$query .= " ORDER BY m.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$mecanicos = $stmt->fetchAll();

$stmtEsp = $pdo->prepare("SELECT DISTINCT especialidad FROM mecanicos WHERE especialidad IS NOT NULL AND especialidad != ''");
$stmtEsp->execute();
$especialidades = $stmtEsp->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Gestión de Mecánicos por Especialidad';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-user-cog text-primary me-2"></i> Equipo Técnico y Mecánicos
        </h2>
        <p class="text-muted small mb-0">Gestión de especialistas automotrices, carga laboral y asignación de órdenes.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/modules/mecanicos/crear.php" class="btn btn-motor-accent shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Mecánico
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o DNI..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="especialidad" class="form-select">
                    <option value="">-- Filtrar por Especialidad --</option>
                    <?php foreach ($especialidades as $esp): ?>
                        <option value="<?= htmlspecialchars($esp) ?>" <?= ($especialidad === $esp) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($esp) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-motor-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                <?php if (!empty($search) || !empty($especialidad)): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <?php if (empty($mecanicos)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                <i class="fas fa-users-slash fs-1 text-secondary mb-3 d-block"></i>
                No se encontraron mecánicos con los filtros indicados.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($mecanicos as $m): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden">
                    <div class="p-3 text-center border-bottom bg-light">
                        <div class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-2 shadow-sm" style="width: 60px; height: 60px; font-size: 1.5rem;">
                            <i class="fas fa-wrench"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($m['nombre_completo']) ?></h6>
                        <small class="badge bg-secondary font-monospace mt-1">DNI: <?= htmlspecialchars($m['dni']) ?></small>
                    </div>

                    <div class="card-body p-3">
                        <div class="mb-3">
                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size:0.7rem;">Especialidad:</small>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle w-100 py-1 text-wrap text-start">
                                <i class="fas fa-award me-1"></i> <?= htmlspecialchars($m['especialidad']) ?>
                            </span>
                        </div>

                        <div class="small mb-3">
                            <div class="text-muted mb-1"><i class="fas fa-phone text-success me-1"></i> <?= htmlspecialchars($m['telefono'] ?? '-') ?></div>
                            <div class="text-muted"><i class="fas fa-envelope text-info me-1"></i> <?= htmlspecialchars($m['email'] ?? '-') ?></div>
                        </div>

                        <div class="p-2 bg-light rounded-3 d-flex justify-content-between text-center mb-3">
                            <div>
                                <small class="text-muted d-block" style="font-size:0.75rem;">Órdenes Activas</small>
                                <strong class="text-danger fs-6"><?= $m['ordenes_activas'] ?></strong>
                            </div>
                            <div class="border-start ps-3">
                                <small class="text-muted d-block" style="font-size:0.75rem;">Total Atendidas</small>
                                <strong class="text-dark fs-6"><?= $m['total_ordenes'] ?></strong>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge <?= ($m['estado'] === 'Activo') ? 'bg-success' : 'bg-secondary' ?> px-2 py-1">
                                <?= htmlspecialchars($m['estado']) ?>
                            </span>
                            <div class="btn-group btn-group-sm">
                                <a href="editar.php?id=<?= $m['id'] ?>" class="btn btn-outline-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="eliminar.php?id=<?= $m['id'] ?>" class="btn btn-outline-danger" title="Eliminar" onclick="return confirmDelete('¿Está seguro de eliminar a este mecánico?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
