<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/modules/ordenes/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM ordenes_trabajo WHERE id = ?");
$stmt->execute([$id]);
$orden = $stmt->fetch();

if (!$orden) {
    setFlash('danger', 'Orden de trabajo no encontrada.');
    header('Location: ' . BASE_URL . '/modules/ordenes/index.php');
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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_orden = trim($_POST['numero_orden'] ?? '');
    $vehiculo_id = intval($_POST['vehiculo_id'] ?? 0);
    $mecanico_id = !empty($_POST['mecanico_id']) ? intval($_POST['mecanico_id']) : null;
    $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? '');
    $fecha_estimada_entrega = !empty($_POST['fecha_estimada_entrega']) ? trim($_POST['fecha_estimada_entrega']) : null;
    $kilometraje_ingreso = intval($_POST['kilometraje_ingreso'] ?? 0);
    $nivel_combustible = trim($_POST['nivel_combustible'] ?? '1/2');
    $estado = trim($_POST['estado'] ?? 'En Proceso');
    $problema_reportado = trim($_POST['problema_reportado'] ?? '');
    $notas_tecnicas = trim($_POST['notas_tecnicas'] ?? '');

    if ($vehiculo_id <= 0 || empty($numero_orden) || empty($problema_reportado)) {
        $error = 'Por favor complete todos los datos obligatorios.';
    } else {
        try {
            $stmtCli = $pdo->prepare("SELECT cliente_id FROM vehiculos WHERE id = ?");
            $stmtCli->execute([$vehiculo_id]);
            $cliente_id = $stmtCli->fetchColumn();

            $updateStmt = $pdo->prepare("UPDATE ordenes_trabajo SET 
                numero_orden = ?, vehiculo_id = ?, cliente_id = ?, mecanico_id = ?, 
                fecha_ingreso = ?, fecha_estimada_entrega = ?, kilometraje_ingreso = ?, 
                nivel_combustible = ?, estado = ?, problema_reportado = ?, notas_tecnicas = ? 
                WHERE id = ?");
            $updateStmt->execute([
                $numero_orden, $vehiculo_id, $cliente_id, $mecanico_id, 
                $fecha_ingreso, $fecha_estimada_entrega, $kilometraje_ingreso, 
                $nivel_combustible, $estado, $problema_reportado, $notas_tecnicas, $id
            ]);

            setFlash('success', 'Orden de trabajo actualizada correctamente.');
            header("Location: " . BASE_URL . "/modules/ordenes/ver.php?id=$id");
            exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar orden: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Editar Orden: ' . $orden['numero_orden'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-edit text-primary me-2"></i> Editar Orden: <?= htmlspecialchars($orden['numero_orden']) ?>
                    </h5>
                    <a href="ver.php?id=<?= $orden['id'] ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver a la Orden
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
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Número de Orden *</label>
                            <input type="text" name="numero_orden" class="form-control font-monospace fw-bold" required value="<?= htmlspecialchars($orden['numero_orden']) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Vehículo *</label>
                            <select name="vehiculo_id" class="form-select" required>
                                <?php foreach ($vehiculos as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= ($orden['vehiculo_id'] == $v['id']) ? 'selected' : '' ?>>
                                        [<?= htmlspecialchars($v['placa']) ?>] <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?> — <?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mecánico Asignado</label>
                            <select name="mecanico_id" class="form-select">
                                <option value="">-- Sin asignar --</option>
                                <?php foreach ($mecanicos as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= ($orden['mecanico_id'] == $m['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['nombre_completo']) ?> (<?= htmlspecialchars($m['especialidad']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado de la Orden *</label>
                            <select name="estado" class="form-select">
                                <option value="Pendiente" <?= ($orden['estado'] === 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                                <option value="En Diagnóstico" <?= ($orden['estado'] === 'En Diagnóstico') ? 'selected' : '' ?>>En Diagnóstico</option>
                                <option value="En Proceso" <?= ($orden['estado'] === 'En Proceso') ? 'selected' : '' ?>>En Proceso</option>
                                <option value="Esperando Repuestos" <?= ($orden['estado'] === 'Esperando Repuestos') ? 'selected' : '' ?>>Esperando Repuestos</option>
                                <option value="Finalizada" <?= ($orden['estado'] === 'Finalizada') ? 'selected' : '' ?>>Finalizada</option>
                                <option value="Entregada" <?= ($orden['estado'] === 'Entregada') ? 'selected' : '' ?>>Entregada</option>
                                <option value="Cancelada" <?= ($orden['estado'] === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha de Ingreso *</label>
                            <input type="datetime-local" name="fecha_ingreso" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime($orden['fecha_ingreso'])) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Estimada de Entrega</label>
                            <input type="datetime-local" name="fecha_estimada_entrega" class="form-control" value="<?= !empty($orden['fecha_estimada_entrega']) ? date('Y-m-d\TH:i', strtotime($orden['fecha_estimada_entrega'])) : '' ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kilometraje de Ingreso (km)</label>
                            <input type="number" name="kilometraje_ingreso" class="form-control" value="<?= htmlspecialchars($orden['kilometraje_ingreso'] ?? 0) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nivel de Combustible</label>
                            <select name="nivel_combustible" class="form-select">
                                <option value="Reserva" <?= ($orden['nivel_combustible'] === 'Reserva') ? 'selected' : '' ?>>Reserva</option>
                                <option value="1/4" <?= ($orden['nivel_combustible'] === '1/4') ? 'selected' : '' ?>>1/4</option>
                                <option value="1/2" <?= ($orden['nivel_combustible'] === '1/2') ? 'selected' : '' ?>>1/2</option>
                                <option value="3/4" <?= ($orden['nivel_combustible'] === '3/4') ? 'selected' : '' ?>>3/4</option>
                                <option value="Full" <?= ($orden['nivel_combustible'] === 'Full') ? 'selected' : '' ?>>Full</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Problema Reportado *</label>
                            <textarea name="problema_reportado" rows="3" class="form-control" required><?= htmlspecialchars($orden['problema_reportado']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notas Técnicas</label>
                            <textarea name="notas_tecnicas" rows="2" class="form-control"><?= htmlspecialchars($orden['notas_tecnicas'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="ver.php?id=<?= $orden['id'] ?>" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-primary px-4 shadow-sm">
                            <i class="fas fa-sync-alt me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
