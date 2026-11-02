<?php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];
session_destroy();

session_start();
setFlash('info', 'Has cerrado sesión exitosamente en Motor Express.');
header('Location: ' . BASE_URL . '/modules/auth/login.php');
exit;
