<?php
/**
 * includes/functions.php
 * Core utility functions for SPIDERMOD
 */

if (!defined('ROOT_DIR')) {
    require_once dirname(__DIR__) . '/config.php';
}

define('SETTINGS_FILE', DATA_DIR . '/settings.json');


// ─── JSON Read / Write ────────────────────────────────────────────────────────

/**
 * Read and decode a JSON file. Returns array or default on failure.
 */
function readJson(string $file, $default = []): array {
    if (!file_exists($file)) return (array)$default;
    $content = file_get_contents($file);
    if ($content === false || trim($content) === '') return (array)$default;
    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : (array)$default;
}

/**
 * Write data as JSON to a file atomically (temp file + rename).
 */
function writeJson(string $file, $data): bool {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) return false;
    $tmp = $file . '.tmp.' . getmypid();
    if (file_put_contents($tmp, $json, LOCK_EX) === false) return false;
    return rename($tmp, $file);
}

// ─── Site Settings ───────────────────────────────────────────────────────────

function readSettings(): array {
    $defaults = [
        'brand_name' => 'SpiderMod',
        'logo_url' => 'public/assets/img/555.png',
        'site_title' => 'SpiderMod | Premium Storefront',
        'meta_description' => 'Discover and download the latest modded apps and games with premium features for free.',
        'ga_measurement_id' => '',
        'active_theme' => 'cyber_blue',
        'color_mode' => 'dark',
        'proof_enabled' => true,
        'proof_interval' => 15,
        'proof_duration' => 6,
        'proof_names' => ["Ahmed", "Sarah", "Youssef", "Layla", "Marco", "Elena", "Jean", "Sophie", "Khaled", "Amira", "Lucas", "Mia", "Omar", "Fatima", "David", "Emma", "Ali", "Zahra", "Tom", "Anna", "Chen", "Yuki", "Priya", "Raj", "Svetlana", "Ivan", "Diego", "Isabella", "Kwami", "Nia", "Hiroshi", "Aoi", "Mateo", "Camila", "Lars", "Ingrid", "Sanjay", "Anjali", "Dimitri", "Olga", "Carlos", "Sofia", "Alejandro", "Valentina", "Kwame", "Adjoa", "Santiago", "Lucia", "Nikolai", "Anastasia", "Kenji", "Hana", "Arjun", "Ishani", "Bjorn", "Freja", "Mustafa", "Zainab"],
        'proof_countries' => ["Morocco", "France", "USA", "Egypt", "Spain", "Germany", "United Kingdom", "Turkey", "Saudi Arabia", "Canada", "Italy", "Belgium", "Algeria", "UAE", "China", "Japan", "India", "Russia", "Brazil", "Mexico", "South Korea", "Australia", "South Africa", "Nigeria", "Argentina", "Sweden", "Norway", "Netherlands", "Portugal", "Greece", "Pakistan", "Indonesia", "Vietnam", "Thailand", "Poland", "Ukraine", "Switzerland", "Israel", "Qatar", "Kuwait", "Colombia", "Chile", "Peru"],
        'manual_check_enabled' => true,
        'manual_check_admin_mode' => false,
        'phone_whitelist_id' => '',
        'api_offer_url' => 'https://d5b3uz3fo8hn3.cloudfront.net/public/offers/feed.php',
        'api_user_id' => '64435',
        'api_key' => '',
        'api_s2_param' => 'Wetry',
        'api_network' => 'cpabuild',
        'ogads_api_key' => '',
        'social_instagram' => '',
        'social_youtube' => '',
        'social_telegram' => '',
        'social_tiktok' => '',
        'social_facebook' => '',
        'license_key' => ''
    ];
    return readJson(SETTINGS_FILE, $defaults);
}

function getThemes(): array {
    return [
        'cyber_blue' => [
            'name' => 'Cyber Blue',
            'primary' => '#81ecff',
            'primary_container' => '#00e3fd',
            'secondary' => '#af88ff'
        ],
        'emerald_coast' => [
            'name' => 'Emerald Coast',
            'primary' => '#2ecc71',
            'primary_container' => '#27ae60',
            'secondary' => '#14b8a6'
        ],
        'sunset_blvd' => [
            'name' => 'Sunset Blvd',
            'primary' => '#f59e0b',
            'primary_container' => '#d97706',
            'secondary' => '#ef4444'
        ],
        'neon_dream' => [
            'name' => 'Neon Dream',
            'primary' => '#c084fc',
            'primary_container' => '#a855f7',
            'secondary' => '#f472b6'
        ],
        'deep_ocean' => [
            'name' => 'Deep Ocean',
            'primary' => '#3b82f6',
            'primary_container' => '#2563eb',
            'secondary' => '#0ea5e9'
        ],
        'ruby_flare' => [
            'name' => 'Ruby Flare',
            'primary' => '#ef4444',
            'primary_container' => '#dc2626',
            'secondary' => '#f43f5e'
        ],
        'arctic_icicle' => [
            'name' => 'Arctic Icicle',
            'primary' => '#7dd3fc',
            'primary_container' => '#38bdf8',
            'secondary' => '#6366f1'
        ],
        'forest_bloom' => [
            'name' => 'Forest Bloom',
            'primary' => '#a3e635',
            'primary_container' => '#84cc16',
            'secondary' => '#059669'
        ]
    ];
}




