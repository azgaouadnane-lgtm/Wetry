<?php
/**
 * SPIDERMOD — Global Configuration
 * Change ADMIN_USER and ADMIN_PASS_HASH before deploying.
 * Generate a new hash: php -r "echo password_hash('yourpassword', PASSWORD_BCRYPT);"
 */

// ─── Admin Credentials ──────────────────────────────────────────────────────
define('ADMIN_DATA_FILE', __DIR__ . '/data/admin.json');

// ─── Paths ───────────────────────────────────────────────────────────────────
define('ROOT_DIR',       __DIR__);
define('DATA_DIR',       ROOT_DIR . '/data');
define('APPS_FILE',      DATA_DIR . '/apps.json');
define('CLICKS_FILE',    DATA_DIR . '/clicks.json');
define('GEO_CACHE_FILE', DATA_DIR . '/geo_cache.json');

// ─── Geo Settings ────────────────────────────────────────────────────────────
define('GEO_CACHE_TTL', 86400);          // 24 hours
define('GEO_API_URL',   'http://ip-api.com/json/%s?fields=countryCode');

// ─── Security ────────────────────────────────────────────────────────────────
define('MAX_LOGIN_ATTEMPTS', 5);
define('ADMIN_SESSION_KEY', 'wetry_admin_v1');

// ─── Site Info ───────────────────────────────────────────────────────────────
define('SITE_NAME', 'Wetry');
define('SITE_URL', '');
