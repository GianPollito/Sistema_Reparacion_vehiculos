<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/modules/servicios/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM servicios WHERE id = ?");
$stmt->execute([$id]);
$servicio = $stmt->fetch();

if (!$servicio) {
    setFlash('danger', 'Servicio no encontrado.');
    header('Location: ' . BASE_URL . '/modules/servicios/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $tiempo_estimado = trim($_POST['tiempo_estimado'] ?? '');

    if (empty($codigo) || empty($nombre) || empty($categoria) || $precio <= 0) {
        $error = 'Por favor complete todos los datos requeridos.';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE servicios SET codigo = ?, nombre = ?, categoria = ?, descripcion = ?, precio = ?, tiempo_estimado = ? WHERE id = ?");
            $updateStmt->execute([$codigo, $nombre, $categoria, $descripcion, $precio, $tiempo_estimado, $id]);

            setFlash('success', 'Servicio actualizado satisfactoriamente.');
            header('Location: ' . BASE_URL . '/modules/servicios/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar servicio: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Editar Servicio: ' . $servicio['nombre'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-edit text-primary me-2"></i> Editar Servicio
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
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Código *</label>
                            <input type="text" name="codigo" class="form-control font-monospace fw-bold" required value="<?= htmlspecialchars($servicio['codigo']) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nombre del Servicio *</label>
                            <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($servicio['nombre']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Categoría *</label>
                            <input type="text" name="categoria" class="form-control" list="listaCategorias" required value="<?= htmlspecialchars($servicio['categoria']) ?>">
                            <datalist id="listaCategorias">
                                <option value="Motor">
                                <option value="Frenos">
                                <option value="Mantenimiento">
                                <option value="Eléctrico">
                                <option value="Suspensión">
                                <option value="Aire Acondicionado">
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Precio / Tarifa (S/.) *</label>
                            <input type="number" step="0.01" min="1" name="precio" class="form-control fw-bold text-success" required value="<?= htmlspecialchars($servicio['precio']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tiempo Estimado</label>
                            <input type="text" name="tiempo_estimado" class="form-control" value="<?= htmlspecialchars($servicio['tiempo_estimado'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción</label>
                            <textarea name="descripcion" rows="3" class="form-control"><?= htmlspecialchars($servicio['descripcion'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-primary px-4 shadow-sm">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Servicio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
