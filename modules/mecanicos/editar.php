<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/modules/mecanicos/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM mecanicos WHERE id = ?");
$stmt->execute([$id]);
$mecanico = $stmt->fetch();

if (!$mecanico) {
    setFlash('danger', 'Mecánico no encontrado.');
    header('Location: ' . BASE_URL . '/modules/mecanicos/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni'] ?? '');
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $estado = trim($_POST['estado'] ?? 'Activo');

    if (empty($dni) || empty($nombre_completo) || empty($especialidad)) {
        $error = 'Por favor complete todos los campos obligatorios.';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE mecanicos SET dni = ?, nombre_completo = ?, especialidad = ?, telefono = ?, email = ?, estado = ? WHERE id = ?");
            $updateStmt->execute([$dni, $nombre_completo, $especialidad, $telefono, $email, $estado, $id]);

            setFlash('success', 'Mecánico actualizado satisfactoriamente.');
            header('Location: ' . BASE_URL . '/modules/mecanicos/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Editar Mecánico: ' . $mecanico['nombre_completo'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-user-edit text-primary me-2"></i> Editar Mecánico
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
                            <input type="text" name="dni" class="form-control" required value="<?= htmlspecialchars($mecanico['dni']) ?>">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Nombre Completo *</label>
                            <input type="text" name="nombre_completo" class="form-control" required value="<?= htmlspecialchars($mecanico['nombre_completo']) ?>">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Especialidad *</label>
                            <input type="text" name="especialidad" class="form-control" list="listaEspecialidades" required value="<?= htmlspecialchars($mecanico['especialidad']) ?>">
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
                                <option value="Activo" <?= ($mecanico['estado'] === 'Activo') ? 'selected' : '' ?>>Activo</option>
                                <option value="Inactivo" <?= ($mecanico['estado'] === 'Inactivo') ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($mecanico['telefono'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($mecanico['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-primary px-4 shadow-sm">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Mecánico
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
