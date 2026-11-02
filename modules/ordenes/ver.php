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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'cambiar_estado') {
        $nuevoEstado = trim($_POST['estado'] ?? '');
        $notas = trim($_POST['notas_tecnicas'] ?? '');
        if (!empty($nuevoEstado)) {
            $stmt = $pdo->prepare("UPDATE ordenes_trabajo SET estado = ?, notas_tecnicas = ? WHERE id = ?");
            $stmt->execute([$nuevoEstado, $notas, $id]);
            setFlash('success', "Estado de la orden actualizado a: $nuevoEstado");
        }
        header("Location: ver.php?id=$id");
        exit;
    }

    if ($action === 'agregar_servicio') {
        $servicio_id = intval($_POST['servicio_id'] ?? 0);
        $cantidad = intval($_POST['cantidad'] ?? 1);
        if ($servicio_id > 0 && $cantidad > 0) {
            $stmtS = $pdo->prepare("SELECT precio FROM servicios WHERE id = ?");
            $stmtS->execute([$servicio_id]);
            $srv = $stmtS->fetch();
            if ($srv) {
                $precio = floatval($srv['precio']);
                $subtotal = $precio * $cantidad;
                $stmtIns = $pdo->prepare("INSERT INTO orden_servicios (orden_id, servicio_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmtIns->execute([$id, $servicio_id, $cantidad, $precio, $subtotal]);

                recalcularTotalesOrden($pdo, $id);
                setFlash('success', 'Servicio agregado a la orden de trabajo.');
            }
        }
        header("Location: ver.php?id=$id");
        exit;
    }

    if ($action === 'eliminar_servicio') {
        $itemId = intval($_POST['item_id'] ?? 0);
        if ($itemId > 0) {
            $pdo->prepare("DELETE FROM orden_servicios WHERE id = ? AND orden_id = ?")->execute([$itemId, $id]);
            recalcularTotalesOrden($pdo, $id);
            setFlash('info', 'Servicio eliminado de la orden.');
        }
        header("Location: ver.php?id=$id");
        exit;
    }

    if ($action === 'agregar_repuesto') {
        $repuesto_id = intval($_POST['repuesto_id'] ?? 0);
        $cantidad = intval($_POST['cantidad'] ?? 1);

        if ($repuesto_id > 0 && $cantidad > 0) {
            $stmtR = $pdo->prepare("SELECT precio_unitario, stock, nombre FROM repuestos WHERE id = ?");
            $stmtR->execute([$repuesto_id]);
            $rep = $stmtR->fetch();

            if ($rep) {
                if ($rep['stock'] < $cantidad) {
                    setFlash('danger', "Stock insuficiente para {$rep['nombre']}. Solo quedan {$rep['stock']} unidades disponibles.");
                } else {
                    $precio = floatval($rep['precio_unitario']);
                    $subtotal = $precio * $cantidad;

                    $pdo->prepare("UPDATE repuestos SET stock = stock - ? WHERE id = ?")->execute([$cantidad, $repuesto_id]);

                    $stmtInsR = $pdo->prepare("INSERT INTO repuestos_utilizados (orden_id, repuesto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
                    $stmtInsR->execute([$id, $repuesto_id, $cantidad, $precio, $subtotal]);

                    recalcularTotalesOrden($pdo, $id);
                    setFlash('success', "Se agregaron $cantidad unidades de {$rep['nombre']} a la orden.");
                }
            }
        }
        header("Location: ver.php?id=$id");
        exit;
    }

    if ($action === 'eliminar_repuesto') {
        $ruId = intval($_POST['ru_id'] ?? 0);
        if ($ruId > 0) {
            $stmtRU = $pdo->prepare("SELECT repuesto_id, cantidad FROM repuestos_utilizados WHERE id = ? AND orden_id = ?");
            $stmtRU->execute([$ruId, $id]);
            $ru = $stmtRU->fetch();

            if ($ru) {
                $pdo->prepare("UPDATE repuestos SET stock = stock + ? WHERE id = ?")->execute([$ru['cantidad'], $ru['repuesto_id']]);
                $pdo->prepare("DELETE FROM repuestos_utilizados WHERE id = ?")->execute([$ruId]);

                recalcularTotalesOrden($pdo, $id);
                setFlash('info', 'Repuesto removido de la orden y stock repuesto en el inventario.');
            }
        }
        header("Location: ver.php?id=$id");
        exit;
    }
}

function recalcularTotalesOrden(PDO $pdo, $ordenId) {
    $stmtS = $pdo->prepare("SELECT COALESCE(SUM(subtotal), 0) FROM orden_servicios WHERE orden_id = ?");
    $stmtS->execute([$ordenId]);
    $totalServicios = floatval($stmtS->fetchColumn());

    $stmtR = $pdo->prepare("SELECT COALESCE(SUM(subtotal), 0) FROM repuestos_utilizados WHERE orden_id = ?");
    $stmtR->execute([$ordenId]);
    $totalRepuestos = floatval($stmtR->fetchColumn());

    $totalGeneral = $totalServicios + $totalRepuestos;

    $stmtUp = $pdo->prepare("UPDATE ordenes_trabajo SET total_servicios = ?, total_repuestos = ?, total_general = ? WHERE id = ?");
    $stmtUp->execute([$totalServicios, $totalRepuestos, $totalGeneral, $ordenId]);
}

$stmt = $pdo->prepare("SELECT ot.*, v.placa, v.marca, v.modelo, v.anio, v.color,
    c.nombres as cliente_nombres, c.apellidos as cliente_apellidos, c.telefono as cliente_telefono, c.email as cliente_email, c.dni_ruc as cliente_dni,
    m.nombre_completo as mecanico_nombre, m.especialidad as mecanico_especialidad
    FROM ordenes_trabajo ot
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    JOIN clientes c ON ot.cliente_id = c.id
    LEFT JOIN mecanicos m ON ot.mecanico_id = m.id
    WHERE ot.id = ?");
$stmt->execute([$id]);
$orden = $stmt->fetch();

if (!$orden) {
    setFlash('danger', 'Orden de trabajo no encontrada.');
    header('Location: ' . BASE_URL . '/modules/ordenes/index.php');
    exit;
}

$stmtServicios = $pdo->prepare("SELECT os.*, s.nombre, s.categoria, s.codigo 
    FROM orden_servicios os
    JOIN servicios s ON os.servicio_id = s.id
    WHERE os.orden_id = ?");
$stmtServicios->execute([$id]);
$serviciosOrden = $stmtServicios->fetchAll();

$stmtRepuestos = $pdo->prepare("SELECT ru.*, r.nombre, r.codigo, r.marca, r.categoria
    FROM repuestos_utilizados ru
    JOIN repuestos r ON ru.repuesto_id = r.id
    WHERE ru.orden_id = ?");
$stmtRepuestos->execute([$id]);
$repuestosOrden = $stmtRepuestos->fetchAll();

$stmtDiag = $pdo->prepare("SELECT d.*, m.nombre_completo as mecanico_nombre
    FROM diagnosticos d
    JOIN mecanicos m ON d.mecanico_id = m.id
    WHERE d.orden_id = ? OR d.vehiculo_id = ?");
$stmtDiag->execute([$id, $orden['vehiculo_id']]);
$diagnosticos = $stmtDiag->fetchAll();

$stmtPagos = $pdo->prepare("SELECT * FROM pagos WHERE orden_id = ? ORDER BY id DESC");
$stmtPagos->execute([$id]);
$pagos = $stmtPagos->fetchAll();

$totalPagado = 0;
foreach ($pagos as $p) {
    if ($p['estado'] === 'Completado') {
        $totalPagado += floatval($p['monto']);
    }
}
$saldoPendiente = max(0, $orden['total_general'] - $totalPagado);

$catalogoServicios = $pdo->query("SELECT id, codigo, nombre, categoria, precio FROM servicios ORDER BY nombre ASC")->fetchAll();
$catalogoRepuestos = $pdo->query("SELECT id, codigo, nombre, marca, precio_unitario, stock FROM repuestos WHERE stock > 0 ORDER BY nombre ASC")->fetchAll();

$pageTitle = 'Orden de Trabajo: ' . $orden['numero_orden'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($orden['numero_orden']) ?></h2>
            <?= statusBadge($orden['estado']) ?>
        </div>
        <p class="text-muted small mb-0">
            Ingreso: <strong><?= formatDate($orden['fecha_ingreso'], true) ?></strong> &bull;
            Entrega Estimada: <strong><?= formatDate($orden['fecha_estimada_entrega'], true) ?></strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="imprimir.php?id=<?= $orden['id'] ?>" target="_blank" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-print me-1"></i> Imprimir Orden
        </a>
        <a href="editar.php?id=<?= $orden['id'] ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i> Editar Orden
        </a>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver a Órdenes
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-car text-primary me-2"></i> Vehículo y Propietario
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-3 p-2 bg-light rounded text-center">
                    <span class="badge bg-warning text-dark font-monospace fs-5 px-3 py-1 border border-dark border-opacity-10">
                        <?= htmlspecialchars($orden['placa']) ?>
                    </span>
                    <strong class="d-block mt-1"><?= htmlspecialchars($orden['marca'] . ' ' . $orden['modelo']) ?> (<?= htmlspecialchars($orden['anio']) ?>)</strong>
                    <small class="text-muted">Color: <?= htmlspecialchars($orden['color'] ?? '-') ?></small>
                </div>

                <ul class="list-unstyled small mb-0">
                    <li class="py-2 border-bottom">
                        <span class="text-muted">Propietario:</span>
                        <strong class="d-block text-dark"><?= htmlspecialchars($orden['cliente_nombres'] . ' ' . $orden['cliente_apellidos']) ?></strong>
                    </li>
                    <li class="py-2 border-bottom">
                        <span class="text-muted">DNI / RUC:</span>
                        <span class="font-monospace fw-semibold"><?= htmlspecialchars($orden['cliente_dni']) ?></span>
                    </li>
                    <li class="py-2 border-bottom">
                        <span class="text-muted">Teléfono:</span>
                        <span><i class="fas fa-phone text-success me-1"></i> <?= htmlspecialchars($orden['cliente_telefono'] ?? '-') ?></span>
                    </li>
                    <li class="py-2 border-bottom">
                        <span class="text-muted">Kilometraje de Ingreso:</span>
                        <strong><?= number_format($orden['kilometraje_ingreso']) ?> km</strong>
                    </li>
                    <li class="py-2">
                        <span class="text-muted">Nivel de Combustible:</span>
                        <span class="badge bg-info-subtle text-info fw-bold"><i class="fas fa-gas-pump me-1"></i> <?= htmlspecialchars($orden['nivel_combustible']) ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-clipboard-check text-primary me-2"></i> Estado &amp; Mecánico
                </h6>
            </div>
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="mb-3">
                        <small class="text-muted fw-bold d-block">Mecánico Responsable:</small>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <i class="fas fa-user-cog text-danger fs-5"></i>
                            <div>
                                <strong class="text-dark d-block"><?= htmlspecialchars($orden['mecanico_nombre'] ?? 'Sin asignar') ?></strong>
                                <small class="text-muted"><?= htmlspecialchars($orden['mecanico_especialidad'] ?? '') ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted fw-bold d-block">Problema Reportado por el Cliente:</small>
                        <p class="small text-secondary bg-light p-2 rounded mb-0 mt-1">
                            <?= nl2br(htmlspecialchars($orden['problema_reportado'])) ?>
                        </p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted fw-bold d-block">Notas Técnicas del Taller:</small>
                        <p class="small text-secondary bg-light p-2 rounded mb-0 mt-1">
                            <?= nl2br(htmlspecialchars($orden['notas_tecnicas'] ?? 'Sin notas adicionales.')) ?>
                        </p>
                    </div>
                </div>

                <form method="POST" action="" class="p-2 border rounded-3 bg-light">
                    <input type="hidden" name="action" value="cambiar_estado">
                    <label class="form-label small fw-bold mb-1">Actualizar Estado de Reparación:</label>
                    <div class="input-group input-group-sm">
                        <select name="estado" class="form-select">
                            <option value="Pendiente" <?= ($orden['estado'] === 'Pendiente') ? 'selected' : '' ?>>Pendiente</option>
                            <option value="En Diagnóstico" <?= ($orden['estado'] === 'En Diagnóstico') ? 'selected' : '' ?>>En Diagnóstico</option>
                            <option value="En Proceso" <?= ($orden['estado'] === 'En Proceso') ? 'selected' : '' ?>>En Proceso</option>
                            <option value="Esperando Repuestos" <?= ($orden['estado'] === 'Esperando Repuestos') ? 'selected' : '' ?>>Esperando Repuestos</option>
                            <option value="Finalizada" <?= ($orden['estado'] === 'Finalizada') ? 'selected' : '' ?>>Finalizada</option>
                            <option value="Entregada" <?= ($orden['estado'] === 'Entregada') ? 'selected' : '' ?>>Entregada</option>
                            <option value="Cancelada" <?= ($orden['estado'] === 'Cancelada') ? 'selected' : '' ?>>Cancelada</option>
                        </select>
                        <button type="submit" class="btn btn-motor-primary">
                            <i class="fas fa-check me-1"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-receipt text-primary me-2"></i> Liquidación de la Reparación
                </h6>
            </div>
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-wrench me-1 text-primary"></i> Total Mano de Obra:</span>
                        <strong class="text-dark"><?= formatCurrency($orden['total_servicios']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-boxes me-1 text-danger"></i> Total Repuestos Usados:</span>
                        <strong class="text-dark"><?= formatCurrency($orden['total_repuestos']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom fs-5">
                        <span class="fw-bold text-dark">Total General:</span>
                        <strong class="text-primary"><?= formatCurrency($orden['total_general']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="fas fa-check-circle me-1 text-success"></i> Pagos Registrados:</span>
                        <strong class="text-success"><?= formatCurrency($totalPagado) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between py-2 fs-6">
                        <span class="fw-bold <?= ($saldoPendiente > 0) ? 'text-danger' : 'text-success' ?>">Saldo Pendiente:</span>
                        <strong class="<?= ($saldoPendiente > 0) ? 'text-danger' : 'text-success' ?>"><?= formatCurrency($saldoPendiente) ?></strong>
                    </div>
                </div>

                <div class="mt-3">
                    <?php if ($saldoPendiente > 0): ?>
                        <a href="<?= BASE_URL ?>/modules/pagos/crear.php?orden_id=<?= $orden['id'] ?>&monto=<?= $saldoPendiente ?>" class="btn btn-success w-100 py-2 shadow-sm fw-bold">
                            <i class="fas fa-cash-register me-1"></i> Cobrar Saldo (<?= formatCurrency($saldoPendiente) ?>)
                        </a>
                    <?php else: ?>
                        <div class="alert alert-success py-2 mb-0 text-center small fw-bold">
                            <i class="fas fa-check-double me-1"></i> ¡Orden 100% Pagada!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fas fa-wrench text-primary me-2"></i> Servicios de Mano de Obra Realizados
        </h5>
        <button type="button" class="btn btn-sm btn-motor-primary" data-bs-toggle="modal" data-bs-target="#modalAddServicio">
            <i class="fas fa-plus me-1"></i> Agregar Servicio
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Servicio Técnico</th>
                        <th>Categoría</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">Tarifa</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Quitar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($serviciosOrden)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Aún no se han agregado servicios a esta orden de trabajo.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($serviciosOrden as $so): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($so['codigo']) ?></span></td>
                                <td><strong><?= htmlspecialchars($so['nombre']) ?></strong></td>
                                <td><span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($so['categoria']) ?></span></td>
                                <td class="text-center"><?= $so['cantidad'] ?></td>
                                <td class="text-end"><?= formatCurrency($so['precio_unitario']) ?></td>
                                <td class="text-end fw-bold text-dark"><?= formatCurrency($so['subtotal']) ?></td>
                                <td class="text-end">
                                    <form method="POST" action="" onsubmit="return confirm('¿Quitar servicio?');" class="d-inline">
                                        <input type="hidden" name="action" value="eliminar_servicio">
                                        <input type="hidden" name="item_id" value="<?= $so['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-boxes text-danger me-2"></i> Repuestos e Insumos Utilizados
            </h5>
            <small class="text-muted">Control de partes instaladas con descuento automático de stock de almacén.</small>
        </div>
        <button type="button" class="btn btn-sm btn-motor-accent" data-bs-toggle="modal" data-bs-target="#modalAddRepuesto">
            <i class="fas fa-plus me-1"></i> Asignar Repuesto
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Repuesto Instalado</th>
                        <th>Marca</th>
                        <th>Categoría</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">Precio Unitario</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Quitar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($repuestosOrden)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No se han registrado repuestos o insumos utilizados para esta reparación.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($repuestosOrden as $ro): ?>
                            <tr>
                                <td><span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($ro['codigo']) ?></span></td>
                                <td><strong><?= htmlspecialchars($ro['nombre']) ?></strong></td>
                                <td><?= htmlspecialchars($ro['marca'] ?? 'Genérico') ?></td>
                                <td><span class="badge bg-info-subtle text-info"><?= htmlspecialchars($ro['categoria']) ?></span></td>
                                <td class="text-center"><span class="badge bg-dark px-2 py-1"><?= $ro['cantidad'] ?> unid.</span></td>
                                <td class="text-end"><?= formatCurrency($ro['precio_unitario']) ?></td>
                                <td class="text-end fw-bold text-danger"><?= formatCurrency($ro['subtotal']) ?></td>
                                <td class="text-end">
                                    <form method="POST" action="" onsubmit="return confirm('¿Remover repuesto? Se devolverá al stock.');" class="d-inline">
                                        <input type="hidden" name="action" value="eliminar_repuesto">
                                        <input type="hidden" name="ru_id" value="<?= $ro['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Remover y reponer a stock">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fas fa-money-bill-wave text-success me-2"></i> Pagos Registrados (Efectivo, Tarjeta, Yape)
        </h5>
        <?php if ($saldoPendiente > 0): ?>
            <a href="<?= BASE_URL ?>/modules/pagos/crear.php?orden_id=<?= $orden['id'] ?>&monto=<?= $saldoPendiente ?>" class="btn btn-sm btn-success">
                <i class="fas fa-plus me-1"></i> Registrar Pago
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Comprobante</th>
                        <th>Tipo</th>
                        <th>Método de Pago</th>
                        <th>N° Operación / Ref</th>
                        <th>Fecha de Pago</th>
                        <th>Estado</th>
                        <th class="text-end">Monto Cobrado</th>
                        <th class="text-end">Recibo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Aún no se han recibido pagos para esta orden de trabajo.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pagos as $p): ?>
                            <tr>
                                <td><strong class="font-monospace text-primary"><?= htmlspecialchars($p['numero_comprobante']) ?></strong></td>
                                <td><?= htmlspecialchars($p['tipo_comprobante']) ?></td>
                                <td>
                                    <?php 
                                        $iconPago = 'money-bill-alt';
                                        if (str_contains($p['metodo_pago'], 'Tarjeta')) $iconPago = 'credit-card';
                                        if ($p['metodo_pago'] === 'Yape') $iconPago = 'mobile-alt';
                                    ?>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-<?= $iconPago ?> text-success me-1"></i> <?= htmlspecialchars($p['metodo_pago']) ?>
                                    </span>
                                </td>
                                <td><span class="font-monospace small"><?= htmlspecialchars($p['numero_operacion'] ?? '-') ?></span></td>
                                <td><?= formatDate($p['fecha_pago'], true) ?></td>
                                <td><span class="badge bg-success">Completado</span></td>
                                <td class="text-end fw-bold text-success fs-6"><?= formatCurrency($p['monto']) ?></td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/modules/pagos/ver.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver Recibo de Pago">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddServicio" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="" class="modal-content">
            <input type="hidden" name="action" value="agregar_servicio">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-wrench text-primary me-2"></i> Agregar Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Seleccionar Servicio *</label>
                    <select name="servicio_id" class="form-select" required>
                        <option value="">-- Elija un servicio del tarifario --</option>
                        <?php foreach ($catalogoServicios as $cs): ?>
                            <option value="<?= $cs['id'] ?>">
                                [<?= htmlspecialchars($cs['codigo']) ?>] <?= htmlspecialchars($cs['nombre']) ?> (<?= formatCurrency($cs['precio']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cantidad *</label>
                    <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-motor-primary"><i class="fas fa-plus me-1"></i> Agregar Servicio</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalAddRepuesto" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="" class="modal-content">
            <input type="hidden" name="action" value="agregar_repuesto">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-boxes text-danger me-2"></i> Asignar Repuesto del Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Seleccionar Repuesto / Insumo *</label>
                    <select name="repuesto_id" class="form-select" required>
                        <option value="">-- Seleccione pieza (Stock disponible) --</option>
                        <?php foreach ($catalogoRepuestos as $cr): ?>
                            <option value="<?= $cr['id'] ?>">
                                [<?= htmlspecialchars($cr['codigo']) ?>] <?= htmlspecialchars($cr['nombre']) ?> (Stock: <?= $cr['stock'] ?> u.) - <?= formatCurrency($cr['precio_unitario']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Cantidad a Instalar *</label>
                    <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
                    <small class="text-muted">Se descontará automáticamente del inventario del taller.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-motor-accent"><i class="fas fa-plus me-1"></i> Asignar Repuesto</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
