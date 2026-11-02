<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni'] ?? '');
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $estado = trim($_POST['estado'] ?? 'Activo');

    if (empty($dni) || empty($nombre_completo) || empty($especialidad)) {
        $error = 'Por favor complete los campos obligatorios (DNI, Nombre Completo y Especialidad).';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO mecanicos (dni, nombre_completo, especialidad, telefono, email, estado) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$dni, $nombre_completo, $especialidad, $telefono, $email, $estado]);

            setFlash('success', 'Mecánico "' . htmlspecialchars($nombre_completo) . '" registrado exitosamente.');
            header('Location: ' . BASE_URL . '/modules/mecanicos/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al registrar mecánico: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Nuevo Mecánico';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-user-plus text-primary me-2"></i> Registrar Nuevo Mecánico
                    </h5>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver
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
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">DNI *</label>
                            <input type="text" name="dni" class="form-control" placeholder="Ej. 41258963" required value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Nombre Completo *</label>
                            <input type="text" name="nombre_completo" class="form-control" placeholder="Ej. Carlos Mendoza Vargas" required value="<?= htmlspecialchars($_POST['nombre_completo'] ?? '') ?>">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Especialidad Técnica *</label>
                            <input type="text" name="especialidad" class="form-control" list="listaEspecialidades" placeholder="Ej. Diagnóstico y Electrónica Automotriz" required value="<?= htmlspecialchars($_POST['especialidad'] ?? '') ?>">
                            <datalist id="listaEspecialidades">
                                <option value="Diagnóstico y Electrónica Automotriz">
                                <option value="Motores y Cajas de Cambio">
                                <option value="Frenos, Dirección y Suspensión">
                                <option value="Mantenimiento Preventivo y Afinamiento">
                                <option value="Aire Acondicionado y Climatización">
                                <option value="Sistemas de Inyección Electrónica">
                            </datalist>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Estado *</label>
                            <select name="estado" class="form-select">
                                <option value="Activo" selected>Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono / Celular</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Ej. 981 234 567" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="mecanico@motorexpress.pe" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-accent px-4 shadow-sm">
                            <i class="fas fa-save me-1"></i> Guardar Mecánico
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
