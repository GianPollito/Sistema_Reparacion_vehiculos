<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$categoria = trim($_GET['categoria'] ?? '');
$search = trim($_GET['search'] ?? '');

$query = "SELECT s.*, 
    (SELECT COUNT(*) FROM orden_servicios os WHERE os.servicio_id = s.id) as veces_realizado
    FROM servicios s WHERE 1=1";
$params = [];

if (!empty($categoria)) {
    $query .= " AND s.categoria = ?";
    $params[] = $categoria;
}
if (!empty($search)) {
    $query .= " AND (s.nombre LIKE ? OR s.codigo LIKE ? OR s.descripcion LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}
$query .= " ORDER BY s.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$servicios = $stmt->fetchAll();

$stmtCat = $pdo->prepare("SELECT DISTINCT categoria FROM servicios WHERE categoria IS NOT NULL");
$stmtCat->execute();
$categorias = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Servicios de Reparación';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-wrench text-primary me-2"></i> Catálogo de Servicios de Reparación
        </h2>
        <p class="text-muted small mb-0">Tarifario de mano de obra técnica, mantenimientos preventivos y correctivos.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/modules/servicios/crear.php" class="btn btn-motor-accent shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Servicio
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nombre de servicio o código..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="categoria" class="form-select">
                    <option value="">-- Todas las Categorías --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($categoria === $cat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-motor-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                <?php if (!empty($search) || !empty($categoria)): ?>
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
                    <th>Servicio</th>
                    <th>Categoría</th>
                    <th>Tiempo Estimado</th>
                    <th>Veces Realizado</th>
                    <th class="text-end">Tarifa / Precio</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($servicios)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-tools fs-1 text-secondary mb-2 d-block"></i>
                            No hay servicios registrados con los criterios seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($servicios as $s): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($s['codigo']) ?></span>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($s['nombre']) ?></strong>
                                <small class="text-muted d-block" style="max-width: 320px;"><?= htmlspecialchars($s['descripcion'] ?? '-') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <i class="fas fa-tag me-1"></i> <?= htmlspecialchars($s['categoria']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="small text-secondary"><i class="far fa-clock me-1 text-warning"></i> <?= htmlspecialchars($s['tiempo_estimado'] ?? '-') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill px-2 py-1">
                                    <?= $s['veces_realizado'] ?> veces
                                </span>
                            </td>
                            <td class="text-end fw-bold text-success fs-6">
                                <?= formatCurrency($s['precio']) ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="editar.php?id=<?= $s['id'] ?>" class="btn btn-outline-primary" title="Editar Servicio">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $s['id'] ?>" class="btn btn-outline-danger" title="Eliminar" onclick="return confirmDelete('¿Está seguro de eliminar este servicio del catálogo?');">
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
