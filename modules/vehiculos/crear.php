<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$selectedClienteId = intval($_GET['cliente_id'] ?? 0);

$stmtCli = $pdo->prepare("SELECT id, dni_ruc, nombres, apellidos FROM clientes ORDER BY nombres ASC");
$stmtCli->execute();
$clientes = $stmtCli->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = intval($_POST['cliente_id'] ?? 0);
    $placa = strtoupper(trim($_POST['placa'] ?? ''));
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $anio = intval($_POST['anio'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $kilometraje = intval($_POST['kilometraje'] ?? 0);
    $observaciones = trim($_POST['observaciones'] ?? '');

    if ($cliente_id <= 0 || empty($placa) || empty($marca) || empty($modelo) || $anio <= 1900) {
        $error = 'Por favor complete todos los datos obligatorios (Cliente, Placa, Marca, Modelo y Año).';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO vehiculos (cliente_id, placa, marca, modelo, anio, color, kilometraje, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cliente_id, $placa, $marca, $modelo, $anio, $color, $kilometraje, $observaciones]);

            setFlash('success', 'Vehículo con placa ' . htmlspecialchars($placa) . ' registrado exitosamente.');
            header('Location: ' . BASE_URL . '/modules/vehiculos/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al registrar vehículo: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Registrar Vehículo';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-car text-primary me-2"></i> Registrar Nuevo Vehículo
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
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Propietario / Cliente *</label>
                            <select name="cliente_id" class="form-select" required>
                                <option value="">-- Seleccione el cliente --</option>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($selectedClienteId == $c['id'] || (isset($_POST['cliente_id']) && $_POST['cliente_id'] == $c['id'])) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?> (DNI/RUC: <?= htmlspecialchars($c['dni_ruc']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Placa del Vehículo *</label>
                            <input type="text" name="placa" class="form-control text-uppercase font-monospace fw-bold" placeholder="Ej. ABC-123" required value="<?= htmlspecialchars($_POST['placa'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Marca *</label>
                            <input type="text" name="marca" class="form-control" placeholder="Ej. Toyota, Nissan, Hyundai" required value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Modelo *</label>
                            <input type="text" name="modelo" class="form-control" placeholder="Ej. Corolla, Sentra, Tucson" required value="<?= htmlspecialchars($_POST['modelo'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Año de Fabricación *</label>
                            <input type="number" name="anio" class="form-control" min="1970" max="<?= date('Y') + 1 ?>" placeholder="Ej. 2022" required value="<?= htmlspecialchars($_POST['anio'] ?? date('Y')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Color</label>
                            <input type="text" name="color" class="form-control" placeholder="Ej. Gris Plata, Blanco, Rojo" value="<?= htmlspecialchars($_POST['color'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Kilometraje Actual (km)</label>
                            <input type="number" name="kilometraje" class="form-control" min="0" placeholder="Ej. 45000" value="<?= htmlspecialchars($_POST['kilometraje'] ?? '0') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Observaciones / Estado del Vehículo</label>
                            <textarea name="observaciones" rows="3" class="form-control" placeholder="Detalles de carrocería, rayones previos o notas del propietario..."><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-accent px-4 shadow-sm">
                            <i class="fas fa-save me-1"></i> Guardar Vehículo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
