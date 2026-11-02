<?php
?>
    </div>
</main>

<footer class="footer-motor">
    <div class="container-fluid px-3 px-lg-4">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="brand-icon" style="width: 36px; height: 36px; font-size: 1.1rem;">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h5 class="m-0 text-white fw-bold">MOTOR EXPRESS</h5>
                </div>
                <p class="small text-muted mb-3">
                    Sistema integral para la modernización administrativa y operativa del taller automotriz. Gestión eficiente de clientes, vehículos, mecánicos, órdenes de trabajo, diagnósticos computarizados y control de inventario de repuestos.
                </p>
                <div class="d-flex gap-2">
                    <span class="badge bg-danger"><i class="fas fa-check-double me-1"></i> Consultas SQL Preparadas</span>
                    <span class="badge bg-primary"><i class="fas fa-shield-alt me-1"></i> Sesiones Seguras</span>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h5>Módulos</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL ?>/modules/clientes/index.php"><i class="fas fa-angle-right me-1 text-danger"></i> Clientes</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/modules/vehiculos/index.php"><i class="fas fa-angle-right me-1 text-danger"></i> Vehículos</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/modules/mecanicos/index.php"><i class="fas fa-angle-right me-1 text-danger"></i> Mecánicos</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/modules/citas/index.php"><i class="fas fa-angle-right me-1 text-danger"></i> Citas Taller</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/modules/ordenes/index.php"><i class="fas fa-angle-right me-1 text-danger"></i> Órdenes de Trabajo</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <h5>Taller &amp; Pagos</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?= BASE_URL ?>/modules/diagnosticos/index.php"><i class="fas fa-angle-right me-1 text-danger"></i> Diagnósticos Mecánicos</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/modules/servicios/index.php"><i class="fas fa-angle-right me-1 text-danger"></i> Catálogo de Servicios</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/modules/repuestos/index.php"><i class="fas fa-angle-right me-1 text-danger"></i> Control de Repuestos</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/modules/pagos/index.php"><i class="fas fa-angle-right me-1 text-danger"></i> Pagos (Efectivo, Tarjetas, Yape)</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/modules/reportes/index.php"><i class="fas fa-angle-right me-1 text-danger"></i> Reportes Estadísticos</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Ubicación &amp; Contacto</h5>
                <ul class="list-unstyled small">
                    <li class="mb-2 text-muted">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i> <?= APP_ADDRESS ?>
                    </li>
                    <li class="mb-2 text-muted">
                        <i class="fas fa-phone-alt text-success me-2"></i> <?= APP_PHONE ?>
                    </li>
                    <li class="mb-2 text-muted">
                        <i class="fas fa-envelope text-info me-2"></i> <?= APP_EMAIL ?>
                    </li>
                    <li class="mb-2 text-muted">
                        <i class="far fa-clock text-warning me-2"></i> <?= APP_HOURS ?>
                    </li>
                </ul>
                <div class="mt-3 p-2 bg-dark rounded border border-secondary small">
                    <span class="text-white fw-bold"><i class="fas fa-graduation-cap text-warning me-1"></i> SENATI</span>
                    <span class="text-muted d-block" style="font-size:0.75rem;">Ingeniería de Software / Desarrollo Web</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 text-muted">
            <div>
                &copy; <?= date('Y') ?> <strong><?= APP_NAME ?></strong> - Todos los derechos reservados.
            </div>
            <div>
                Diseño Adaptable para PC, Tablet y Celular &bull; <span class="text-light">Motor Express v1.0</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function confirmDelete(message = '¿Está seguro de que desea eliminar este registro?') {
        return confirm(message);
    }
</script>
</body>
</html>
