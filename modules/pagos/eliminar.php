<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM pagos WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Registro de pago anulado/eliminado correctamente.');
    } catch (PDOException $e) {
        setFlash('danger', 'Error al eliminar pago: ' . $e->getMessage());
    }
}

header('Location: ' . BASE_URL . '/modules/pagos/index.php');
exit;
