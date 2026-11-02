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
        $stmt = $pdo->prepare("DELETE FROM mecanicos WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Mecánico eliminado satisfactoriamente.');
    } catch (PDOException $e) {
        setFlash('danger', 'Error al eliminar mecánico: ' . $e->getMessage());
    }
}

header('Location: ' . BASE_URL . '/modules/mecanicos/index.php');
exit;
