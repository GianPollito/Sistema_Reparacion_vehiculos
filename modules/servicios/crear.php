<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$stmtCod = $pdo->prepare("SELECT COUNT(*) FROM servicios");
$stmtCod->execute();
$count = $stmtCod->fetchColumn() + 1;
$nuevoCodigo = 'SRV-' . str_pad($count, 3, '0', STR_PAD_LEFT);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? $nuevoCodigo);
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $tiempo_estimado = trim($_POST['tiempo_estimado'] ?? '');

    if (empty($codigo) || empty($nombre) || empty($categoria) || $precio <= 0) {
        $error = 'Por favor complete el código, nombre, categoría y un precio válido mayor a 0.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO servicios (codigo, nombre, categoria, descripcion, precio, tiempo_estimado) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$codigo, $nombre, $categoria, $descripcion, $precio, $tiempo_estimado]);

            setFlash('success', 'Servicio "' . htmlspecialchars($nombre) . '" creado satisfactoriamente.');
            header('Location: ' . BASE_URL . '/modules/servicios/index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error al registrar servicio: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Nuevo Servicio de Reparación';
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-plus text-primary me-2"></i> Nuevo Servicio de Reparación
                    </h5>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver al Catálogo
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
                            <label class="form-label fw-semibold">Nombre del Servicio *</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Cambio de Pastillas de Freno" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Categoría *</label>
                            <input type="text" name="categoria" class="form-control" list="listaCategorias" placeholder="Seleccione o escriba..." required value="<?= htmlspecialchars($_POST['categoria'] ?? '') ?>">
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
                            <input type="number" step="0.01" min="1" name="precio" class="form-control fw-bold text-success" placeholder="150.00" required value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tiempo Estimado</label>
                            <input type="text" name="tiempo_estimado" class="form-control" placeholder="Ej. 1.5 horas" value="<?= htmlspecialchars($_POST['tiempo_estimado'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Descripción del Servicio Técnico</label>
                            <textarea name="descripcion" rows="3" class="form-control" placeholder="Detalle qué incluye el servicio, procedimientos técnicos y garantías..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-motor-accent px-4 shadow-sm">
                            <i class="fas fa-save me-1"></i> Guardar Servicio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
