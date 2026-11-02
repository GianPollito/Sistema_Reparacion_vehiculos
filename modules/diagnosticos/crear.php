<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$selectedVehiculoId = intval($_GET['vehiculo_id'] ?? 0);
$selectedOrdenId = intval($_GET['orden_id'] ?? 0);

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
            $stmt = $pdo->prepare("INSERT INTO diagnosticos (orden_id, vehiculo_id, mecanico_id, descripcion_falla, diagnostico_tecnico, gravedad, recomendaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$orden_id, $vehiculo_id, $mecanico_id, $descripcion_falla, $diagnostico_tecnico, $gravedad, $recomendaciones]);

            setFlash('success', 'Diagnóstico técnico registrado exitosamente.');
            if ($orden_id > 0) {
                header("Location: " . BASE_URL . "/modules/ordenes/ver.php?id=$orden_id");
            } else {
                header("Location: " . BASE_URL . "/modules/diagnosticos/index.php");
            }
            exit;
        } catch (PDOException $e) {
            $error = 'Error al registrar diagnóstico: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Registrar Diagnóstico Mecánico';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-stethoscope text-danger me-2"></i> Registrar Diagnóstico de Falla Mecánica
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
                                <option value="">-- Seleccionar Vehículo (Placa) --</option>
                                <?php foreach ($vehiculos as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= ($selectedVehiculoId == $v['id'] || (isset($_POST['vehiculo_id']) && $_POST['vehiculo_id'] == $v['id'])) ? 'selected' : '' ?>>
                                        [<?= htmlspecialchars($v['placa']) ?>] <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?> - Cliente: <?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Mecánico Especialista *</label>
                            <select name="mecanico_id" class="form-select" required>
                                <option value="">-- Seleccionar Mecánico --</option>
                                <?php foreach ($mecanicos as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= ((isset($_POST['mecanico_id']) && $_POST['mecanico_id'] == $m['id']) ? 'selected' : '') ?>>
                                        <?= htmlspecialchars($m['nombre_completo']) ?> (<?= htmlspecialchars($m['especialidad']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Orden de Trabajo Asociada (Opcional)</label>
                            <select name="orden_id" class="form-select">
                                <option value="">-- Sin orden directa / Evaluación previa --</option>
                                <?php foreach ($ordenes as $o): ?>
                                    <option value="<?= $o['id'] ?>" <?= ($selectedOrdenId == $o['id'] || (isset($_POST['orden_id']) && $_POST['orden_id'] == $o['id'])) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($o['numero_orden']) ?> (<?= htmlspecialchars($o['estado']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nivel de Gravedad / Criticidad *</label>
                            <select name="gravedad" class="form-select">
                                <option value="Baja">Baja (Mantenimiento preventivo sugerido)</option>
                                <option value="Media" selected>Media (Falla perceptible, desgaste normal)</option>
                                <option value="Alta">Alta (Riesgo mecánico o de seguridad)</option>
                                <option value="Crítica">Crítica (Inmovilización inmediata del vehículo)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción de la Falla / Síntoma Observado *</label>
                            <input type="text" name="descripcion_falla" class="form-control" placeholder="Ej. Código DTC P0300 - Falla de encendido en cilindro 2 y 3" required value="<?= htmlspecialchars($_POST['descripcion_falla'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Diagnóstico Técnico Detallado (Causa Raíz) *</label>
                            <textarea name="diagnostico_tecnico" rows="3" class="form-control" placeholder="Explique las pruebas realizadas (presión de compresión, osciloscopio, inspección visual) y la causa técnica de la avería..." required><?= htmlspecialchars($_POST['diagnostico_tecnico'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Recomendaciones y Plan de Acción</label>
                            <textarea name="recomendaciones" rows="2" class="form-control" placeholder="Repuestos a reemplazar, servicios a ejecutar o advertencias al cliente..."><?= htmlspecialchars($_POST['recomendaciones'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-accent px-4 shadow-sm">
                            <i class="fas fa-save me-1"></i> Guardar Diagnóstico
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
