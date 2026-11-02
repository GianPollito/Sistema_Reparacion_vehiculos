<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$marca = $_GET['marca'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT v.*, c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, c.telefono as cliente_telefono,
    COUNT(ot.id) as total_visitas,
    COALESCE(SUM(ot.total_general), 0) as total_gastado,
    MAX(ot.fecha_ingreso) as ultima_visita
    FROM vehiculos v
    JOIN clientes c ON v.cliente_id = c.id
    LEFT JOIN ordenes_trabajo ot ON ot.vehiculo_id = v.id
    WHERE 1=1";
$params = [];

if (!empty($marca)) {
    $query .= " AND v.marca = ?";
    $params[] = $marca;
}
if (!empty($search)) {
    $query .= " AND (v.placa LIKE ? OR v.modelo LIKE ? OR c.nombres LIKE ? OR c.apellidos LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$query .= " GROUP BY v.id ORDER BY total_visitas DESC, total_gastado DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$vehiculos = $stmt->fetchAll();

$marcas = $pdo->query("SELECT DISTINCT marca FROM vehiculos ORDER BY marca ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Reporte de Vehículos Atendidos';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-car-side text-info me-2"></i> Reporte: Vehículos Atendidos
        </h2>
        <p class="text-muted small mb-0">Frecuencia de visitas por vehículo, historial de mantenimiento e inversión realizada.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="exportar.php?tipo=vehiculos" class="btn btn-outline-success btn-sm">
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
                <label class="form-label small fw-bold text-muted mb-1">Buscar:</label>
                <input type="text" name="search" class="form-control" placeholder="Placa, modelo o propietario..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted mb-1">Filtrar por Marca:</label>
                <select name="marca" class="form-select">
                    <option value="">-- Todas las Marcas --</option>
                    <?php foreach ($marcas as $m): ?>
                        <option value="<?= htmlspecialchars($m) ?>" <?= ($marca === $m) ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-motor-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filtrar Vehículos
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
                    <th>Placa</th>
                    <th>Marca &amp; Modelo</th>
                    <th>Año</th>
                    <th>Propietario / Contacto</th>
                    <th>Kilometraje</th>
                    <th class="text-center">Total Visitas</th>
                    <th>Última Atención</th>
                    <th class="text-end">Inversión en Reparaciones</th>
                    <th class="text-end">Historial</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vehiculos)): ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted">No se encontraron vehículos atendidos.</td></tr>
                <?php else: ?>
                    <?php foreach ($vehiculos as $v): ?>
                        <tr>
                            <td>
                                <span class="badge bg-warning text-dark font-monospace fs-6 px-3 py-1">
                                    <?= htmlspecialchars($v['placa']) ?>
                                </span>
                            </td>
                            <td>
                                <strong class="text-dark d-block"><?= htmlspecialchars($v['marca']) ?></strong>
                                <span class="text-muted small"><?= htmlspecialchars($v['modelo']) ?> (<?= htmlspecialchars($v['color'] ?? '-') ?>)</span>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($v['anio']) ?></span></td>
                            <td>
                                <strong class="d-block text-dark"><?= htmlspecialchars($v['cliente_nombres'] . ' ' . $v['cliente_apellidos']) ?></strong>
                                <small class="text-muted"><i class="fas fa-phone text-success me-1"></i> <?= htmlspecialchars($v['cliente_telefono'] ?? '-') ?></small>
                            </td>
                            <td><small class="text-secondary"><?= number_format($v['kilometraje']) ?> km</small></td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill px-3 py-2">
                                    <i class="fas fa-wrench me-1"></i> <?= $v['total_visitas'] ?> veces
                                </span>
                            </td>
                            <td><small class="text-muted"><?= formatDate($v['ultima_visita']) ?></small></td>
                            <td class="text-end fw-bold text-success fs-6">
                                <?= formatCurrency($v['total_gastado']) ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/modules/vehiculos/ver.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye me-1"></i> Ficha Técnica
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
