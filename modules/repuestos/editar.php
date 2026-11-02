<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/modules/repuestos/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM repuestos WHERE id = ?");
$stmt->execute([$id]);
$repuesto = $stmt->fetch();

if (!$repuesto) {
    setFlash('danger', 'Repuesto no encontrado.');
    header('Location: ' . BASE_URL . '/modules/repuestos/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $precio_unitario = floatval($_POST['precio_unitario'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $stock_minimo = intval($_POST['stock_minimo'] ?? 5);

    if (empty($codigo) || empty($nombre) || empty($categoria) || $precio_unitario <= 0) {
        $error = 'Por favor complete todos los campos obligatorios.';
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE repuestos SET codigo = ?, nombre = ?, categoria = ?, marca = ?, precio_unitario = ?, stock = ?, stock_minimo = ? WHERE id = ?");
            $updateStmt->execute([$codigo, $nombre, $categoria, $marca, $precio_unitario, $stock, $stock_minimo, $id]);

            setFlash('success', 'Repuesto actualizado satisfactoriamente.');
            header('Location: ' . BASE_URL . '/modules/repuestos/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al actualizar repuesto: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Editar Repuesto: ' . $repuesto['nombre'];
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-edit text-primary me-2"></i> Editar Repuesto
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
                            <input type="text" name="codigo" class="form-control font-monospace fw-bold" required value="<?= htmlspecialchars($repuesto['codigo']) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nombre del Repuesto *</label>
                            <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($repuesto['nombre']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Categoría *</label>
                            <input type="text" name="categoria" class="form-control" list="listaCategoriasRep" required value="<?= htmlspecialchars($repuesto['categoria']) ?>">
                            <datalist id="listaCategoriasRep">
                                <option value="Frenos">
                                <option value="Filtros">
                                <option value="Lubricantes">
                                <option value="Encendido">
                                <option value="Suspensión">
                                <option value="Eléctrico">
                                <option value="Motor">
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Marca / Fabricante</label>
                            <input type="text" name="marca" class="form-control" value="<?= htmlspecialchars($repuesto['marca'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Precio Unitario (S/.) *</label>
                            <input type="number" step="0.01" min="0.10" name="precio_unitario" class="form-control fw-bold text-primary" required value="<?= htmlspecialchars($repuesto['precio_unitario']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Actual (unid.) *</label>
                            <input type="number" min="0" name="stock" class="form-control" required value="<?= htmlspecialchars($repuesto['stock']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Mínimo de Alerta</label>
                            <input type="number" min="1" name="stock_minimo" class="form-control" value="<?= htmlspecialchars($repuesto['stock_minimo']) ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-primary px-4 shadow-sm">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Repuesto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
