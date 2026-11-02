<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

checkAuth();
$pdo = getDBConnection();

$id = intval($_GET['id'] ?? 0);
$estado = trim($_GET['estado'] ?? '');

$validEstados = ['Pendiente', 'Confirmada', 'En Atención', 'Completada', 'Cancelada'];

if ($id > 0 && in_array($estado, $validEstados)) {
    try {
        $stmt = $pdo->prepare("UPDATE citas SET estado = ? WHERE id = ?");
        $stmt->execute([$estado, $id]);
        setFlash('success', "Estado de la cita actualizado a: $estado");
    } catch (PDOException $e) {
        setFlash('danger', 'Error al cambiar estado: ' . $e->getMessage());
    }
}

header('Location: ' . BASE_URL . '/modules/citas/index.php');
exit;