function writeSettings(array $settings): bool {
    return writeJson(SETTINGS_FILE, $settings);
}


// ─── Apps ─────────────────────────────────────────────────────────────────────

function readApps(): array {
    $data = readJson(APPS_FILE, ['apps' => []]);
    return $data['apps'] ?? [];
}

function writeApps(array $apps): bool {
    return writeJson(APPS_FILE, ['apps' => array_values($apps)]);
}

function getAppById(int $id): ?array {
    foreach (readApps() as $app) {
        if ((int)$app['id'] === $id) return $app;
    }
    return null;
}

function generateId(?array $apps = null): int {
    if ($apps === null) $apps = readApps();
    if (empty($apps)) return 1;
    return max(array_column($apps, 'id')) + 1;
}

// ─── Sanitization ─────────────────────────────────────────────────────────────

function sanitize($val): string {
    return htmlspecialchars(trim((string)$val), ENT_QUOTES, 'UTF-8');
}

function sanitizeUrl(string $url): string {
    $url = trim($url);
    // Only allow http/https
    if (!empty($url) && !preg_match('#^https?://#i', $url)) {
        return '';
    }
    return filter_var($url, FILTER_SANITIZE_URL) ?: '';
}

// ─── Click Tracking ───────────────────────────────────────────────────────────

function recordClick(string $country, ?int $appId = null): bool {
    $clicks = readJson(CLICKS_FILE, []);
    $today  = date('Y-m-d');
    $country = strtoupper(preg_replace('/[^A-Z]/i', '', $country));
    if (strlen($country) !== 2) $country = 'XX';

    if (!isset($clicks[$today])) {
        $clicks[$today] = ['geo' => [], 'apps' => []];
    }
    
    // Check if it's the old format and migrate on the fly if needed
    if (!isset($clicks[$today]['geo']) && !isset($clicks[$today]['apps'])) {
        $oldData = $clicks[$today];
        $clicks[$today] = ['geo' => $oldData, 'apps' => []];
    }

    $clicks[$today]['geo'][$country] = ($clicks[$today]['geo'][$country] ?? 0) + 1;
    
    if ($appId !== null) {
        $appIdStr = (string)$appId;
        $clicks[$today]['apps'][$appIdStr] = ($clicks[$today]['apps'][$appIdStr] ?? 0) + 1;
    }

    // Keep only last 90 days to avoid file bloat
    ksort($clicks);
    if (count($clicks) > 90) {
        $clicks = array_slice($clicks, -90, 90, true);
    }

    return writeJson(CLICKS_FILE, $clicks);
}

// ─── Stats Helpers ────────────────────────────────────────────────────────────

function getClicksForDays(int $days): array {
    $clicks = readJson(CLICKS_FILE, []);
    $result = [];
    for ($i = 0; $i < $days; $i++) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $result[$date] = $clicks[$date] ?? [];
    }
    return $result;
}

function sumClicks(array $clicksByDate): int {
    $total = 0;
    foreach ($clicksByDate as $day) {
        $geo = $day['geo'] ?? $day; // fallback to old format
        foreach ($geo as $k => $count) {
            if (!is_numeric($count)) continue; // skip 'apps' key if nested
            $total += (int)$count;
        }
    }
    return $total;
}

function topCountries(int $days = 30, int $limit = 10): array {
    $data   = getClicksForDays($days);
    $totals = [];
    foreach ($data as $day) {
        $geo = $day['geo'] ?? $day;
        foreach ($geo as $country => $count) {
            if (!is_numeric($count)) continue;
            $totals[$country] = ($totals[$country] ?? 0) + (int)$count;
        }
    }
    arsort($totals);
    return array_slice($totals, 0, $limit, true);
}

