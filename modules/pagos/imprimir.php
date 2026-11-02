<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die('ID no válido');

$stmt = $pdo->prepare("SELECT p.*, c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, 
    c.dni_ruc as cliente_dni, ot.numero_orden, v.placa, v.marca, v.modelo
    FROM pagos p
    JOIN clientes c ON p.cliente_id = c.id
    JOIN ordenes_trabajo ot ON p.orden_id = ot.id
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    WHERE p.id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) die('Pago no encontrado');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo <?= htmlspecialchars($p['numero_comprobante']) ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #e2e8f0; margin: 0; padding: 20px; }
        .ticket {
            max-width: 320px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border: 1px dashed #94a3b8;
            border-radius: 4px;
        }
        .text-center { text-align: center; }
        .divider { border-top: 1px dashed #333; margin: 10px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 13px; }
        .total-row { font-size: 16px; font-weight: bold; margin-top: 6px; }
        @media print {
            body { background: #fff; padding: 0; }
            .ticket { border: none; max-width: 100%; width: 100%; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="text-center no-print" style="margin-bottom: 15px;">
    <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer; font-weight: bold;">
        🖨️ IMPRIMIR COMPROBANTE
    </button>
</div>

<div class="ticket">
    <div class="text-center">
        <h2 style="margin: 0; font-size: 18px;"><?= APP_NAME ?></h2>
        <div style="font-size: 11px;"><?= APP_TAGLINE ?></div>
        <div style="font-size: 11px;"><?= APP_ADDRESS ?></div>
        <div style="font-size: 11px;">RUC: 20601234567 &bull; TEL: <?= APP_PHONE ?></div>
    </div>

    <div class="divider"></div>

    <div class="text-center" style="font-weight: bold; font-size: 14px;">
        <?= strtoupper($p['tipo_comprobante']) ?><br>
        <?= htmlspecialchars($p['numero_comprobante']) ?>
    </div>

    <div class="divider"></div>

    <div class="row">
        <span>Fecha:</span>
        <span><?= formatDate($p['fecha_pago'], true) ?></span>
    </div>
    <div class="row">
        <span>Cliente:</span>
        <span><?= htmlspecialchars($p['cliente_nombres'] . ' ' . $p['cliente_apellidos']) ?></span>
    </div>
    <div class="row">
        <span>DNI/RUC:</span>
        <span><?= htmlspecialchars($p['cliente_dni']) ?></span>
    </div>
    <div class="row">
        <span>Vehículo:</span>
        <span>[<?= htmlspecialchars($p['placa']) ?>] <?= htmlspecialchars($p['marca']) ?></span>
    </div>
    <div class="row">
        <span>Orden:</span>
        <span><?= htmlspecialchars($p['numero_orden']) ?></span>
    </div>

    <div class="divider"></div>

    <div class="row">
        <span>Método de Pago:</span>
        <strong><?= htmlspecialchars($p['metodo_pago']) ?></strong>
    </div>
    <?php if (!empty($p['numero_operacion'])): ?>
        <div class="row">
            <span>N° Operación:</span>
            <span><?= htmlspecialchars($p['numero_operacion']) ?></span>
        </div>
    <?php endif; ?>

    <div class="divider"></div>

    <div class="row total-row">
        <span>TOTAL COBRADO:</span>
        <span><?= formatCurrency($p['monto']) ?></span>
    </div>

    <div class="divider"></div>

    <div class="text-center" style="font-size: 11px; margin-top: 15px;">
        ¡Gracias por su preferencia!<br>
        Garantía de servicio por 90 días.<br>
        *** Conservar este comprobante ***
    </div>
</div>

</body>
</html>
