<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$selectedOrdenId = intval($_GET['orden_id'] ?? 0);
$presetMonto = floatval($_GET['monto'] ?? 0);

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM pagos");
$stmtCount->execute();
$count = $stmtCount->fetchColumn() + 1;
$nuevoComprobante = 'B001-' . str_pad($count, 6, '0', STR_PAD_LEFT);

$stmtOrd = $pdo->prepare("SELECT ot.id, ot.numero_orden, ot.total_general, ot.cliente_id,
    c.nombres as cliente_nombres, c.apellidos as cliente_apellidos,
    v.placa, v.marca, v.modelo,
    (SELECT COALESCE(SUM(p.monto), 0) FROM pagos p WHERE p.orden_id = ot.id AND p.estado = 'Completado') as pagado
    FROM ordenes_trabajo ot
    JOIN clientes c ON ot.cliente_id = c.id
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    ORDER BY ot.id DESC");
$stmtOrd->execute();
$ordenes = $stmtOrd->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero_comprobante = trim($_POST['numero_comprobante'] ?? $nuevoComprobante);
    $tipo_comprobante = trim($_POST['tipo_comprobante'] ?? 'Boleta');
    $orden_id = intval($_POST['orden_id'] ?? 0);
    $monto = floatval($_POST['monto'] ?? 0);
    $metodo_pago = trim($_POST['metodo_pago'] ?? 'Efectivo');
    $numero_operacion = trim($_POST['numero_operacion'] ?? '');
    $notas = trim($_POST['notas'] ?? '');

    if ($orden_id <= 0 || $monto <= 0 || empty($numero_comprobante) || empty($metodo_pago)) {
        $error = 'Por favor seleccione la orden e ingrese un monto válido mayor a 0.';
    } else {
        try {
            $stmtCli = $pdo->prepare("SELECT cliente_id FROM ordenes_trabajo WHERE id = ?");
            $stmtCli->execute([$orden_id]);
            $cliente_id = $stmtCli->fetchColumn();

            $stmtIns = $pdo->prepare("INSERT INTO pagos (numero_comprobante, tipo_comprobante, orden_id, cliente_id, monto, metodo_pago, numero_operacion, notas, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Completado')");
            $stmtIns->execute([$numero_comprobante, $tipo_comprobante, $orden_id, $cliente_id, $monto, $metodo_pago, $numero_operacion, $notas]);
            $pagoId = $pdo->lastInsertId();

            setFlash('success', "Pago de " . formatCurrency($monto) . " con $metodo_pago registrado correctamente. Comprobante: $numero_comprobante.");
            header("Location: " . BASE_URL . "/modules/pagos/ver.php?id=$pagoId");
            exit;
        } catch (PDOException $e) {
            $error = 'Error al registrar el pago: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Registrar Pago / Cobranza';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-cash-register text-success me-2"></i> Registrar Pago del Cliente
                    </h5>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Pagos
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
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">N° de Comprobante *</label>
                            <input type="text" name="numero_comprobante" class="form-control font-monospace fw-bold text-primary" required value="<?= htmlspecialchars($_POST['numero_comprobante'] ?? $nuevoComprobante) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo de Comprobante *</label>
                            <select name="tipo_comprobante" class="form-select">
                                <option value="Boleta" selected>Boleta de Venta Electrónica</option>
                                <option value="Factura">Factura Electrónica</option>
                                <option value="Recibo de Caja">Recibo de Caja de Taller</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Orden de Trabajo a Cobrar *</label>
                            <select name="orden_id" id="selectOrden" class="form-select" required onchange="actualizarSaldo(this)">
                                <option value="">-- Seleccionar Orden de Trabajo --</option>
                                <?php foreach ($ordenes as $o): ?>
                                    <?php $saldo = max(0, $o['total_general'] - $o['pagado']); ?>
                                    <option value="<?= $o['id'] ?>" 
                                            data-saldo="<?= $saldo ?>" 
                                            data-total="<?= $o['total_general'] ?>"
                                            <?= ($selectedOrdenId == $o['id'] || (isset($_POST['orden_id']) && $_POST['orden_id'] == $o['id'])) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($o['numero_orden']) ?> — [<?= htmlspecialchars($o['placa']) ?>] <?= htmlspecialchars($o['cliente_nombres'] . ' ' . $o['cliente_apellidos']) ?> (Total: <?= formatCurrency($o['total_general']) ?> | Saldo: <?= formatCurrency($saldo) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Monto a Cobrar (S/.) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">S/.</span>
                                <input type="number" step="0.01" min="0.50" name="monto" id="inputMonto" class="form-control form-control-lg fw-bold text-success fs-5" placeholder="0.00" required value="<?= htmlspecialchars($_POST['monto'] ?? ($presetMonto > 0 ? $presetMonto : '')) ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Método de Pago *</label>
                            <select name="metodo_pago" id="selectMetodo" class="form-select form-select-lg" required onchange="cambioMetodo(this.value)">
                                <option value="Efectivo" selected>💵 Efectivo</option>
                                <option value="Tarjeta de Crédito">💳 Tarjeta de Crédito (Visa / Mastercard / Amex)</option>
                                <option value="Tarjeta de Débito">💳 Tarjeta de Débito</option>
                                <option value="Yape">🟣 Yape (Billetera Móvil)</option>
                                <option value="Plin">🔵 Plin (Interbank / BBVA / Scotiabank)</option>
                            </select>
                        </div>

                        <div class="col-md-12" id="boxOperacion">
                            <label class="form-label fw-semibold">Número de Operación / Código de Aprobación POS / Ref</label>
                            <input type="text" name="numero_operacion" id="inputOperacion" class="form-control" placeholder="Ej. OPE-98741258 o Lote POS / N° de celular Yape" value="<?= htmlspecialchars($_POST['numero_operacion'] ?? '') ?>">
                            <small class="text-muted">Indispensable para auditoría de pagos con tarjeta o billeteras digitales.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notas / Observaciones del Pago</label>
                            <textarea name="notas" rows="2" class="form-control" placeholder="Detalles de la transacción, cuotas o comentarios..."><?= htmlspecialchars($_POST['notas'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-success px-4 shadow-sm fw-bold">
                            <i class="fas fa-check-circle me-1"></i> Confirmar y Registrar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function actualizarSaldo(select) {
    const selectedOption = select.options[select.selectedIndex];
    const saldo = selectedOption.getAttribute('data-saldo');
    if (saldo) {
        document.getElementById('inputMonto').value = parseFloat(saldo).toFixed(2);
    }
}

function cambioMetodo(metodo) {
    const box = document.getElementById('boxOperacion');
    const input = document.getElementById('inputOperacion');
    if (metodo === 'Efectivo') {
        input.placeholder = "Opcional (Ej. Pago en caja central)";
    } else if (metodo === 'Yape' || metodo === 'Plin') {
        input.placeholder = "N° de confirmación de Yape / Operación móvil";
    } else {
        input.placeholder = "N° de operación POS / Referencia de autorización";
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
