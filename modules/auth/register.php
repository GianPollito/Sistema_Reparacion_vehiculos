<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = trim($_POST['rol'] ?? 'Recepcionista');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Por favor complete todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no tiene un formato válido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $password_confirm) {
        $error = 'Las contraseñas ingresadas no coinciden.';
    } else {
        try {
            $pdo = getDBConnection();

            $checkStmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                $error = 'El correo electrónico ya se encuentra registrado.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $insertStmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
                $insertStmt->execute([$nombre, $email, $hash, $rol]);

                setFlash('success', '¡Usuario registrado correctamente! Ahora puedes iniciar sesión.');
                header('Location: ' . BASE_URL . '/modules/auth/login.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Error en el registro: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Registro de Usuario';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #0b3b60 0%, #1e293b 100%);">
                <div class="brand-icon mx-auto mb-3" style="width: 55px; height: 55px; font-size: 1.6rem;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h4 class="fw-bold mb-1">Registro de Usuario</h4>
                <p class="text-white-50 small mb-0">Alta de personal para el taller Motor Express</p>
            </div>

            <div class="card-body p-4 p-md-5 bg-white">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-circle me-2 fs-5"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">
                            <i class="fas fa-user text-primary me-1"></i> Nombre Completo *
                        </label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Juan Pérez Ramos" 
                               required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">
                            <i class="fas fa-envelope text-primary me-1"></i> Correo Electrónico *
                        </label>
                        <input type="email" name="email" class="form-control" placeholder="correo@motorexpress.pe" 
                               required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">
                            <i class="fas fa-id-badge text-primary me-1"></i> Rol en el Taller *
                        </label>
                        <select name="rol" class="form-select" required>
                            <option value="Administrador" <?= (($_POST['rol'] ?? '') === 'Administrador') ? 'selected' : '' ?>>Administrador (Control total)</option>
                            <option value="Recepcionista" <?= (($_POST['rol'] ?? '') === 'Recepcionista') ? 'selected' : 'selected' ?>>Recepcionista (Atención, Citas y Cobros)</option>
                            <option value="Mecanico" <?= (($_POST['rol'] ?? '') === 'Mecanico') ? 'selected' : '' ?>>Mecánico (Órdenes, Diagnósticos y Repuestos)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fas fa-lock text-primary me-1"></i> Contraseña *
                            </label>
                            <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">
                                <i class="fas fa-check-circle text-primary me-1"></i> Confirmar Contraseña *
                            </label>
                            <input type="password" name="password_confirm" class="form-control" placeholder="Repite la contraseña" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-motor-accent w-100 py-2 fs-6 fw-bold shadow-sm">
                        <i class="fas fa-save me-1"></i> Completar Registro
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-center">
                    <p class="text-muted small mb-2">¿Ya tienes una cuenta registrada?</p>
                    <a href="<?= BASE_URL ?>/modules/auth/login.php" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión Aquí
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
