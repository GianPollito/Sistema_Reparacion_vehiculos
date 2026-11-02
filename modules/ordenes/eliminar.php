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
        $stmtR = $pdo->prepare("SELECT repuesto_id, cantidad FROM repuestos_utilizados WHERE orden_id = ?");
        $stmtR->execute([$id]);
        $repuestos = $stmtR->fetchAll();

        foreach ($repuestos as $r) {
            $pdo->prepare("UPDATE repuestos SET stock = stock + ? WHERE id = ?")->execute([$r['cantidad'], $r['repuesto_id']]);
        }

        $stmt = $pdo->prepare("DELETE FROM ordenes_trabajo WHERE id = ?");
        $stmt->execute([$id]);

        setFlash('success', 'Orden de trabajo eliminada y stock de repuestos restaurado.');
    } catch (PDOException $e) {
        setFlash('danger', 'Error al eliminar orden: ' . $e->getMessage());
    }
}

header('Location: ' . BASE_URL . '/modules/ordenes/index.php');
exit;
