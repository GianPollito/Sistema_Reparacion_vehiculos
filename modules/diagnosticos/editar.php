<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/modules/diagnosticos/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM diagnosticos WHERE id = ?");
$stmt->execute([$id]);
$diagnostico = $stmt->fetch();

if (!$diagnostico) {
    setFlash('danger', 'Diagnóstico no encontrado.');
    header('Location: ' . BASE_URL . '/modules/diagnosticos/index.php');
    exit;
}

$stmtVeh = $pdo->prepare("SELECT v.id, v.placa, v.marca, v.modelo, c.nombres, c.apellidos
    FROM vehiculos v
    JOIN clientes c ON v.cliente_id = c.id
    ORDER BY v.placa ASC");
$stmtVeh->execute();
$vehiculos = $stmtVeh->fetchAll();

$stmtMec = $pdo->prepare("SELECT id, nombre_completo, especialidad FROM mecanicos WHERE estado = 'Activo' ORDER BY nombre_completo ASC");
$stmtMec->execute();
$mecanicos = $stmtMec->fetchAll();

$stmtOrd = $pdo->prepare("SELECT id, numero_orden, estado FROM ordenes_trabajo ORDER BY id DESC LIMIT 30");
$stmtOrd->execute();
$ordenes = $stmtOrd->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehiculo_id = intval($_POST['vehiculo_id'] ?? 0);
    $mecanico_id = intval($_POST['mecanico_id'] ?? 0);
    $orden_id = !empty($_POST['orden_id']) ? intval($_POST['orden_id']) : null;
    $descripcion_falla = trim($_POST['descripcion_falla'] ?? '');
    $diagnostico_tecnico = trim($_POST['diagnostico_tecnico'] ?? '');
    $gravedad = trim($_POST['gravedad'] ?? 'Media');
    $recomendaciones = trim($_POST['recomendaciones'] ?? '');

    if ($vehiculo_id <= 0 || $mecanico_id <= 0 || empty($descripcion_falla) || empty($diagnostico_tecnico)) {
        $error = 'Por favor complete todos los campos obligatorios.';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE diagnosticos SET orden_id = ?, vehiculo_id = ?, mecanico_id = ?, descripcion_falla = ?, diagnostico_tecnico = ?, gravedad = ?, recomendaciones = ? WHERE id = ?");
            $updateStmt->execute([$orden_id, $vehiculo_id, $mecanico_id, $descripcion_falla, $diagnostico_tecnico, $gravedad, $recomendaciones, $id]);

            setFlash('success', 'Diagnóstico técnico actualizado exitosamente.');
            header('Location: ' . BASE_URL . '/modules/diagnosticos/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar diagnóstico: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Editar Diagnóstico';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-edit text-primary me-2"></i> Editar Diagnóstico Técnico
                    </h5>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Vehículo Evaluado *</label>
                            <select name="vehiculo_id" class="form-select" required>
                                <?php foreach ($vehiculos as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= ($diagnostico['vehiculo_id'] == $v['id']) ? 'selected' : '' ?>>
                                        [<?= htmlspecialchars($v['placa']) ?>] <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?> - <?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Mecánico Evaluador *</label>
                            <select name="mecanico_id" class="form-select" required>
                                <?php foreach ($mecanicos as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= ($diagnostico['mecanico_id'] == $m['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Orden Asociada</label>
                            <select name="orden_id" class="form-select">
                                <option value="">-- Sin orden directa --</option>
                                <?php foreach ($ordenes as $o): ?>
                                    <option value="<?= $o['id'] ?>" <?= ($diagnostico['orden_id'] == $o['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($o['numero_orden']) ?> (<?= htmlspecialchars($o['estado']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nivel de Gravedad *</label>
                            <select name="gravedad" class="form-select">
                                <option value="Baja" <?= ($diagnostico['gravedad'] === 'Baja') ? 'selected' : '' ?>>Baja</option>
                                <option value="Media" <?= ($diagnostico['gravedad'] === 'Media') ? 'selected' : '' ?>>Media</option>
                                <option value="Alta" <?= ($diagnostico['gravedad'] === 'Alta') ? 'selected' : '' ?>>Alta</option>
                                <option value="Crítica" <?= ($diagnostico['gravedad'] === 'Crítica') ? 'selected' : '' ?>>Crítica</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Falla / Síntoma *</label>
                            <input type="text" name="descripcion_falla" class="form-control" required value="<?= htmlspecialchars($diagnostico['descripcion_falla']) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Diagnóstico Técnico Detallado *</label>
                            <textarea name="diagnostico_tecnico" rows="3" class="form-control" required><?= htmlspecialchars($diagnostico['diagnostico_tecnico']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Recomendaciones y Plan de Acción</label>
                            <textarea name="recomendaciones" rows="2" class="form-control"><?= htmlspecialchars($diagnostico['recomendaciones'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-primary px-4 shadow-sm">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Diagnóstico
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
