<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/modules/pagos/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, 
    c.dni_ruc as cliente_dni, c.telefono as cliente_telefono, c.direccion as cliente_direccion,
    ot.numero_orden, ot.total_general, v.placa, v.marca, v.modelo
    FROM pagos p
    JOIN clientes c ON p.cliente_id = c.id
    JOIN ordenes_trabajo ot ON p.orden_id = ot.id
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    WHERE p.id = ?");
$stmt->execute([$id]);
$pago = $stmt->fetch();

if (!$pago) {
    setFlash('danger', 'Comprobante de pago no encontrado.');
    header('Location: ' . BASE_URL . '/modules/pagos/index.php');
    exit;
}

$pageTitle = 'Comprobante: ' . $pago['numero_comprobante'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4">
            <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #0b3b60 0%, #1e293b 100%);">
                <div class="brand-icon mx-auto mb-2" style="width: 50px; height: 50px; font-size: 1.4rem;">
                    <i class="fas fa-receipt"></i>
                </div>
                <h4 class="fw-bold mb-0">MOTOR EXPRESS</h4>
                <small class="text-white-50 d-block">Taller Automotriz Especializado</small>
                <div class="mt-3">
                    <span class="badge bg-warning text-dark font-monospace fs-6 px-3 py-1">
                        <?= htmlspecialchars($pago['numero_comprobante']) ?>
                    </span>
                    <span class="badge bg-light text-dark ms-1"><?= htmlspecialchars($pago['tipo_comprobante']) ?></span>
                </div>
            </div>

            <div class="card-body p-4 bg-white">
                <div class="text-center my-3 pb-3 border-bottom">
                    <small class="text-muted d-block text-uppercase fw-bold">Monto Recaudado</small>
                    <h1 class="display-6 fw-bold text-success mb-0"><?= formatCurrency($pago['monto']) ?></h1>
                    <span class="badge bg-success-subtle text-success border border-success-subtle mt-1">
                        <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($pago['estado']) ?>
                    </span>
                </div>

                <ul class="list-unstyled small mb-4">
                    <li class="py-2 border-bottom d-flex justify-content-between">
                        <span class="text-muted">Método de Pago:</span>
                        <strong><i class="fas fa-wallet text-primary me-1"></i> <?= htmlspecialchars($pago['metodo_pago']) ?></strong>
                    </li>
                    <li class="py-2 border-bottom d-flex justify-content-between">
                        <span class="text-muted">N° de Operación / Ref:</span>
                        <span class="font-monospace fw-bold"><?= htmlspecialchars($pago['numero_operacion'] ?? 'No aplica') ?></span>
                    </li>
                    <li class="py-2 border-bottom d-flex justify-content-between">
                        <span class="text-muted">Fecha y Hora:</span>
                        <span><?= formatDate($pago['fecha_pago'], true) ?></span>
                    </li>
                    <li class="py-2 border-bottom d-flex justify-content-between">
                        <span class="text-muted">Orden de Trabajo:</span>
                        <a href="<?= BASE_URL ?>/modules/ordenes/ver.php?id=<?= $pago['orden_id'] ?>" class="fw-bold text-primary">
                            <?= htmlspecialchars($pago['numero_orden']) ?>
                        </a>
                    </li>
                    <li class="py-2 border-bottom d-flex justify-content-between">
                        <span class="text-muted">Vehículo Atendido:</span>
                        <span>[<?= htmlspecialchars($pago['placa']) ?>] <?= htmlspecialchars($pago['marca'] . ' ' . $pago['modelo']) ?></span>
                    </li>
                    <li class="py-2 border-bottom d-flex justify-content-between">
                        <span class="text-muted">Cliente:</span>
                        <strong class="text-dark"><?= htmlspecialchars($pago['cliente_nombres'] . ' ' . $pago['cliente_apellidos']) ?></strong>
                    </li>
                    <li class="py-2 d-flex justify-content-between">
                        <span class="text-muted">DNI / RUC:</span>
                        <span class="font-monospace"><?= htmlspecialchars($pago['cliente_dni']) ?></span>
                    </li>
                </ul>

                <?php if (!empty($pago['notas'])): ?>
                    <div class="p-2 bg-light rounded small text-secondary mb-4">
                        <strong>Notas:</strong> <?= nl2br(htmlspecialchars($pago['notas'])) ?>
                    </div>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <a href="imprimir.php?id=<?= $pago['id'] ?>" target="_blank" class="btn btn-outline-dark flex-grow-1">
                        <i class="fas fa-print me-1"></i> Imprimir Recibo
                    </a>
                    <a href="index.php" class="btn btn-light px-3">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
