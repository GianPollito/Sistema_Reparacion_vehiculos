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
        $stmt = $pdo->prepare("DELETE FROM repuestos WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Repuesto eliminado del inventario.');
    } catch (PDOException $e) {
        setFlash('danger', 'Error al eliminar repuesto: ' . $e->getMessage());
    }
}

header('Location: ' . BASE_URL . '/modules/repuestos/index.php');
exit;
