<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Por favor ingresa tu correo electrónico y contraseña.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_rol'] = $user['rol'];

                setFlash('success', '¡Bienvenido de nuevo, ' . htmlspecialchars($user['nombre']) . '!');
                $redirect = $_SESSION['redirect_after_login'] ?? (BASE_URL . '/index.php');
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Credenciales incorrectas. Verifica tu correo y contraseña.';
            }
        } catch (PDOException $e) {
            $error = 'Error en el sistema: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Iniciar Sesión';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-md-8 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #0b3b60 0%, #1e293b 100%);">
                <div class="brand-icon mx-auto mb-3" style="width: 55px; height: 55px; font-size: 1.6rem;">
                    <i class="fas fa-lock"></i>
                </div>
                <h4 class="fw-bold mb-1">Acceso a Motor Express</h4>
                <p class="text-white-50 small mb-0">Sistema de Control y Gestión del Taller Automotriz</p>
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
                            <i class="fas fa-envelope text-primary me-1"></i> Correo Electrónico
                        </label>
                        <input type="email" name="email" id="inputEmail" class="form-control form-control-lg fs-6" 
                               placeholder="ejemplo@motorexpress.pe" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">
                            <i class="fas fa-key text-primary me-1"></i> Contraseña
                        </label>
                        <input type="password" name="password" id="inputPassword" class="form-control form-control-lg fs-6" 
                               placeholder="Ingresa tu contraseña" required>
                    </div>

                    <button type="submit" class="btn btn-motor-primary w-100 py-2 fs-6 fw-bold shadow-sm">
                        <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-center">
                    <p class="text-muted small mb-2">¿No tienes una cuenta aún?</p>
                    <a href="<?= BASE_URL ?>/modules/auth/register.php" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="fas fa-user-plus me-1"></i> Registrar Nuevo Usuario
                    </a>
                </div>

                <div class="mt-4 p-3 bg-light rounded-3 border">
                    <small class="fw-bold text-dark d-block mb-2">
                        <i class="fas fa-key text-warning me-1"></i> Cuentas Demo de Prueba (Clic para autocompletar):
                    </small>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setDemo('admin@motorexpress.pe', 'admin123')">
                            <i class="fas fa-user-shield me-1"></i> Admin
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDemo('carlos@motorexpress.pe', 'admin123')">
                            <i class="fas fa-wrench me-1"></i> Mecánico
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="setDemo('valeria@motorexpress.pe', 'admin123')">
                            <i class="fas fa-headset me-1"></i> Recepción
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setDemo(email, pass) {
    document.getElementById('inputEmail').value = email;
    document.getElementById('inputPassword').value = pass;
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
