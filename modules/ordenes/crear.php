<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$selectedVehiculoId = intval($_GET['vehiculo_id'] ?? 0);
$citaId = intval($_GET['cita_id'] ?? 0);

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM ordenes_trabajo");
$stmtCount->execute();
$count = $stmtCount->fetchColumn() + 1;
$nuevoNumero = 'OT-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

$stmtVeh = $pdo->prepare("SELECT v.id, v.placa, v.marca, v.modelo, v.kilometraje, v.cliente_id, c.nombres, c.apellidos
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
    $numero_orden = trim($_POST['numero_orden'] ?? $nuevoNumero);
    $vehiculo_id = intval($_POST['vehiculo_id'] ?? 0);
    $mecanico_id = !empty($_POST['mecanico_id']) ? intval($_POST['mecanico_id']) : null;
    $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? date('Y-m-d H:i:s'));
    $fecha_estimada_entrega = !empty($_POST['fecha_estimada_entrega']) ? trim($_POST['fecha_estimada_entrega']) : null;
    $kilometraje_ingreso = intval($_POST['kilometraje_ingreso'] ?? 0);
    $nivel_combustible = trim($_POST['nivel_combustible'] ?? '1/2');
    $estado = trim($_POST['estado'] ?? 'En Proceso');
    $problema_reportado = trim($_POST['problema_reportado'] ?? '');
    $notas_tecnicas = trim($_POST['notas_tecnicas'] ?? '');

    if ($vehiculo_id <= 0 || empty($numero_orden) || empty($problema_reportado)) {
        $error = 'Por favor seleccione el vehículo e ingrese el problema reportado.';
    } else {
        try {
            $stmtCli = $pdo->prepare("SELECT cliente_id FROM vehiculos WHERE id = ?");
            $stmtCli->execute([$vehiculo_id]);
            $cliente_id = $stmtCli->fetchColumn();

            $stmtInsert = $pdo->prepare("INSERT INTO ordenes_trabajo 
                (numero_orden, vehiculo_id, cliente_id, mecanico_id, fecha_ingreso, fecha_estimada_entrega, kilometraje_ingreso, nivel_combustible, estado, problema_reportado, notas_tecnicas) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([
                $numero_orden, $vehiculo_id, $cliente_id, $mecanico_id, 
                $fecha_ingreso, $fecha_estimada_entrega, $kilometraje_ingreso, 
                $nivel_combustible, $estado, $problema_reportado, $notas_tecnicas
            ]);
            $ordenId = $pdo->lastInsertId();

            $pdo->prepare("UPDATE vehiculos SET kilometraje = MAX(kilometraje, ?) WHERE id = ?")->execute([$kilometraje_ingreso, $vehiculo_id]);

            if ($citaId > 0) {
                $pdo->prepare("UPDATE citas SET estado = 'En Atención' WHERE id = ?")->execute([$citaId]);
            }

            setFlash('success', "Orden de trabajo $numero_orden generada exitosamente. Ahora puedes agregar repuestos y servicios.");
            header('Location: ' . BASE_URL . "/modules/ordenes/ver.php?id=$ordenId");
            exit;
        } catch (PDOException $e) {
            $error = 'Error al generar orden: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Generar Orden de Trabajo';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-file-invoice text-primary me-2"></i> Generar Orden de Trabajo
                    </h5>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Órdenes
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
                            <input type="text" name="numero_orden" class="form-control font-monospace fw-bold text-primary" required value="<?= htmlspecialchars($_POST['numero_orden'] ?? $nuevoNumero) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Vehículo a Ingresar *</label>
                            <select name="vehiculo_id" class="form-select" required>
                                <option value="">-- Seleccione Vehículo (Placa - Modelo - Propietario) --</option>
                                <?php foreach ($vehiculos as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= ($selectedVehiculoId == $v['id'] || (isset($_POST['vehiculo_id']) && $_POST['vehiculo_id'] == $v['id'])) ? 'selected' : '' ?>>
                                        [<?= htmlspecialchars($v['placa']) ?>] <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?> — Propietario: <?= htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mecánico Asignado</label>
                            <select name="mecanico_id" class="form-select">
                                <option value="">-- Asignar más adelante / Sin asignar --</option>
                                <?php foreach ($mecanicos as $m): ?>
                                    <option value="<?= $m['id'] ?>" <?= ((isset($_POST['mecanico_id']) && $_POST['mecanico_id'] == $m['id']) ? 'selected' : '') ?>>
                                        <?= htmlspecialchars($m['nombre_completo']) ?> (<?= htmlspecialchars($m['especialidad']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Estado Inicial de la Orden *</label>
                            <select name="estado" class="form-select">
                                <option value="Pendiente">Pendiente de Revisión</option>
                                <option value="En Diagnóstico">En Diagnóstico</option>
                                <option value="En Proceso" selected>En Proceso de Reparación</option>
                                <option value="Esperando Repuestos">Esperando Repuestos</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha y Hora de Ingreso *</label>
                            <input type="datetime-local" name="fecha_ingreso" class="form-control" required value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Estimada de Entrega</label>
                            <input type="datetime-local" name="fecha_estimada_entrega" class="form-control" value="<?= date('Y-m-d\T18:00', strtotime('+1 day')) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kilometraje de Ingreso (km)</label>
                            <input type="number" name="kilometraje_ingreso" class="form-control" placeholder="Ej. 45200" value="<?= htmlspecialchars($_POST['kilometraje_ingreso'] ?? '40000') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nivel de Combustible</label>
                            <select name="nivel_combustible" class="form-select">
                                <option value="Reserva">Reserva</option>
                                <option value="1/4">1/4 de Tanque</option>
                                <option value="1/2" selected>1/2 de Tanque</option>
                                <option value="3/4">3/4 de Tanque</option>
                                <option value="Full">Full (Lleno)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Falla / Problema Reportado por el Cliente *</label>
                            <textarea name="problema_reportado" rows="3" class="form-control" placeholder="Detalle los síntomas, ruidos, alertas en tablero o solicitudes expresas del cliente..." required><?= htmlspecialchars($_POST['problema_reportado'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notas Técnicas Iniciales (Opcional)</label>
                            <textarea name="notas_tecnicas" rows="2" class="form-control" placeholder="Observaciones preliminares del jefe de taller o mecánico..."><?= htmlspecialchars($_POST['notas_tecnicas'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-accent px-4 shadow-sm">
                            <i class="fas fa-file-invoice me-1"></i> Crear Orden de Trabajo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
