<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID de orden no válido.');
}

$stmt = $pdo->prepare("SELECT ot.*, v.placa, v.marca, v.modelo, v.anio, v.color,
    c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, c.telefono as cliente_telefono, c.email as cliente_email, c.dni_ruc as cliente_dni, c.direccion as cliente_direccion,
    m.nombre_completo as mecanico_nombre, m.especialidad as mecanico_especialidad
    FROM ordenes_trabajo ot
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    JOIN clientes c ON ot.cliente_id = c.id
    LEFT JOIN mecanicos m ON ot.mecanico_id = m.id
    WHERE ot.id = ?");
$stmt->execute([$id]);
$orden = $stmt->fetch();

if (!$orden) {
    die('Orden no encontrada.');
}

$stmtS = $pdo->prepare("SELECT os.*, s.nombre, s.categoria, s.codigo FROM orden_servicios os JOIN servicios s ON os.servicio_id = s.id WHERE os.orden_id = ?");
$stmtS->execute([$id]);
$servicios = $stmtS->fetchAll();

$stmtR = $pdo->prepare("SELECT ru.*, r.nombre, r.codigo, r.marca FROM repuestos_utilizados ru JOIN repuestos r ON ru.repuesto_id = r.id WHERE ru.orden_id = ?");
$stmtR->execute([$id]);
$repuestos = $stmtR->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Trabajo <?= htmlspecialchars($orden['numero_orden']) ?> | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .invoice-box {
            max-width: 850px;
            margin: 25px auto;
            padding: 35px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }
        @media print {
            body { background: #fff; }
            .invoice-box { box-shadow: none; padding: 0; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container my-3 no-print text-center">
    <button onclick="window.print()" class="btn btn-primary px-4 py-2 me-2">
        <i class="fas fa-print me-1"></i> Imprimir / Guardar en PDF
    </button>
    <a href="ver.php?id=<?= $orden['id'] ?>" class="btn btn-outline-secondary px-4 py-2">
        <i class="fas fa-arrow-left me-1"></i> Volver a la Orden
    </a>
</div>

<div class="invoice-box">
    <div class="row align-items-center pb-3 mb-4 border-bottom border-2">
        <div class="col-7">
            <h2 class="fw-bold text-primary mb-0"><i class="fas fa-tools text-danger me-2"></i><?= APP_NAME ?></h2>
            <small class="text-muted d-block fw-semibold"><?= APP_TAGLINE ?></small>
            <small class="text-secondary d-block"><?= APP_ADDRESS ?> &bull; Tel: <?= APP_PHONE ?></small>
            <small class="text-secondary d-block">Email: <?= APP_EMAIL ?> &bull; RUC: 20601234567</small>
        </div>
        <div class="col-5 text-end">
            <div class="p-2 border rounded bg-light">
                <span class="text-uppercase small text-muted fw-bold d-block">ORDEN DE TRABAJO</span>
                <h4 class="text-danger fw-bold mb-0 font-monospace"><?= htmlspecialchars($orden['numero_orden']) ?></h4>
                <span class="badge bg-dark mt-1">Estado: <?= htmlspecialchars($orden['estado']) ?></span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="p-3 border rounded bg-light h-100">
                <h6 class="fw-bold text-primary border-bottom pb-1 mb-2"><i class="fas fa-user me-1"></i> DATOS DEL CLIENTE</h6>
                <div class="small">
                    <div><strong>Cliente:</strong> <?= htmlspecialchars($orden['cliente_nombres'] . ' ' . $orden['cliente_apellidos']) ?></div>
                    <div><strong>DNI / RUC:</strong> <?= htmlspecialchars($orden['cliente_dni']) ?></div>
                    <div><strong>Teléfono:</strong> <?= htmlspecialchars($orden['cliente_telefono'] ?? '-') ?></div>
                    <div><strong>Email:</strong> <?= htmlspecialchars($orden['cliente_email'] ?? '-') ?></div>
                    <div><strong>Dirección:</strong> <?= htmlspecialchars($orden['cliente_direccion'] ?? '-') ?></div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="p-3 border rounded bg-light h-100">
                <h6 class="fw-bold text-primary border-bottom pb-1 mb-2"><i class="fas fa-car me-1"></i> DATOS DEL VEHÍCULO</h6>
                <div class="small">
                    <div><strong>Placa:</strong> <span class="badge bg-warning text-dark font-monospace"><?= htmlspecialchars($orden['placa']) ?></span></div>
                    <div><strong>Vehículo:</strong> <?= htmlspecialchars($orden['marca'] . ' ' . $orden['modelo']) ?> (<?= htmlspecialchars($orden['anio']) ?>)</div>
                    <div><strong>Color:</strong> <?= htmlspecialchars($orden['color'] ?? '-') ?></div>
                    <div><strong>Kilometraje:</strong> <?= number_format($orden['kilometraje_ingreso']) ?> km</div>
                    <div><strong>Nivel de Combustible:</strong> <?= htmlspecialchars($orden['nivel_combustible']) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-3 border rounded mb-4 small bg-light">
        <div class="row">
            <div class="col-4">
                <strong>Fecha de Ingreso:</strong><br> <?= formatDate($orden['fecha_ingreso'], true) ?>
            </div>
            <div class="col-4">
                <strong>Fecha Estimada Entrega:</strong><br> <?= formatDate($orden['fecha_estimada_entrega'], true) ?>
            </div>
            <div class="col-4">
                <strong>Mecánico Responsable:</strong><br> <?= htmlspecialchars($orden['mecanico_nombre'] ?? 'Sin asignar') ?>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">MOTIVO DE INGRESO / FALLA REPORTADA</h6>
        <div class="p-2 border rounded small bg-light">
            <?= nl2br(htmlspecialchars($orden['problema_reportado'])) ?>
        </div>
    </div>

    <div class="mb-4">
        <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">1. SERVICIOS Y MANO DE OBRA REALIZADA</h6>
        <table class="table table-bordered table-sm small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Descripción del Servicio</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-end">Tarifa</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($servicios)): ?>
                    <tr><td colspan="5" class="text-center text-muted">Sin servicios registrados</td></tr>
                <?php else: ?>
                    <?php foreach ($servicios as $s): ?>
                        <tr>
                            <td class="font-monospace"><?= htmlspecialchars($s['codigo']) ?></td>
                            <td><?= htmlspecialchars($s['nombre']) ?></td>
                            <td class="text-center"><?= $s['cantidad'] ?></td>
                            <td class="text-end"><?= formatCurrency($s['precio_unitario']) ?></td>
                            <td class="text-end"><?= formatCurrency($s['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr class="fw-bold bg-light">
                    <td colspan="4" class="text-end">Subtotal Mano de Obra:</td>
                    <td class="text-end"><?= formatCurrency($orden['total_servicios']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mb-4">
        <h6 class="fw-bold text-dark border-bottom pb-1 mb-2">2. REPUESTOS E INSUMOS UTILIZADOS</h6>
        <table class="table table-bordered table-sm small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Repuesto / Pieza</th>
                    <th>Marca</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-end">P. Unitario</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($repuestos)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No se utilizaron repuestos adicionales</td></tr>
                <?php else: ?>
                    <?php foreach ($repuestos as $r): ?>
                        <tr>
                            <td class="font-monospace"><?= htmlspecialchars($r['codigo']) ?></td>
                            <td><?= htmlspecialchars($r['nombre']) ?></td>
                            <td><?= htmlspecialchars($r['marca'] ?? 'Genérico') ?></td>
                            <td class="text-center"><?= $r['cantidad'] ?></td>
                            <td class="text-end"><?= formatCurrency($r['precio_unitario']) ?></td>
                            <td class="text-end"><?= formatCurrency($r['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr class="fw-bold bg-light">
                    <td colspan="5" class="text-end">Subtotal Repuestos:</td>
                    <td class="text-end"><?= formatCurrency($orden['total_repuestos']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end mb-4">
        <div class="col-5">
            <div class="p-3 border rounded bg-light text-end">
                <div class="d-flex justify-content-between mb-1 small">
                    <span>Mano de Obra:</span>
                    <span><?= formatCurrency($orden['total_servicios']) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span>Repuestos:</span>
                    <span><?= formatCurrency($orden['total_repuestos']) ?></span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 fs-5 fw-bold text-primary">
                    <span>TOTAL LIQUIDACIÓN:</span>
                    <span><?= formatCurrency($orden['total_general']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row text-center pt-5 mt-4" style="margin-top: 60px;">
        <div class="col-6">
            <div class="border-top border-dark mx-4 pt-2 small">
                <strong><?= htmlspecialchars($orden['cliente_nombres'] . ' ' . $orden['cliente_apellidos']) ?></strong><br>
                Firma del Cliente (Conforme)
            </div>
        </div>
        <div class="col-6">
            <div class="border-top border-dark mx-4 pt-2 small">
                <strong>TALLER MOTOR EXPRESS</strong><br>
                Mecánico Responsable / Control de Calidad
            </div>
        </div>
    </div>
</div>

</body>
</html>
