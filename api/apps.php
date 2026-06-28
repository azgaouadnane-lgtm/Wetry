<?php
/**
 * api/apps.php
 * Returns all apps as JSON with geo-resolved links.
 * GET → returns JSON array of apps
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

require_once dirname(__DIR__) . '/config.php';
require_once ROOT_DIR . '/includes/functions.php';
require_once ROOT_DIR . '/includes/geo.php';

try {
    $apps    = readApps();
    $country = getUserCountry();
    $output  = [];

    foreach ($apps as $app) {
        $link = resolveLink($app, $country);
        $output[] = [
            'id'          => (int)$app['id'],
            'name'        => $app['name'] ?? '',
            'icon_app'    => $app['icon_app'] ?? '',
            'bg'          => $app['bg'] ?? '',
            'type'        => $app['type'] ?? '',
            'content'     => $app['content'] ?? '',
            'rating'      => (float)($app['rating'] ?? 0),
            'size'        => $app['size'] ?? '',
            'download'    => $app['download'] ?? '',
            'developer'   => $app['developer'] ?? '',
            'category'    => $app['category'] ?? '',
            'supportedOS' => $app['supportedOS'] ?? [],
            'it'          => $app['it'] ?? '',
            'badge'       => $app['badge'] ?? '',
            'link'        => $link,
            'geo'         => $app['geo'] ?? [],
            'popup_type'    => $app['popup_type'] ?? 'mod',
            'coupon_code'   => $app['coupon_code'] ?? '',
            'resource_icon' => $app['resource_icon'] ?? '',
            'is_trending'   => !empty($app['is_trending']),
            'is_slider'     => !empty($app['is_slider']),
            'is_discover'   => !empty($app['is_discover']),
            'reward_amounts'=> $app['reward_amounts'] ?? [1000, 5000, 8000, 9999],
            's1'            => $app['s1'] ?? '',
        ];
    }

    $sliderApps = array_values(array_filter($output, function($a) {
        return !empty($a['is_slider']);
    }));

    // Optional debug param (only shows country)
    $response = [
        'apps' => $output, 
        'country' => $country,
        'trending' => $sliderApps
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
