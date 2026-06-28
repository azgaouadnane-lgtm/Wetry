<?php
/**
 * includes/geo.php
 * Server-side country detection with Cloudflare priority + IP-API fallback
 */

if (!defined('ROOT_DIR')) {
    require_once dirname(__DIR__) . '/config.php';
}

/**
 * Get the real client IP address.
 */
function getClientIp(): string {
    $headers = [
        'HTTP_CF_CONNECTING_IP',   // Cloudflare real IP
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];
    foreach ($headers as $h) {
        if (!empty($_SERVER[$h])) {
            // X-Forwarded-For may contain a list
            $ip = trim(explode(',', $_SERVER[$h])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    // Fallback (might be private range in local dev)
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Detect user country code (2-letter ISO 3166-1 alpha-2).
 * Priority:
 *   1. Cloudflare header  HTTP_CF_IPCOUNTRY
 *   2. Local geo cache    data/geo_cache.json
 *   3. ip-api.com fallback
 * Returns 'XX' on failure.
 */
function getUserCountry(): string {
    // 1. Cloudflare header (fastest — no API call needed)
    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        $cc = strtoupper(trim($_SERVER['HTTP_CF_IPCOUNTRY']));
        if (preg_match('/^[A-Z]{2}$/', $cc) && $cc !== 'XX') {
            return $cc;
        }
    }

    $ip = getClientIp();

    // Local / private IP → For local testing with VPN, fetch the real public IP instead of returning 'XX'
    if (in_array($ip, ['127.0.0.1', '::1']) ||
        !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        
        $publicIp = '';
        if (function_exists('curl_init')) {
            $ch = curl_init('https://api.ipify.org');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $publicIp = curl_exec($ch);
            curl_close($ch);
        } else {
            $publicIp = @file_get_contents('https://api.ipify.org');
        }
        
        if ($publicIp && filter_var($publicIp, FILTER_VALIDATE_IP)) {
            $ip = $publicIp;
        } else {
            return 'XX';
        }
    }

    // 2. Cache lookup
    $cache   = readGeoCache();
    $cacheHit = $cache[$ip] ?? null;
    if ($cacheHit && isset($cacheHit['country'], $cacheHit['time'])) {
        if ((time() - (int)$cacheHit['time']) < GEO_CACHE_TTL) {
            return strtoupper($cacheHit['country']);
        }
    }

    // 3. ip-api.com fallback
    $country = fetchCountryFromApi($ip);

    // Store in cache
    if ($country !== 'XX') {
        $cache[$ip] = ['country' => $country, 'time' => time()];
        writeGeoCache($cache);
    }

    return $country;
}

/**
 * Resolve the correct link for an app based on user country.
 */
function resolveLink(array $app, string $country): string {
    if (!empty($app['geo']) && is_array($app['geo'])) {
        foreach ($app['geo'] as $rule) {
            $countries = array_map('strtoupper', (array)($rule['countries'] ?? []));
            if (in_array(strtoupper($country), $countries)) {
                return $rule['link'] ?? $app['general_link'];
            }
        }
    }
    return $app['general_link'] ?? '';
}

// ─── Internal helpers ─────────────────────────────────────────────────────────

function readGeoCache(): array {
    if (!file_exists(GEO_CACHE_FILE)) return [];
    $data = json_decode(file_get_contents(GEO_CACHE_FILE), true);
    return is_array($data) ? $data : [];
}

function writeGeoCache(array $cache): void {
    // Prune entries older than TTL to keep the file lean
    $now = time();
    foreach ($cache as $ip => $entry) {
        if (($now - (int)($entry['time'] ?? 0)) >= GEO_CACHE_TTL) {
            unset($cache[$ip]);
        }
    }
    file_put_contents(GEO_CACHE_FILE, json_encode($cache, JSON_PRETTY_PRINT), LOCK_EX);
}

function fetchCountryFromApi(string $ip): string {
    $url = sprintf(GEO_API_URL, urlencode($ip));

    // Try cURL first, fall back to file_get_contents
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_USERAGENT      => 'SPIDERMOD/1.0',
        ]);
        $body = curl_exec($ch);
        curl_close($ch);
    } else {
        $ctx  = stream_context_create(['http' => ['timeout' => 3]]);
        $body = @file_get_contents($url, false, $ctx);
    }

    if (!$body) return 'XX';
    $data = json_decode($body, true);
    if (!is_array($data) || ($data['status'] ?? '') !== 'success') return 'XX';

    $cc = strtoupper(trim($data['countryCode'] ?? ''));
    return preg_match('/^[A-Z]{2}$/', $cc) ? $cc : 'XX';
}
