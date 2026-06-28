<?php
/**
 * includes/auth.php
 * Session-based admin authentication with brute-force protection + CSRF
 */

if (!defined('ROOT_DIR')) {
    require_once dirname(__DIR__) . '/config.php';
}

/**
 * Fetch current admin credentials from JSON
 */
function getAdminCredentials(): array {
    if (!file_exists(ADMIN_DATA_FILE)) {
        return ['username' => 'admin', 'password_hash' => ''];
    }
    return json_decode(file_get_contents(ADMIN_DATA_FILE), true) ?: [];
}


// ─── Session Bootstrap ────────────────────────────────────────────────────────

function startAdminSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        // Harden session cookie
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_name(ADMIN_SESSION_KEY);
        session_start();
    }
}

// ─── Auth Checks ──────────────────────────────────────────────────────────────

function isLoggedIn(): bool {
    startAdminSession();
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require authentication — redirect to login if not logged in.
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /Admin/index.php');
        exit;
    }
}

// ─── Login Logic ──────────────────────────────────────────────────────────────

/**
 * Attempt login. Returns true on success, error string on failure.
 */
function attemptLogin(string $username, string $password): bool|string {
    startAdminSession();

    // Brute-force protection
    $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
    $_SESSION['login_blocked_until'] = $_SESSION['login_blocked_until'] ?? 0;

    if (time() < (int)$_SESSION['login_blocked_until']) {
        $wait = (int)$_SESSION['login_blocked_until'] - time();
        return "Too many failed attempts. Try again in {$wait}s.";
    }

    // Validate credentials
    $creds = getAdminCredentials();
    $userOk = hash_equals($creds['username'] ?? '', $username);
    $passOk = password_verify($password, $creds['password_hash'] ?? '');

    if ($userOk && $passOk) {
        // Success — reset counters, regenerate session
        $_SESSION['login_attempts']    = 0;
        $_SESSION['login_blocked_until'] = 0;
        session_regenerate_id(true);
        $_SESSION['admin_logged_in']   = true;
        $_SESSION['admin_user']        = $creds['username'];
        $_SESSION['admin_login_time']  = time();
        return true;
    }

    // Failed attempt
    $_SESSION['login_attempts']++;
    if ((int)$_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION['login_blocked_until'] = time() + 300; // Block 5 minutes
        $_SESSION['login_attempts']      = 0;
        return 'Too many failed attempts. Blocked for 5 minutes.';
    }

    $remaining = MAX_LOGIN_ATTEMPTS - (int)$_SESSION['login_attempts'];
    return "Invalid credentials. {$remaining} attempt(s) remaining.";
}

// ─── Logout ───────────────────────────────────────────────────────────────────

function logout(): void {
    startAdminSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}
