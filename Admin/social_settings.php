<?php
require_once dirname(__DIR__) . '/config.php';
require_once ROOT_DIR . '/includes/auth.php';
require_once ROOT_DIR . '/includes/functions.php';

requireLogin();

$success = '';
$error = '';
$settings = readSettings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'save_social') {
        $settings['social_instagram'] = sanitize($_POST['social_instagram'] ?? '');
        $settings['social_youtube']   = sanitize($_POST['social_youtube'] ?? '');
        $settings['social_telegram']  = sanitize($_POST['social_telegram'] ?? '');
        $settings['social_tiktok']    = sanitize($_POST['social_tiktok'] ?? '');
        $settings['social_facebook']  = sanitize($_POST['social_facebook'] ?? '');
        
        if (writeSettings($settings)) {
            $success = 'Social network links updated successfully.';
        } else {
            $error = 'Failed to save settings.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Social Networks | SpiderMod Admin</title>
    <link rel="stylesheet" href="assets/admin.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
</head>
<body>
    <nav class="admin-nav">
        <div class="brand">
            <img src="<?= sanitize((strpos($settings['logo_url'] ?? '', 'http') === 0) ? $settings['logo_url'] : '../' . ltrim($settings['logo_url'] ?? 'public/assets/img/555.png', '/')) ?>" alt="SpiderMod Logo" style="width: 24px; height: 24px; object-fit: contain; filter: drop-shadow(0 0 5px #81ecff);">
            <?= htmlspecialchars(explode(' | ', $settings['site_title'] ?? 'SpiderMod')[0]) ?>
        </div>
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">
                <span class="material-symbols-outlined">dashboard</span>
                Dashboard
            </a>
            <a href="general_settings.php" class="nav-link">
                <span class="material-symbols-outlined">settings_suggest</span>
                General Settings
            </a>
            <a href="locker_settings.php" class="nav-link">
                <span class="material-symbols-outlined">lock_open</span>
                Locker Settings
            </a>
            <a href="api_settings.php" class="nav-link">
                <span class="material-symbols-outlined">api</span>
                API Settings
            </a>
            <a href="social_settings.php" class="nav-link active">
                <span class="material-symbols-outlined">share</span>
                Social Networks
            </a>
            <a href="themes.php" class="nav-link">
                <span class="material-symbols-outlined">palette</span>
                Themes
            </a>
            <a href="settings.php" class="nav-link">
                <span class="material-symbols-outlined">settings</span>
                Admin Settings
            </a>
            <a href="system_check.php" class="nav-link">
                <span class="material-symbols-outlined">health_and_safety</span>
                System Check
            </a>
            <a href="logout.php" class="nav-link">
                <span class="material-symbols-outlined">logout</span>
                Logout
            </a>
        </div>
        </div>
                </div>
        <div class="sidebar-footer" style="margin-top: auto; padding: 20px 15px; border-top: 1px solid rgba(255,255,255,0.05); text-align: center;">
            
            <a href="https://t.me/medhachim" target="_blank" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(0, 136, 204, 0.1); color: #0088cc; padding: 10px 12px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 0.85rem; margin-bottom: 20px; border: 1px solid rgba(0, 136, 204, 0.3); transition: 0.3s;" onmouseover="this.style.background='rgba(0, 136, 204, 0.2)'" onmouseout="this.style.background='rgba(0, 136, 204, 0.1)'">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.223-.548.223l.188-2.85 5.18-4.686c.223-.195-.054-.304-.346-.11l-6.4 4.024-2.76-.86c-.6-.185-.61-.6.125-.89l10.736-4.135c.498-.184.945.106.825.96z"/></svg>
                Contact Support
            </a>
            
            <div style="display: flex; justify-content: center; gap: 15px; margin-bottom: 15px;">
                <a href="https://github.com/medhachim" target="_blank" style="color: #94a3b8; transition: 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#94a3b8'">
                    <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                </a>
                <a href="https://www.instagram.com/medhachim_/" target="_blank" style="color: #94a3b8; transition: 0.3s;" onmouseover="this.style.color='#E1306C'" onmouseout="this.style.color='#94a3b8'">
                    <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </a>
                <a href="https://www.youtube.com/@medhachim01" target="_blank" style="color: #94a3b8; transition: 0.3s;" onmouseover="this.style.color='#FF0000'" onmouseout="this.style.color='#94a3b8'">
                    <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                </a>
            </div>
            <div style="font-size: 0.75rem; color: #64748b; letter-spacing: 0.5px; opacity: 0.8;">
                Created by <br><span style="color: #00e5ff; text-shadow: 0 0 8px rgba(0,229,255,0.6); font-weight: 800; font-size: 0.85rem; letter-spacing: 1px;">MedHachim</span>
            </div>
        </div>
    </nav>

    <div class="container" style="max-width: 900px;">
        <div class="card-header">
            <h2>Social Networks</h2>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="card settings-card">
            <div class="card-header">
                <div>
                    <h3 style="font-size: 1.4em; color:var(--text-main); font-family:var(--font-headline);">Community Links</h3>
                    <p style="color:var(--text-muted); font-size: 0.9em; margin-top: 5px;">Configure your official social network profile links here.</p>
                </div>
            </div>
            
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save_social">

                <div class="form-grid">
                    <!-- Instagram -->
                    <div class="form-group">
                        <label>Instagram URL</label>
                        <div style="position:relative;">
                            <span class="material-symbols-outlined" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:18px;">photo_camera</span>
                            <input type="url" name="social_instagram" class="form-control" style="padding-left:45px;" value="<?= sanitize($settings['social_instagram'] ?? '') ?>" placeholder="https://instagram.com/yourprofile">
                        </div>
                    </div>

                    <!-- YouTube -->
                    <div class="form-group">
                        <label>YouTube URL</label>
                        <div style="position:relative;">
                            <span class="material-symbols-outlined" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:18px;">play_circle</span>
                            <input type="url" name="social_youtube" class="form-control" style="padding-left:45px;" value="<?= sanitize($settings['social_youtube'] ?? '') ?>" placeholder="https://youtube.com/@yourchannel">
                        </div>
                    </div>
                </div>

                <div class="form-grid" style="margin-top: 20px;">
                    <!-- Telegram -->
                    <div class="form-group">
                        <label>Telegram URL</label>
                        <div style="position:relative;">
                            <span class="material-symbols-outlined" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:18px;">send</span>
                            <input type="url" name="social_telegram" class="form-control" style="padding-left:45px;" value="<?= sanitize($settings['social_telegram'] ?? '') ?>" placeholder="https://t.me/yourchannel">
                        </div>
                    </div>

                    <!-- TikTok -->
                    <div class="form-group">
                        <label>TikTok URL</label>
                        <div style="position:relative;">
                            <span class="material-symbols-outlined" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:18px;">music_note</span>
                            <input type="url" name="social_tiktok" class="form-control" style="padding-left:45px;" value="<?= sanitize($settings['social_tiktok'] ?? '') ?>" placeholder="https://tiktok.com/@yourprofile">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <!-- Facebook -->
                    <label>Facebook URL</label>
                    <div style="position:relative;">
                        <span class="material-symbols-outlined" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:18px;">facebook</span>
                        <input type="url" name="social_facebook" class="form-control" style="padding-left:45px;" value="<?= sanitize($settings['social_facebook'] ?? '') ?>" placeholder="https://facebook.com/yourpage">
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Save Social Networks</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
