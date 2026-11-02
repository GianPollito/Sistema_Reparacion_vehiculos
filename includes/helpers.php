<?php

require_once __DIR__ . '/../config/config.php';

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format((float)$amount, 2, '.', ',');
}

function formatDate($date, $withTime = false) {
    if (empty($date)) return '-';
    $timestamp = strtotime($date);
    if (!$timestamp) return '-';
    return $withTime ? date('d/m/Y h:i A', $timestamp) : date('d/m/Y', $timestamp);
}

function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function renderFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = 'info-circle';
        if ($flash['type'] === 'success') $icon = 'check-circle';
        if ($flash['type'] === 'danger') $icon = 'exclamation-circle';
        if ($flash['type'] === 'warning') $icon = 'exclamation-triangle';

        return '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show shadow-sm d-flex align-items-center" role="alert">
            <i class="fas fa-' . $icon . ' me-2 fs-5"></i>
            <div>' . $flash['message'] . '</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>';
    }
    return '';
}

function statusBadge($status) {
    $map = [
        'Pendiente' => 'bg-warning text-dark',
        'En Diagnóstico' => 'bg-info text-dark',
        'En Proceso' => 'bg-primary text-white',
        'Esperando Repuestos' => 'bg-secondary text-white',
        'Finalizada' => 'bg-success text-white',
        'Entregada' => 'bg-dark text-white',
        'Cancelada' => 'bg-danger text-white',
        'Confirmada' => 'bg-primary text-white',
        'En Atención' => 'bg-info text-dark',
        'Completada' => 'bg-success text-white',
        'Activo' => 'bg-success text-white',
        'Inactivo' => 'bg-secondary text-white',
        'Baja' => 'bg-info text-dark',
        'Media' => 'bg-warning text-dark',
        'Alta' => 'bg-danger text-white',
        'Crítica' => 'bg-danger text-white'
    ];

    $cls = $map[$status] ?? 'bg-secondary text-white';
    return '<span class="badge ' . $cls . ' px-2 py-1"><i class="fas fa-circle me-1" style="font-size:0.5rem; vertical-align:middle;"></i>' . htmlspecialchars($status) . '</span>';
}
