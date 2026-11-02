<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni_ruc = trim($_POST['dni_ruc'] ?? '');
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if (empty($dni_ruc) || empty($nombres) || empty($apellidos)) {
        $error = 'Por favor complete los campos obligatorios (DNI/RUC, Nombres y Apellidos).';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO clientes (dni_ruc, nombres, apellidos, telefono, email, direccion) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$dni_ruc, $nombres, $apellidos, $telefono, $email, $direccion]);

            setFlash('success', 'Cliente "' . htmlspecialchars($nombres . ' ' . $apellidos) . '" registrado exitosamente.');
            header('Location: ' . BASE_URL . '/modules/clientes/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al registrar cliente: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Nuevo Cliente';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-user-plus text-primary me-2"></i> Registrar Nuevo Cliente
                    </h5>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver a la lista
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
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">DNI o RUC *</label>
                            <input type="text" name="dni_ruc" class="form-control" placeholder="Ej. 47895214" required value="<?= htmlspecialchars($_POST['dni_ruc'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nombres *</label>
                            <input type="text" name="nombres" class="form-control" placeholder="Ej. Juan Carlos" required value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Apellidos *</label>
                            <input type="text" name="apellidos" class="form-control" placeholder="Ej. Pérez Quispe" required value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono / Celular</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Ej. 987 654 321" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="cliente@correo.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección de Domicilio / Oficina</label>
                            <input type="text" name="direccion" class="form-control" placeholder="Ej. Av. Los Próceres 450, Santiago de Surco" value="<?= htmlspecialchars($_POST['direccion'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-accent px-4 shadow-sm">
                            <i class="fas fa-save me-1"></i> Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
