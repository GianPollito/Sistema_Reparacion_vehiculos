<?php

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$totalClientes = $pdo->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
$totalVehiculos = $pdo->query("SELECT COUNT(*) FROM vehiculos")->fetchColumn();
$totalMecanicos = $pdo->query("SELECT COUNT(*) FROM mecanicos WHERE estado = 'Activo'")->fetchColumn();

$totalOrdenesActivas = $pdo->query("SELECT COUNT(*) FROM ordenes_trabajo WHERE estado IN ('Pendiente', 'En Diagnóstico', 'En Proceso', 'Esperando Repuestos')")->fetchColumn();

$totalIngresos = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'Completado'")->fetchColumn();

$repuestosBajos = $pdo->query("SELECT COUNT(*) FROM repuestos WHERE stock <= stock_minimo")->fetchColumn();

$stmtRecentOT = $pdo->query("SELECT ot.*, v.placa, v.marca, v.modelo, 
    c.nombres as cliente_nombres, c.apellidos as cliente_apellidos,
    m.nombre_completo as mecanico_nombre
    FROM ordenes_trabajo ot
    JOIN vehiculos v ON ot.vehiculo_id = v.id
    JOIN clientes c ON ot.cliente_id = c.id
    LEFT JOIN mecanicos m ON ot.mecanico_id = m.id
    ORDER BY ot.id DESC LIMIT 5");
$ordenesRecientes = $stmtRecentOT->fetchAll();

$stmtCitas = $pdo->query("SELECT c.*, cl.nombres as cliente_nombres, cl.apellidos as cliente_apellidos,
    v.placa, v.marca, v.modelo
    FROM citas c
    JOIN clientes cl ON c.cliente_id = cl.id
    JOIN vehiculos v ON c.vehiculo_id = v.id
    WHERE c.estado IN ('Pendiente', 'Confirmada', 'En Atención')
    ORDER BY c.fecha_cita ASC, c.hora_cita ASC LIMIT 5");
$citasProximas = $stmtCitas->fetchAll();

$stmtEstados = $pdo->query("SELECT estado, COUNT(*) as cantidad FROM ordenes_trabajo GROUP BY estado");
$estadosData = $stmtEstados->fetchAll(PDO::FETCH_KEY_PAIR);

$stmtPagosMetodo = $pdo->query("SELECT metodo_pago, SUM(monto) as total FROM pagos WHERE estado = 'Completado' GROUP BY metodo_pago");
$pagosMetodoData = $stmtPagosMetodo->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0b3b60 0%, #1e293b 100%);">
    <div class="row g-0 align-items-center">
        <div class="col-lg-7 p-4 p-md-5 text-white">
            <span class="badge bg-danger text-uppercase px-3 py-1 mb-2 fw-bold">
                <i class="fas fa-check-circle me-1"></i> Taller Automotriz Especializado
            </span>
            <h1 class="fw-extrabold display-6 mb-2">Bienvenido a Motor Express</h1>
            <p class="text-white-50 fs-6 mb-4">
                Plataforma integral para el control de clientes, vehículos, mecánicos, órdenes de trabajo, diagnósticos computarizados e inventario de repuestos con liquidación en Efectivo, Tarjetas y Yape.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= BASE_URL ?>/modules/ordenes/crear.php" class="btn btn-motor-accent px-4 py-2 shadow">
                    <i class="fas fa-plus-circle me-1"></i> Nueva Orden de Trabajo
                </a>
                <a href="<?= BASE_URL ?>/modules/citas/crear.php" class="btn btn-outline-light px-4 py-2">
                    <i class="fas fa-calendar-plus me-1"></i> Programar Cita
                </a>
                <a href="<?= BASE_URL ?>/modules/reportes/index.php" class="btn btn-light px-4 py-2 text-dark">
                    <i class="fas fa-chart-line me-1"></i> Ver Reportes
                </a>
            </div>
        </div>
        <div class="col-lg-5 d-none d-lg-block p-4 text-center">
            <img src="<?= BASE_URL ?>/assets/img/banners/hero-car.svg" alt="Motor Express Automotriz" class="img-fluid rounded-4 shadow-sm" style="max-height: 230px;">
        </div>
    </div>
</div>

<div class="row g-3 g-xl-4 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="card card-kpi p-3 h-100 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase">Clientes</span>
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <h2 class="fw-bold text-dark mb-0"><?= number_format($totalClientes) ?></h2>
            <small class="text-muted">Propietarios registrados</small>
            <a href="<?= BASE_URL ?>/modules/clientes/index.php" class="stretched-link"></a>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl">
        <div class="card card-kpi p-3 h-100 border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase">Vehículos</span>
                <div class="kpi-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-car"></i>
                </div>
            </div>
            <h2 class="fw-bold text-dark mb-0"><?= number_format($totalVehiculos) ?></h2>
            <small class="text-muted">En base de datos</small>
            <a href="<?= BASE_URL ?>/modules/vehiculos/index.php" class="stretched-link"></a>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl">
        <div class="card card-kpi p-3 h-100 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase">Reparaciones</span>
                <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
            <h2 class="fw-bold text-dark mb-0"><?= number_format($totalOrdenesActivas) ?></h2>
            <small class="text-muted">Activas en bahía</small>
            <a href="<?= BASE_URL ?>/modules/ordenes/index.php?estado=En+Proceso" class="stretched-link"></a>
        </div>
    </div>

    <div class="col-6 col-md-6 col-xl">
        <div class="card card-kpi p-3 h-100 border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase">Mecánicos</span>
                <div class="kpi-icon bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-user-cog"></i>
                </div>
            </div>
            <h2 class="fw-bold text-dark mb-0"><?= number_format($totalMecanicos) ?></h2>
            <small class="text-muted">Personal de turno</small>
            <a href="<?= BASE_URL ?>/modules/mecanicos/index.php" class="stretched-link"></a>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl">
        <div class="card card-kpi p-3 h-100 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small fw-bold text-uppercase">Recaudación</span>
                <div class="kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <h2 class="fw-bold text-dark mb-0"><?= formatCurrency($totalIngresos) ?></h2>
            <small class="text-muted">Cobranza registrada</small>
            <a href="<?= BASE_URL ?>/modules/pagos/index.php" class="stretched-link"></a>
        </div>
    </div>
</div>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark m-0">
            <i class="fas fa-cubes text-primary me-2"></i> Áreas Técnicas de Especialidad Automotriz
        </h4>
        <span class="text-muted small">Servicios certificados Motor Express</span>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card card-service-img h-100">
                <div class="img-wrapper">
                    <img src="<?= BASE_URL ?>/assets/img/banners/engine.svg" alt="Afinamiento y Motor">
                    <span class="badge-category"><i class="fas fa-cog me-1"></i> MOTOR &amp; INYECCIÓN</span>
                </div>
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-1 text-dark">Afinamiento Electrónico</h6>
                    <p class="text-muted small mb-3">Limpieza de inyectores por ultrasonido, bujías y calibración de cuerpo de aceleración.</p>
                    <a href="<?= BASE_URL ?>/modules/servicios/index.php?categoria=Motor" class="btn btn-sm btn-outline-primary w-100">
                        Ver Servicios de Motor &rarr;
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card card-service-img h-100">
                <div class="img-wrapper">
                    <img src="<?= BASE_URL ?>/assets/img/banners/brakes.svg" alt="Frenos y Seguridad">
                    <span class="badge-category"><i class="fas fa-shield-alt me-1"></i> FRENOS &amp; SEGURIDAD</span>
                </div>
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-1 text-dark">Sistema de Frenos ABS</h6>
                    <p class="text-muted small mb-3">Pastillas cerámicas, rectificación de discos, purga hidráulica y diagnóstico de sensores.</p>
                    <a href="<?= BASE_URL ?>/modules/servicios/index.php?categoria=Frenos" class="btn btn-sm btn-outline-danger w-100">
                        Ver Servicios de Frenos &rarr;
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card card-service-img h-100">
                <div class="img-wrapper">
                    <img src="<?= BASE_URL ?>/assets/img/banners/electric.svg" alt="Diagnóstico Electrónico">
                    <span class="badge-category"><i class="fas fa-stethoscope me-1"></i> ESCANEO OBD-II</span>
                </div>
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-1 text-dark">Diagnóstico Computarizado</h6>
                    <p class="text-muted small mb-3">Lectura de códigos de falla DTC con escáner avanzado y detección de fallas eléctricas.</p>
                    <a href="<?= BASE_URL ?>/modules/diagnosticos/index.php" class="btn btn-sm btn-outline-info w-100">
                        Ver Diagnósticos &rarr;
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card card-service-img h-100">
                <div class="img-wrapper">
                    <img src="<?= BASE_URL ?>/assets/img/banners/oil.svg" alt="Mantenimiento Preventivo">
                    <span class="badge-category"><i class="fas fa-oil-can me-1"></i> LUBRICACIÓN</span>
                </div>
                <div class="card-body p-3">
                    <h6 class="fw-bold mb-1 text-dark">Cambio de Aceite &amp; Filtros</h6>
                    <p class="text-muted small mb-3">Aceites 100% sintéticos multimarca, filtros originales y revisión de 25 puntos clave.</p>
                    <a href="<?= BASE_URL ?>/modules/repuestos/index.php?categoria=Lubricantes" class="btn btn-sm btn-outline-success w-100">
                        Ver Insumos &amp; Stock &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-chart-pie text-success me-2"></i> Recaudación por Método de Pago
                </h6>
                <a href="<?= BASE_URL ?>/modules/reportes/ingresos.php" class="small text-decoration-none">Ver reporte &rarr;</a>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                <canvas id="dashboardChartPagos" style="max-height: 240px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-chart-bar text-primary me-2"></i> Distribución de Órdenes por Estado
                </h6>
                <a href="<?= BASE_URL ?>/modules/reportes/reparaciones.php" class="small text-decoration-none">Ver reporte &rarr;</a>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                <canvas id="dashboardChartEstados" style="max-height: 240px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-clipboard-list text-primary me-2"></i> Órdenes de Trabajo Recientes
                </h6>
                <a href="<?= BASE_URL ?>/modules/ordenes/index.php" class="small text-decoration-none">Ver todas &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>N° Orden</th>
                                <th>Vehículo</th>
                                <th>Cliente</th>
                                <th>Mecánico</th>
                                <th>Estado</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordenesRecientes as $ot): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>/modules/ordenes/ver.php?id=<?= $ot['id'] ?>" class="fw-bold font-monospace text-primary text-decoration-none">
                                            <?= htmlspecialchars($ot['numero_orden']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark font-monospace"><?= htmlspecialchars($ot['placa']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($ot['cliente_nombres'] . ' ' . $ot['cliente_apellidos']) ?></td>
                                    <td><small><?= htmlspecialchars($ot['mecanico_nombre'] ?? 'Sin asignar') ?></small></td>
                                    <td><?= statusBadge($ot['estado']) ?></td>
                                    <td class="text-end fw-bold text-dark"><?= formatCurrency($ot['total_general']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-calendar-check text-success me-2"></i> Próximas Citas
                </h6>
                <a href="<?= BASE_URL ?>/modules/citas/index.php" class="small text-decoration-none">Agenda completa &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha &amp; Hora</th>
                                <th>Cliente / Vehículo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($citasProximas)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No hay citas pendientes agendadas.</td></tr>
                            <?php else: ?>
                                <?php foreach ($citasProximas as $c): ?>
                                    <tr>
                                        <td>
                                            <strong><?= formatDate($c['fecha_cita']) ?></strong><br>
                                            <small class="text-muted"><i class="far fa-clock me-1 text-warning"></i> <?= date('h:i A', strtotime($c['hora_cita'])) ?></small>
                                        </td>
                                        <td>
                                            <strong class="text-dark d-block"><?= htmlspecialchars($c['cliente_nombres'] . ' ' . $c['cliente_apellidos']) ?></strong>
                                            <span class="badge bg-light text-dark border font-monospace"><?= htmlspecialchars($c['placa']) ?></span>
                                        </td>
                                        <td><?= statusBadge($c['estado']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxPagos = document.getElementById('dashboardChartPagos');
    if (ctxPagos) {
        new Chart(ctxPagos, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($pagosMetodoData)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($pagosMetodoData)) ?>,
                    backgroundColor: ['#10b981', '#0b3b60', '#0284c7', '#742284', '#f59e0b'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    const ctxEstados = document.getElementById('dashboardChartEstados');
    if (ctxEstados) {
        new Chart(ctxEstados, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($estadosData)) ?>,
                datasets: [{
                    label: 'Cantidad de Órdenes',
                    data: <?= json_encode(array_values($estadosData)) ?>,
                    backgroundColor: '#0b3b60',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
