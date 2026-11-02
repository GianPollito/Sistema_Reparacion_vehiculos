<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$gravedad = trim($_GET['gravedad'] ?? '');
$search = trim($_GET['search'] ?? '');

$query = "SELECT d.*, v.placa, v.marca, v.modelo, 
    m.nombre_completo as mecanico_nombre, m.especialidad as mecanico_especialidad,
    ot.numero_orden
    FROM diagnosticos d
    JOIN vehiculos v ON d.vehiculo_id = v.id
    JOIN mecanicos m ON d.mecanico_id = m.id
    LEFT JOIN ordenes_trabajo ot ON d.orden_id = ot.id
    WHERE 1=1";
$params = [];

if (!empty($gravedad)) {
    $query .= " AND d.gravedad = ?";
    $params[] = $gravedad;
}
if (!empty($search)) {
    $query .= " AND (v.placa LIKE ? OR d.descripcion_falla LIKE ? OR d.diagnostico_tecnico LIKE ? OR m.nombre_completo LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$query .= " ORDER BY d.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$diagnosticos = $stmt->fetchAll();

$pageTitle = 'Diagnósticos de Fallas Mecánicas';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-stethoscope text-danger me-2"></i> Diagnósticos de Fallas Mecánicas
        </h2>
        <p class="text-muted small mb-0">Informes técnicos de inspección, escaneo computarizado, gravedad y recomendaciones.</p>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/modules/diagnosticos/crear.php" class="btn btn-motor-accent shadow-sm">
            <i class="fas fa-plus me-1"></i> Registrar Diagnóstico
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="" class="row g-2">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Buscar por placa, falla, causa o mecánico..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="gravedad" class="form-select">
                    <option value="">-- Todas las Gravedades --</option>
                    <option value="Baja" <?= ($gravedad === 'Baja') ? 'selected' : '' ?>>Baja</option>
                    <option value="Media" <?= ($gravedad === 'Media') ? 'selected' : '' ?>>Media</option>
                    <option value="Alta" <?= ($gravedad === 'Alta') ? 'selected' : '' ?>>Alta</option>
                    <option value="Crítica" <?= ($gravedad === 'Crítica') ? 'selected' : '' ?>>Crítica</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-motor-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i> Filtrar
                </button>
                <?php if (!empty($search) || !empty($gravedad)): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Limpiar filtro">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <?php if (empty($diagnosticos)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                <i class="fas fa-check-double fs-1 text-success mb-2 d-block"></i>
                No se encontraron diagnósticos registrados con los filtros seleccionados.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($diagnosticos as $d): ?>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-warning text-dark font-monospace fs-6 me-2">
                                <?= htmlspecialchars($d['placa']) ?>
                            </span>
                            <strong><?= htmlspecialchars($d['marca'] . ' ' . $d['modelo']) ?></strong>
                        </div>
                        <div>
                            <?= statusBadge($d['gravedad']) ?>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="mb-3">
                            <span class="text-uppercase fw-bold text-danger small d-block mb-1">
                                <i class="fas fa-exclamation-triangle me-1"></i> Falla Detectada:
                            </span>
                            <h6 class="fw-bold text-dark"><?= htmlspecialchars($d['descripcion_falla']) ?></h6>
                        </div>

                        <div class="mb-3">
                            <span class="text-uppercase fw-bold text-primary small d-block mb-1">
                                <i class="fas fa-microscope me-1"></i> Diagnóstico Técnico Detallado:
                            </span>
                            <p class="small text-secondary bg-light p-3 rounded mb-0">
                                <?= nl2br(htmlspecialchars($d['diagnostico_tecnico'])) ?>
                            </p>
                        </div>

                        <?php if (!empty($d['recomendaciones'])): ?>
                            <div class="mb-3">
                                <span class="text-uppercase fw-bold text-success small d-block mb-1">
                                    <i class="fas fa-check-circle me-1"></i> Recomendaciones / Solución:
                                </span>
                                <p class="small text-muted mb-0">
                                    <?= nl2br(htmlspecialchars($d['recomendaciones'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top small text-muted">
                            <div>
                                <i class="fas fa-user-cog text-primary me-1"></i> <?= htmlspecialchars($d['mecanico_nombre']) ?>
                                <?php if (!empty($d['numero_orden'])): ?>
                                    &bull; <a href="<?= BASE_URL ?>/modules/ordenes/ver.php?id=<?= $d['orden_id'] ?>" class="text-primary fw-semibold"><?= htmlspecialchars($d['numero_orden']) ?></a>
                                <?php endif; ?>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <a href="editar.php?id=<?= $d['id'] ?>" class="btn btn-outline-primary" title="Editar Diagnóstico">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="eliminar.php?id=<?= $d['id'] ?>" class="btn btn-outline-danger" title="Eliminar" onclick="return confirmDelete('¿Desea eliminar este diagnóstico técnico?');">
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
