<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$categoria = trim($_GET['categoria'] ?? '');
$search = trim($_GET['search'] ?? '');
$bajoStock = isset($_GET['bajo_stock']);

$query = "SELECT r.*,
    COALESCE((SELECT SUM(ru.cantidad) FROM repuestos_utilizados ru WHERE ru.repuesto_id = r.id), 0) as total_utilizado,
    COALESCE((SELECT SUM(ru.subtotal) FROM repuestos_utilizados ru WHERE ru.repuesto_id = r.id), 0) as total_facturado
    FROM repuestos r WHERE 1=1";
$params = [];

if (!empty($categoria)) {
    $query .= " AND r.categoria = ?";
    $params[] = $categoria;
}
if (!empty($search)) {
    $query .= " AND (r.nombre LIKE ? OR r.codigo LIKE ? OR r.marca LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}
if ($bajoStock) {
    $query .= " AND r.stock <= r.stock_minimo";
}

$query .= " ORDER BY r.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$repuestos = $stmt->fetchAll();

$stmtCat = $pdo->prepare("SELECT DISTINCT categoria FROM repuestos WHERE categoria IS NOT NULL");
$stmtCat->execute();
$categorias = $stmtCat->fetchAll(PDO::FETCH_COLUMN);

$stmtLow = $pdo->prepare("SELECT COUNT(*) FROM repuestos WHERE stock <= stock_minimo");
$stmtLow->execute();
$totalBajoStock = $stmtLow->fetchColumn();

$pageTitle = 'Inventario de Repuestos';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-boxes text-primary me-2"></i> Control de Repuestos e Insumos
        </h2>
        <p class="text-muted small mb-0">Control de repuestos utilizados en las reparaciones, stock disponible y precios unitarios.</p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($totalBajoStock > 0): ?>
            <a href="index.php?bajo_stock=1" class="btn btn-warning position-relative shadow-sm">
                <i class="fas fa-exclamation-triangle me-1"></i> Stock Bajo
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= $totalBajoStock ?>
                </span>
            </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/modules/repuestos/crear.php" class="btn btn-motor-accent shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Repuesto
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por código, nombre o marca..." value="<?= htmlspecialchars($search) ?>">
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
                <?php if (!empty($search) || !empty($categoria) || $bajoStock): ?>
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
                    <th>Repuesto / Pieza</th>
                    <th>Marca</th>
                    <th>Categoría</th>
                    <th>Stock Actual</th>
                    <th class="text-center">Utilizados en OT</th>
                    <th class="text-end">Precio Unitario</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($repuestos)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fs-1 text-secondary mb-2 d-block"></i>
                            No se encontraron repuestos registrados con los filtros seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($repuestos as $r): ?>
                        <tr>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($r['codigo']) ?></span>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($r['nombre']) ?></strong>
                                <?php if ($r['stock'] <= $r['stock_minimo']): ?>
                                    <span class="badge bg-danger text-white py-1 px-2" style="font-size:0.7rem;">
                                        <i class="fas fa-exclamation-circle me-1"></i> Stock Crítico (Mín: <?= $r['stock_minimo'] ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="text-secondary"><?= htmlspecialchars($r['marca'] ?? 'Genérico') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    <?= htmlspecialchars($r['categoria']) ?>
                                </span>
                            </td>
                            <td>
                                <strong class="fs-6 <?= ($r['stock'] <= $r['stock_minimo']) ? 'text-danger' : 'text-success' ?>">
                                    <?= $r['stock'] ?> unid.
                                </strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-dark-subtle text-dark border px-2 py-1">
                                    <i class="fas fa-wrench me-1"></i> <?= $r['total_utilizado'] ?> u. (<?= formatCurrency($r['total_facturado']) ?>)
                                </span>
                            </td>
                            <td class="text-end fw-bold text-primary fs-6">
                                <?= formatCurrency($r['precio_unitario']) ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="editar.php?id=<?= $r['id'] ?>" class="btn btn-outline-primary" title="Editar Repuesto">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="eliminar.php?id=<?= $r['id'] ?>" class="btn btn-outline-danger" title="Eliminar" onclick="return confirmDelete('¿Está seguro de eliminar este repuesto?');">
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