function getDailyTotals(int $days = 7): array {
    $data   = getClicksForDays($days);
    $result = [];
    foreach ($data as $date => $day) {
        $geo = $day['geo'] ?? $day;
        $result[$date] = is_array($geo) ? array_sum(array_filter($geo, 'is_numeric')) : 0;
    }
    krsort($result);
    return $result;
}

function getTrendingApps(string $category, int $days = 30, int $limit = 3): array {
    $data = getClicksForDays($days);
    $appClickTotals = [];
    
    foreach ($data as $day) {
        $apps = $day['apps'] ?? [];
        foreach ($apps as $id => $count) {
            $appClickTotals[$id] = ($appClickTotals[$id] ?? 0) + (int)$count;
        }
    }
    
    $allApps = readApps();
    $candidates = [];
    
    foreach ($allApps as $app) {
        if (strtolower($app['category'] ?? '') === strtolower($category)) {
            $app['clicks_rank'] = $appClickTotals[(string)$app['id']] ?? 0;
            $candidates[] = $app;
        }
    }
    
    // Sort by clicks DESC, then by ID DESC (newest)
    usort($candidates, function($a, $b) {
        if ($b['clicks_rank'] !== $a['clicks_rank']) {
            return $b['clicks_rank'] <=> $a['clicks_rank'];
        }
        return $b['id'] <=> $a['id'];
    });
    return array_slice($candidates, 0, $limit);
}

function getAllAppsStats(int $days = 30): array {
    $data = getClicksForDays($days);
    $appClickTotals = [];
    
    foreach ($data as $day) {
        $apps = $day['apps'] ?? [];
        foreach ($apps as $id => $count) {
            $appClickTotals[$id] = ($appClickTotals[$id] ?? 0) + (int)$count;
        }
    }
    
    $allApps = readApps();
    $candidates = [];
    
    foreach ($allApps as $app) {
        $clicks = $appClickTotals[(string)$app['id']] ?? 0;
        if ($clicks > 0) {
            $app['clicks_rank'] = $clicks;
            $candidates[] = $app;
        }
    }
    
    usort($candidates, function($a, $b) {
        if ($b['clicks_rank'] !== $a['clicks_rank']) {
            return $b['clicks_rank'] <=> $a['clicks_rank'];
        }
        return $b['id'] <=> $a['id'];
    });
    
    return $candidates;
}

// ─── CSRF ─────────────────────────────────────────────────────────────────────

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

function getCountryList(): array {
    return [
        'AF'=>'Afghanistan','AL'=>'Albania','DZ'=>'Algeria','AR'=>'Argentina','AU'=>'Australia','AT'=>'Austria','BH'=>'Bahrain',
        'BD'=>'Bangladesh','BE'=>'Belgium','BR'=>'Brazil','BG'=>'Bulgaria','CA'=>'Canada','CL'=>'Chile','CN'=>'China','CO'=>'Colombia','HR'=>'Croatia',
        'CY'=>'Cyprus','CZ'=>'Czech Republic','DK'=>'Denmark','EG'=>'Egypt','FI'=>'Finland','FR'=>'France','DE'=>'Germany','GR'=>'Greece',
        'HU'=>'Hungary','IN'=>'India','ID'=>'Indonesia','IQ'=>'Iraq','IE'=>'Ireland','IL'=>'Israel','IT'=>'Italy','JP'=>'Japan','JO'=>'Jordan',
        'KE'=>'Kenya','KW'=>'Kuwait','LB'=>'Lebanon','LY'=>'Libya','MY'=>'Malaysia','MV'=>'Maldives','MX'=>'Mexico','MA'=>'Morocco',
        'NL'=>'Netherlands','NZ'=>'New Zealand','NG'=>'Nigeria','NO'=>'Norway','OM'=>'Oman','PK'=>'Pakistan','PS'=>'Palestine',
        'PH'=>'Philippines','PL'=>'Poland','PT'=>'Portugal','QA'=>'Qatar','RO'=>'Romania','RU'=>'Russia','SA'=>'Saudi Arabia',
        'SG'=>'Singapore','ZA'=>'South Africa','KR'=>'South Korea','ES'=>'Spain','LK'=>'Sri Lanka','SE'=>'Sweden','CH'=>'Switzerland',
        'SY'=>'Syria','TW'=>'Taiwan','TH'=>'Thailand','TN'=>'Tunisia','TR'=>'Turkey','AE'=>'United Arab Emirates','GB'=>'United Kingdom',
        'US'=>'United States','YE'=>'Yemen','VN'=>'Vietnam','CL'=>'Chile','PE'=>'Peru','VE'=>'Venezuela','SD'=>'Sudan','DZ'=>'Algeria',
        // Add more as needed, but these are top 80+
    ];
}
