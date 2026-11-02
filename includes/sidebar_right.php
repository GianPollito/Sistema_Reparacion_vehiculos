<?php

$user = currentUser();
?>
<div class="offcanvas offcanvas-end offcanvas-right-menu shadow-lg" tabindex="-1" id="offcanvasRightMenu" aria-labelledby="offcanvasRightMenuLabel">
    <div class="offcanvas-header offcanvas-right-header">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-bars-staggered fs-5 text-warning"></i>
            <h5 class="offcanvas-title fw-bold m-0" id="offcanvasRightMenuLabel">Panel Rápido Taller</h5>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    
    <div class="offcanvas-body p-3 bg-light">
        <?php if ($user): ?>
            <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-user-shield fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($user['nombre']) ?></h6>
                            <small class="text-muted d-block"><?= htmlspecialchars($user['email']) ?></small>
                            <span class="badge bg-danger rounded-pill px-2 py-1 mt-1"><?= htmlspecialchars($user['rol']) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <span class="text-uppercase fw-bold text-muted small px-1 d-block mb-2">
                    <i class="fas fa-bolt text-warning me-1"></i> Accesos Rápidos
                </span>
                
                <a href="<?= BASE_URL ?>/modules/ordenes/crear.php" class="quick-action-card">
                    <div class="quick-icon bg-danger text-white">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="d-block small">Nueva Orden de Trabajo</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">Ingreso de vehículo al taller</span>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="<?= BASE_URL ?>/modules/citas/crear.php" class="quick-action-card">
                    <div class="quick-icon bg-primary text-white">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="d-block small">Programar Cita</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">Agendar mantenimiento</span>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="<?= BASE_URL ?>/modules/clientes/crear.php" class="quick-action-card">
                    <div class="quick-icon bg-success text-white">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="d-block small">Registrar Cliente</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">Nuevo propietario</span>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="<?= BASE_URL ?>/modules/vehiculos/crear.php" class="quick-action-card">
                    <div class="quick-icon bg-info text-white">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="d-block small">Registrar Vehículo</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">Placa, marca y modelo</span>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="<?= BASE_URL ?>/modules/diagnosticos/crear.php" class="quick-action-card">
                    <div class="quick-icon bg-warning text-dark">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="d-block small">Registrar Diagnóstico</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">Informe de fallas mecánicas</span>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>

                <a href="<?= BASE_URL ?>/modules/pagos/crear.php" class="quick-action-card">
                    <div class="quick-icon bg-dark text-white">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="d-block small">Cobro / Registrar Pago</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">Efectivo, Tarjetas o Yape</span>
                    </div>
                    <i class="fas fa-chevron-right text-muted small"></i>
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white mb-3">
                <span class="text-uppercase fw-bold text-muted small d-block mb-2">
                    <i class="fas fa-shield-alt text-success me-1"></i> Seguridad &amp; Red
                </span>
                <ul class="list-unstyled small mb-0">
                    <li class="py-1 d-flex justify-content-between border-bottom">
                        <span class="text-muted">Consultas SQL:</span>
                        <span class="badge bg-success-subtle text-success fw-bold">PDO Preparadas</span>
                    </li>
                    <li class="py-1 d-flex justify-content-between border-bottom">
                        <span class="text-muted">Puerto Web:</span>
                        <span class="badge bg-primary-subtle text-primary fw-bold"><?= htmlspecialchars($_SERVER['SERVER_PORT'] ?? '8095') ?></span>
                    </li>
                    <li class="py-1 d-flex justify-content-between">
                        <span class="text-muted">Versión PHP:</span>
                        <span class="fw-semibold"><?= PHP_VERSION ?></span>
                    </li>
                </ul>
            </div>

            <div class="d-grid gap-2 pt-2">
                <a href="<?= BASE_URL ?>/modules/auth/logout.php" class="btn btn-outline-danger btn-sm py-2">
                    <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                </a>
            </div>

        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-lock fs-1 text-muted mb-3"></i>
                <h6>Acceso al Sistema</h6>
                <p class="text-muted small">Debes iniciar sesión para usar el panel del taller.</p>
                <a href="<?= BASE_URL ?>/modules/auth/login.php" class="btn btn-motor-accent btn-sm px-4">
                    Iniciar Sesión
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
