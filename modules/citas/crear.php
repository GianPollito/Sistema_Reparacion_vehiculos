<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$stmtVeh = $pdo->prepare("SELECT v.id, v.placa, v.marca, v.modelo, v.cliente_id, c.nombres, c.apellidos
    FROM vehiculos v
    JOIN clientes c ON v.cliente_id = c.id
    ORDER BY c.nombres ASC");
$stmtVeh->execute();
$vehiculos = $stmtVeh->fetchAll();

$stmtCod = $pdo->prepare("SELECT COUNT(*) FROM citas");
$stmtCod->execute();
$count = $stmtCod->fetchColumn() + 1;
$nuevoCodigo = 'CIT-' . str_pad($count, 3, '0', STR_PAD_LEFT);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? $nuevoCodigo);
    $vehiculo_id = intval($_POST['vehiculo_id'] ?? 0);
    $fecha_cita = trim($_POST['fecha_cita'] ?? '');
    $hora_cita = trim($_POST['hora_cita'] ?? '');
    $motivo = trim($_POST['motivo'] ?? '');
    $estado = trim($_POST['estado'] ?? 'Pendiente');

    if ($vehiculo_id <= 0 || empty($fecha_cita) || empty($hora_cita) || empty($motivo)) {
        $error = 'Por favor complete todos los datos requeridos para la cita.';
    } else {
        try {
            $stmtCli = $pdo->prepare("SELECT cliente_id FROM vehiculos WHERE id = ?");
            $stmtCli->execute([$vehiculo_id]);
            $cliente_id = $stmtCli->fetchColumn();

            $insertStmt = $pdo->prepare("INSERT INTO citas (codigo, cliente_id, vehiculo_id, fecha_cita, hora_cita, motivo, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([$codigo, $cliente_id, $vehiculo_id, $fecha_cita, $hora_cita, $motivo, $estado]);

            setFlash('success', 'Cita ' . htmlspecialchars($codigo) . ' agendada correctamente.');
            header('Location: ' . BASE_URL . '/modules/citas/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al programar cita: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Programar Cita de Mantenimiento';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-calendar-plus text-primary me-2"></i> Programar Cita de Taller
                    </h5>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver a la Agenda
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
                            <label class="form-label fw-semibold">Código de Cita *</label>
                            <input type="text" name="codigo" class="form-control font-monospace fw-bold" required value="<?= htmlspecialchars($_POST['codigo'] ?? $nuevoCodigo) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Vehículo y Cliente *</label>
                            <select name="vehiculo_id" class="form-select" required>
                                <option value="">-- Seleccione Vehículo (Placa - Propietario) --</option>
                                <?php foreach ($vehiculos as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= ((isset($_POST['vehiculo_id']) && $_POST['vehiculo_id'] == $v['id']) ? 'selected' : '') ?>>
                                        [<?= htmlspecialchars($v['placa']) ?>] <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?> - Cliente: <?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha de la Cita *</label>
                            <input type="date" name="fecha_cita" class="form-control" required value="<?= htmlspecialchars($_POST['fecha_cita'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Hora de la Cita *</label>
                            <input type="time" name="hora_cita" class="form-control" required value="<?= htmlspecialchars($_POST['hora_cita'] ?? '09:00') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Estado Inicial *</label>
                            <select name="estado" class="form-select">
                                <option value="Pendiente" selected>Pendiente</option>
                                <option value="Confirmada">Confirmada</option>
                                <option value="En Atención">En Atención</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Motivo de Visita / Servicio Solicitado *</label>
                            <textarea name="motivo" rows="3" class="form-control" placeholder="Describa el motivo de la cita, síntomas del vehículo o mantenimiento requerido..." required><?= htmlspecialchars($_POST['motivo'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-accent px-4 shadow-sm">
                            <i class="fas fa-calendar-check me-1"></i> Agendar Cita
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
