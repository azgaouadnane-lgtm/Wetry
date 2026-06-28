<?php
require_once dirname(__DIR__) . '/config.php';
require_once ROOT_DIR . '/includes/auth.php';
require_once ROOT_DIR . '/includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $id = (int)($_POST['id'] ?? 0);
    $apps = readApps();
    
    $filtered = array_filter($apps, function($a) use ($id) {
        return (int)$a['id'] !== $id;
    });

    writeApps(array_values($filtered));
}

header('Location: dashboard.php');
exit;
