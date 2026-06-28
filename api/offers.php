<?php
require_once dirname(__DIR__) . '/config.php';
require_once ROOT_DIR . '/includes/functions.php';

// Dynamic API Credentials from Settings
$settings = readSettings();
$network  = $settings['api_network'] ?? 'cpabuild';

// Get user info for targeting
$user_ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

// If localhost, use a fallback public IP for testing (e.g., a US IP)
if ($user_ip === '127.0.0.1' || $user_ip === '::1' || strpos($user_ip, '192.168.') === 0) {
    $user_ip = '104.28.154.54'; // Example US IP
}

header('Content-Type: application/json');

if ($network === 'ogads') {
    $api_key = $settings['ogads_api_key'] ?? '';
    $endpoint = 'https://saveapp.store/api/v2';
    $data = [
        'ip' => $user_ip,
        'user_agent' => $user_agent
    ];
    $apiUrl = $endpoint . '?' . http_build_query($data);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $api_key,
        ],
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(['error' => 'Failed to fetch OGAds offers']);
        exit;
    }

    $content = json_decode($response, true);
    if (!empty($content['success']) && !empty($content['offers'])) {
        $mapped_offers = [];
        foreach ($content['offers'] as $offer) {
            $mapped_offers[] = [
                'name' => $offer['name_short'] ?? $offer['name'],
                'url' => $offer['link'],
                'conversion' => 'Free | Complete to unlock',
                'network_icon' => $offer['picture'] ?? 'verified'
            ];
        }
        echo json_encode($mapped_offers);
    } else {
        echo json_encode([]);
    }
    exit;

} else {
    // CPABuild / CPAGrip logic
    $user_id  = $settings['api_user_id'] ?? "64435";
    $api_key  = $settings['api_key'] ?? "98f80e6a9cf3e9805f7fd7527a0c0919";
    $s2       = $settings['api_s2_param'] ?? "Spidermod";
    $base_url = $settings['api_offer_url'] ?? "https://d5b3uz3fo8hn3.cloudfront.net/public/offers/feed.php";

    $params = [
        'user_id' => $user_id,
        'api_key' => $api_key,
        'ip' => $user_ip,
        'user_agent' => $user_agent,
        's2' => $s2
    ];

    $apiUrl = $base_url . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(['error' => 'Failed to fetch CPABuild offers']);
        exit;
    }

    echo $response;
    exit;
}
