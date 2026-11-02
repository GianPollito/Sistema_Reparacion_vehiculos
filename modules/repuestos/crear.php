<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$stmtCod = $pdo->prepare("SELECT COUNT(*) FROM repuestos");
$stmtCod->execute();
$count = $stmtCod->fetchColumn() + 1;
$nuevoCodigo = 'REP-' . str_pad($count, 3, '0', STR_PAD_LEFT);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? $nuevoCodigo);
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $precio_unitario = floatval($_POST['precio_unitario'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $stock_minimo = intval($_POST['stock_minimo'] ?? 5);

    if (empty($codigo) || empty($nombre) || empty($categoria) || $precio_unitario <= 0) {
        $error = 'Por favor complete todos los datos obligatorios (Código, Nombre, Categoría y Precio válido).';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO repuestos (codigo, nombre, categoria, marca, precio_unitario, stock, stock_minimo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$codigo, $nombre, $categoria, $marca, $precio_unitario, $stock, $stock_minimo]);

            setFlash('success', 'Repuesto "' . htmlspecialchars($nombre) . '" registrado exitosamente en el inventario.');
            header('Location: ' . BASE_URL . '/modules/repuestos/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al registrar repuesto: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Nuevo Repuesto';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-boxes text-primary me-2"></i> Registrar Nuevo Repuesto / Insumo
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
                            <input type="text" name="codigo" class="form-control font-monospace fw-bold" required value="<?= htmlspecialchars($_POST['codigo'] ?? $nuevoCodigo) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nombre del Repuesto *</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Pastillas de Freno Cerámicas Delanteras" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Categoría *</label>
                            <input type="text" name="categoria" class="form-control" list="listaCategoriasRep" placeholder="Seleccione o escriba..." required value="<?= htmlspecialchars($_POST['categoria'] ?? '') ?>">
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
                            <input type="text" name="marca" class="form-control" placeholder="Ej. Bosch, Brembo, Mobil 1, Mann Filter" value="<?= htmlspecialchars($_POST['marca'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Precio Unitario (S/.) *</label>
                            <input type="number" step="0.01" min="0.10" name="precio_unitario" class="form-control fw-bold text-primary" placeholder="185.00" required value="<?= htmlspecialchars($_POST['precio_unitario'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Inicial (unid.) *</label>
                            <input type="number" min="0" name="stock" class="form-control" placeholder="10" required value="<?= htmlspecialchars($_POST['stock'] ?? '10') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Stock Mínimo de Alerta</label>
                            <input type="number" min="1" name="stock_minimo" class="form-control" placeholder="4" value="<?= htmlspecialchars($_POST['stock_minimo'] ?? '5') ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-accent px-4 shadow-sm">
                            <i class="fas fa-save me-1"></i> Guardar Repuesto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
