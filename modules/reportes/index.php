<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$totalServiciosRealizados = $pdo->query("SELECT COUNT(*) FROM orden_servicios")->fetchColumn();
$stmtTopServicios = $pdo->query("SELECT s.nombre, s.categoria, COUNT(os.id) as total_ejecutado, SUM(os.subtotal) as ingresos_generados 
    FROM orden_servicios os 
    JOIN servicios s ON os.servicio_id = s.id 
    GROUP BY s.id ORDER BY total_ejecutado DESC LIMIT 5");
$topServicios = $stmtTopServicios->fetchAll();

$totalVehiculosAtendidos = $pdo->query("SELECT COUNT(DISTINCT vehiculo_id) FROM ordenes_trabajo")->fetchColumn();
$stmtTopMarcas = $pdo->query("SELECT v.marca, COUNT(ot.id) as total_atenciones 
    FROM ordenes_trabajo ot 
    JOIN vehiculos v ON ot.vehiculo_id = v.id 
    GROUP BY v.marca ORDER BY total_atenciones DESC LIMIT 5");
$topMarcas = $stmtTopMarcas->fetchAll();

$stmtPendientes = $pdo->query("SELECT COUNT(*) FROM ordenes_trabajo WHERE estado IN ('Pendiente', 'En Diagnóstico', 'En Proceso', 'Esperando Repuestos')");
$totalReparacionesPendientes = $stmtPendientes->fetchColumn();

$totalIngresos = $pdo->query("SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'Completado'")->fetchColumn();
$stmtIngresosMetodo = $pdo->query("SELECT metodo_pago, SUM(monto) as total FROM pagos WHERE estado = 'Completado' GROUP BY metodo_pago");
$ingresosMetodo = $stmtIngresosMetodo->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Módulo de Reportes y Estadísticas';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="fw-bold mb-1 text-dark">
            <i class="fas fa-chart-line text-primary me-2"></i> Reportes y Estadísticas del Taller
        </h2>
        <p class="text-muted small mb-0">Monitoreo de servicios realizados, parque automotor atendido, reparaciones en curso e ingresos.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="exportar.php?tipo=general" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-excel me-1"></i> Exportar Datos (CSV)
        </a>
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-print me-1"></i> Imprimir Reporte
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card card-kpi h-100 p-3 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-bold">Servicios Realizados</span>
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-wrench"></i>
                </div>
            </div>
            <h2 class="fw-bold text-dark mb-1"><?= number_format($totalServiciosRealizados) ?></h2>
            <small class="text-muted d-block mb-3">Mantenimientos y reparaciones completadas</small>
            <a href="servicios.php" class="btn btn-sm btn-outline-primary mt-auto w-100">
                <i class="fas fa-list-alt me-1"></i> Ver Reporte Detallado
            </a>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card card-kpi h-100 p-3 border-start border-info border-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-bold">Vehículos Atendidos</span>
                <div class="kpi-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-car"></i>
                </div>
            </div>
            <h2 class="fw-bold text-dark mb-1"><?= number_format($totalVehiculosAtendidos) ?></h2>
            <small class="text-muted d-block mb-3">Unidades vehiculares que pasaron por taller</small>
            <a href="vehiculos.php" class="btn btn-sm btn-outline-info mt-auto w-100">
                <i class="fas fa-car-side me-1"></i> Ver Reporte Detallado
            </a>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card card-kpi h-100 p-3 border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-bold">Reparaciones Pendientes</span>
                <div class="kpi-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <h2 class="fw-bold text-dark mb-1"><?= number_format($totalReparacionesPendientes) ?></h2>
            <small class="text-muted d-block mb-3">Órdenes en diagnóstico o en proceso</small>
            <a href="reparaciones.php" class="btn btn-sm btn-outline-warning text-dark mt-auto w-100">
                <i class="fas fa-tasks me-1"></i> Ver Reporte Detallado
            </a>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card card-kpi h-100 p-3 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small text-uppercase fw-bold">Ingresos Obtenidos</span>
                <div class="kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <h2 class="fw-bold text-dark mb-1"><?= formatCurrency($totalIngresos) ?></h2>
            <small class="text-muted d-block mb-3">Efectivo, Tarjetas y Yape</small>
            <a href="ingresos.php" class="btn btn-sm btn-outline-success mt-auto w-100">
                <i class="fas fa-chart-pie me-1"></i> Ver Reporte Detallado
            </a>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-chart-pie text-success me-2"></i> Ingresos por Medio de Pago (Efectivo, Tarjetas, Yape)
                </h6>
                <a href="ingresos.php" class="small text-decoration-none">Ver más &rarr;</a>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 280px;">
                <canvas id="chartIngresosMetodo" style="max-height: 260px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-chart-bar text-primary me-2"></i> Marcas de Vehículos Más Atendidas
                </h6>
                <a href="vehiculos.php" class="small text-decoration-none">Ver más &rarr;</a>
            </div>
            <div class="card-body p-4 d-flex align-items-center justify-content-center" style="min-height: 280px;">
                <canvas id="chartMarcas" style="max-height: 260px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-medal text-warning me-2"></i> Top Servicios Más Solicitados
                </h6>
                <a href="servicios.php" class="small text-decoration-none">Ver reporte completo &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Servicio</th>
                                <th>Categoría</th>
                                <th class="text-center">Ejecuciones</th>
                                <th class="text-end">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topServicios as $ts): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($ts['nombre']) ?></strong></td>
                                    <td><span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($ts['categoria']) ?></span></td>
                                    <td class="text-center"><span class="badge bg-dark rounded-pill px-2 py-1"><?= $ts['total_ejecutado'] ?></span></td>
                                    <td class="text-end fw-bold text-success"><?= formatCurrency($ts['ingresos_generados']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-tools text-danger me-2"></i> Reparaciones Activas en el Taller
                </h6>
                <a href="reparaciones.php" class="small text-decoration-none">Ver estado de bahías &rarr;</a>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-3">
                    Monitoreo en tiempo real de vehículos en bahías mecánicas, diagnósticos y tiempos de entrega estimada.
                </p>
                <div class="p-3 bg-light rounded-3 d-flex justify-content-around text-center">
                    <div>
                        <h4 class="fw-bold text-warning mb-0"><?= $totalReparacionesPendientes ?></h4>
                        <small class="text-muted">Activas</small>
                    </div>
                    <div class="border-start ps-3">
                        <h4 class="fw-bold text-success mb-0"><?= $pdo->query("SELECT COUNT(*) FROM ordenes_trabajo WHERE estado = 'Finalizada'")->fetchColumn() ?></h4>
                        <small class="text-muted">Finalizadas</small>
                    </div>
                    <div class="border-start ps-3">
                        <h4 class="fw-bold text-dark mb-0"><?= $pdo->query("SELECT COUNT(*) FROM ordenes_trabajo WHERE estado = 'Entregada'")->fetchColumn() ?></h4>
                        <small class="text-muted">Entregadas</small>
                    </div>
                </div>
                <div class="mt-3 text-center">
                    <a href="<?= BASE_URL ?>/modules/ordenes/index.php" class="btn btn-sm btn-motor-primary px-4">
                        <i class="fas fa-clipboard-list me-1"></i> Ir al Tablero de Órdenes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxIngresos = document.getElementById('chartIngresosMetodo');
    if (ctxIngresos) {
        new Chart(ctxIngresos, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($ingresosMetodo)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($ingresosMetodo)) ?>,
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

    const ctxMarcas = document.getElementById('chartMarcas');
    if (ctxMarcas) {
        const marcasLabels = <?= json_encode(array_column($topMarcas, 'marca')) ?>;
        const marcasData = <?= json_encode(array_column($topMarcas, 'total_atenciones')) ?>;

        new Chart(ctxMarcas, {
            type: 'bar',
            data: {
                labels: marcasLabels,
                datasets: [{
                    label: 'Atenciones en Taller',
                    data: marcasData,
                    backgroundColor: '#e63946',
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
