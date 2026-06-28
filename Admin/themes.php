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

    if ($action === 'save_theme') {
        $settings['color_mode'] = $_POST['color_mode'] ?? 'dark';
        if (writeSettings($settings)) {
            $success = 'Color mode updated successfully.';
        } else {
            $error = 'Failed to save settings.';
        }
    } elseif ($action === 'save_accent') {
        $settings['active_theme'] = $_POST['active_theme'] ?? 'cyber_blue';
        if (writeSettings($settings)) {
            $success = 'Accent color updated successfully.';
        } else {
            $error = 'Failed to save settings.';
        }
    }
}

$themes = getThemes();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Themes | SpiderMod Admin</title>
    <link rel="stylesheet" href="assets/admin.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <?php
    $activeTheme = $themes[$settings['active_theme']] ?? $themes['cyber_blue'];
    ?>
    <style>
        :root {
            --primary: <?= $activeTheme['primary'] ?>;
            --secondary: <?= $activeTheme['secondary'] ?>;
            --primary-gradient: linear-gradient(135deg, <?= $activeTheme['primary'] ?>, <?= $activeTheme['secondary'] ?>);
            --primary-glow: <?= $activeTheme['primary'] ?>33;
        }
        .btn-primary { background: var(--primary-gradient) !important; border: none !important; box-shadow: 0 0 15px var(--primary-glow); }
        .nav-link.active { border-color: var(--primary) !important; background: rgba(255,255,255,0.05); }
        .nav-link.active .material-symbols-outlined { color: var(--primary) !important; }
        .brand img { filter: drop-shadow(0 0 8px var(--primary)); }
        
        .settings-card { margin-bottom: 40px; }
        
        /* Theme Selector Styles */
        .mode-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .mode-card {
            cursor: pointer;
            border: 2px solid var(--border);
            border-radius: 16px;
            padding: 10px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--bg-card);
        }
        .mode-card:hover { border-color: var(--border-hover); transform: translateY(-2px); }
        .mode-card.active { border-color: var(--primary); background: rgba(129, 236, 255, 0.05); }
        .mode-preview {
            height: 120px;
            border-radius: 12px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        .mode-preview.light { background: #f8fafc; border: 1px solid #e2e8f0; }
        .mode-preview.dark { background: #090e1c; border: 1px solid #1e253b; }
        
        .accent-grid { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px; }
        .accent-dot {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            background-size: 200% 200%;
        }
        .accent-dot:hover { transform: scale(1.1); }
        .accent-dot.active { border-color: white; box-shadow: 0 0 15px rgba(255,255,255,0.4); }

        .mock-ui { width: 70%; height: 60%; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: flex; flex-direction: column; overflow: hidden; }
        .mock-header { height: 12px; width: 100%; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .mock-body { flex: 1; padding: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
        .mock-item { height: 20px; border-radius: 4px; }
        
        .light .mock-ui { background: white; }
        .light .mock-header { background: #f1f5f9; }
        .light .mock-item { background: #e2e8f0; }
        
        .dark .mock-ui { background: #1e253b; border: 1px solid #334155; }
        .dark .mock-header { background: #0f172a; }
        .dark .mock-item { background: #334155; }
    </style>
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
            <a href="themes.php" class="nav-link active">
                <span class="material-symbols-outlined">palette</span>
                Themes
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
            <a href="social_settings.php" class="nav-link">
                <span class="material-symbols-outlined">share</span>
                Social Networks
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
            <h2>Themes & Styling</h2>
            <p style="color:var(--text-muted); font-size: 0.9em; margin-top: 5px;">Customize the visual appearance of your storefront.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- COLOR MODE SETTINGS -->
        <div class="card settings-card">
            <div class="card-header">
                <div>
                    <h3 style="font-size: 1.4em; color:var(--text-main); font-family:var(--font-headline);">Color Mode</h3>
                    <p style="color:var(--text-muted); font-size: 0.9em; margin-top: 5px;">You can choose between Light and Dark color modes.</p>
                </div>
            </div>

            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save_theme">
                <input type="hidden" name="color_mode" id="color_mode_input" value="<?= $settings['color_mode'] ?>">

                <div class="mode-grid">
                    <div class="mode-card <?= $settings['color_mode'] === 'light' ? 'active' : '' ?>" onclick="setMode('light')">
                        <div class="mode-preview light">
                            <div class="mock-ui">
                                <div class="mock-header"></div>
                                <div class="mock-body">
                                    <div class="mock-item" style="background:var(--primary); opacity:0.6;"></div>
                                    <div class="mock-item"></div>
                                    <div class="mock-item"></div>
                                    <div class="mock-item"></div>
                                </div>
                            </div>
                        </div>
                        <span style="font-weight:700; color: <?= $settings['color_mode'] === 'light' ? 'var(--primary)' : 'var(--text-muted)' ?>">Light Mode</span>
                    </div>

                    <div class="mode-card <?= $settings['color_mode'] === 'dark' ? 'active' : '' ?>" onclick="setMode('dark')">
                        <div class="mode-preview dark">
                            <div class="mock-ui">
                                <div class="mock-header"></div>
                                <div class="mock-body">
                                    <div class="mock-item" style="background:var(--primary); opacity:0.6;"></div>
                                    <div class="mock-item"></div>
                                    <div class="mock-item"></div>
                                    <div class="mock-item"></div>
                                </div>
                            </div>
                        </div>
                        <span style="font-weight:700; color: <?= $settings['color_mode'] === 'dark' ? 'var(--primary)' : 'var(--text-muted)' ?>">Dark Mode</span>
                    </div>
                </div>

                <script>
                    function setMode(mode) {
                        document.getElementById('color_mode_input').value = mode;
                        document.querySelectorAll('.mode-card').forEach(c => c.classList.remove('active'));
                        event.currentTarget.classList.add('active');
                    }
                </script>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Save Color Mode</button>
                </div>
            </form>
        </div>

        <!-- ACCENT COLOR SETTINGS -->
        <div class="card settings-card">
            <div class="card-header">
                <div>
                    <h3 style="font-size: 1.4em; color:var(--text-main); font-family:var(--font-headline);">Accent Color</h3>
                    <p style="color:var(--text-muted); font-size: 0.9em; margin-top: 5px;">You can select the primary accent color below.</p>
                </div>
            </div>

            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save_accent">
                <input type="hidden" name="active_theme" id="active_theme_input" value="<?= $settings['active_theme'] ?>">

                <div class="accent-grid">
                    <?php 
                    $activeThemeKey = $settings['active_theme'] ?? 'cyber_blue';
                    foreach ($themes as $key => $theme): 
                        $isActive = ($activeThemeKey === $key);
                        $gradient = "linear-gradient(135deg, " . $theme['primary'] . ", " . $theme['secondary'] . ")";
                    ?>
                        <div class="accent-dot <?= $isActive ? 'active' : '' ?>" 
                             style="background: <?= $gradient ?>;" 
                             onclick="setAccent('<?= $key ?>')" 
                             title="<?= $theme['name'] ?>">
                             <?php if ($isActive): ?>
                                <span class="material-symbols-outlined" style="color:white; font-size:20px;">check</span>
                             <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <script>
                    function setAccent(themeKey) {
                        document.getElementById('active_theme_input').value = themeKey;
                        document.querySelectorAll('.accent-dot').forEach(d => d.classList.remove('active'));
                        event.currentTarget.classList.add('active');
                    }
                </script>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Save Accent Color</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
