<?php

require_once __DIR__ . '/../config/config.php';

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/modules/auth/login.php');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'nombre' => $_SESSION['user_nombre'] ?? 'Usuario',
        'email' => $_SESSION['user_email'] ?? '',
        'rol' => $_SESSION['user_rol'] ?? 'Invitado'
    ];
}

function hasRole($allowedRoles = []) {
    if (!isLoggedIn()) {
        return false;
    }
    if (empty($allowedRoles)) {
        return true;
    }
    if (is_string($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    return in_array($_SESSION['user_rol'] ?? '', $allowedRoles);
}
