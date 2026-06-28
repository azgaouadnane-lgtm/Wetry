<?php
/**
 * api/click.php
 * Records a click event: country + date.
 * Called via navigator.sendBeacon() from frontend (POST).
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']);
    exit;
}

require_once dirname(__DIR__) . '/config.php';
require_once ROOT_DIR . '/includes/functions.php';
require_once ROOT_DIR . '/includes/geo.php';

try {
    // Accept JSON body or form data
    $input = json_decode(file_get_contents('php://input'), true);
    $country = '';

    if (!empty($input['country'])) {
        $country = strtoupper(preg_replace('/[^A-Z]/i', '', $input['country']));
    }

    $appId   = !empty($input['app_id']) ? (int)$input['app_id'] : null;

    // If no country was sent, detect server-side
    if (strlen($country) !== 2) {
        $country = getUserCountry();
    }

    recordClick($country, $appId);

    echo json_encode(['ok' => true]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to record click']);
}
