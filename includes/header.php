<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

$user = currentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$currentPort = $_SERVER['SERVER_PORT'] ?? '8095';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' . APP_NAME : APP_NAME . ' - ' . APP_TAGLINE ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
</head>
<body>

<header class="top-header-level1">
    <div class="container-fluid px-3 px-lg-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <span class="d-none d-md-inline">
                    <i class="fas fa-map-marker-alt text-danger me-1"></i> <?= APP_ADDRESS ?>
                </span>
                <span>
                    <i class="fas fa-phone-alt text-success me-1"></i> <?= APP_PHONE ?>
                </span>
                <span class="d-none d-lg-inline">
                    <i class="far fa-clock text-warning me-1"></i> <?= APP_HOURS ?>
                </span>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-dark border border-secondary text-light px-2 py-1">
                    <i class="fas fa-network-wired text-info me-1"></i> Puerto: <strong class="text-warning"><?= htmlspecialchars($currentPort) ?></strong>
                </span>

                <?php if ($user): ?>
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-none d-sm-inline">
                            <i class="fas fa-user-circle text-info me-1"></i> <strong><?= htmlspecialchars($user['nombre']) ?></strong>
                            <span class="badge bg-danger ms-1"><?= htmlspecialchars($user['rol']) ?></span>
                        </span>
                        
                        <button class="btn btn-sm btn-outline-light d-flex align-items-center gap-1 py-1 px-2" 
                                type="button" 
                                data-bs-toggle="offcanvas" 
                                data-bs-target="#offcanvasRightMenu" 
                                aria-controls="offcanvasRightMenu"
                                title="Abrir Menú Rápido y Panel Lateral Derecho">
                            <i class="fas fa-sliders-h"></i>
                            <span class="d-none d-md-inline">Menú Derecho</span>
                        </button>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/modules/auth/login.php" class="btn btn-sm btn-outline-light py-1 px-2">
                        <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<nav class="navbar navbar-expand-xl navbar-light main-header-level2">
    <div class="container-fluid px-3 px-lg-4">
        <a class="brand-logo-wrap" href="<?= BASE_URL ?>/index.php">
            <div class="brand-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="brand-text">
                <h1>MOTOR EXPRESS</h1>
                <span>GESTIÓN DE TALLER AUTOMOTRIZ</span>
            </div>
        </a>

        <div class="d-flex align-items-center gap-2 d-xl-none">
            <?php if ($user): ?>
                <button class="btn btn-sm btn-danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRightMenu">
                    <i class="fas fa-bars-staggered"></i>
                </button>
            <?php endif; ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto mb-2 mb-xl-0 navbar-motor align-items-xl-center">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage === 'index.php' && $currentDir !== 'clientes' && $currentDir !== 'vehiculos' && $currentDir !== 'mecanicos' && $currentDir !== 'citas' && $currentDir !== 'ordenes' && $currentDir !== 'diagnosticos' && $currentDir !== 'servicios' && $currentDir !== 'repuestos' && $currentDir !== 'pagos' && $currentDir !== 'reportes') ? 'active' : '' ?>" href="<?= BASE_URL ?>/index.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentDir === 'clientes') ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/clientes/index.php">
                        <i class="fas fa-users"></i> Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentDir === 'vehiculos') ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/vehiculos/index.php">
                        <i class="fas fa-car-side"></i> Vehículos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentDir === 'mecanicos') ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/mecanicos/index.php">
                        <i class="fas fa-user-cog"></i> Mecánicos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentDir === 'citas') ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/citas/index.php">
                        <i class="fas fa-calendar-check"></i> Citas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentDir === 'ordenes') ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/ordenes/index.php">
                        <i class="fas fa-clipboard-list"></i> Órdenes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentDir === 'diagnosticos') ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/diagnosticos/index.php">
                        <i class="fas fa-stethoscope"></i> Diagnósticos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentDir === 'servicios') ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/servicios/index.php">
                        <i class="fas fa-wrench"></i> Servicios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentDir === 'repuestos') ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/repuestos/index.php">
                        <i class="fas fa-cogs"></i> Repuestos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentDir === 'pagos') ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/pagos/index.php">
                        <i class="fas fa-cash-register"></i> Pagos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentDir === 'reportes') ? 'active' : '' ?>" href="<?= BASE_URL ?>/modules/reportes/index.php">
                        <i class="fas fa-chart-line"></i> Reportes
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 ms-xl-3 mt-3 mt-xl-0">
                <a href="<?= BASE_URL ?>/modules/ordenes/crear.php" class="btn btn-motor-accent btn-sm px-3 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-plus me-1"></i> Nueva Orden
                </a>
            </div>
        </div>
    </div>
</nav>

<?php require_once __DIR__ . '/sidebar_right.php'; ?>

<main class="main-content py-4">
    <div class="container-fluid px-3 px-lg-4">
        <?= renderFlash() ?>
