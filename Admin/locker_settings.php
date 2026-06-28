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

    if ($action === 'save_locker') {
        $settings['manual_check_enabled'] = isset($_POST['manual_check_enabled']);
        $settings['manual_check_admin_mode'] = isset($_POST['manual_check_admin_mode']);
        $settings['phone_whitelist_id'] = trim($_POST['phone_whitelist_id'] ?? '');
        
        if (writeSettings($settings)) {
            $success = 'Verification settings updated successfully.';
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
    <title>Locker Settings | SpiderMod Admin</title>
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
            <a href="locker_settings.php" class="nav-link active">
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
            <h2>Locker & Verification</h2>
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
                    <h3 style="font-size: 1.4em; color:var(--text-main); font-family:var(--font-headline);">Locker & Manual Verification</h3>
                    <p style="color:var(--text-muted); font-size: 0.9em; margin-top: 5px;">Manage the "Check Completion" button behavior.</p>
                </div>
            </div>

            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save_locker">

                <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px; padding: 15px; background: rgba(129, 236, 255, 0.03); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="flex: 1;">
                        <span style="display: block; font-weight: 700; color: var(--text-main); font-size: 0.9em;">Enable Manual Check Button</span>
                        <span style="font-size: 0.8em; color: var(--text-muted);">Display a "Check Completion" button below the offers list.</span>
                    </div>
                    <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 26px;">
                        <input type="checkbox" name="manual_check_enabled" <?= ($settings['manual_check_enabled'] ?? true) ? 'checked' : '' ?> style="opacity: 0; width: 0; height: 0;">
                        <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #1e253b; transition: .4s; border-radius: 34px; border: 1px solid var(--border);"></span>
                    </label>
                </div>

                <div class="form-group" id="admin-mode-block" style="background: rgba(255, 85, 85, 0.03); border-radius: 12px; border: 1px solid rgba(255, 85, 85, 0.2); overflow: hidden; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; padding: 15px;">
                        <div style="flex: 1;">
                            <span style="display: block; font-weight: 700; color: #ff5555; font-size: 0.9em;">Admin Mode (Force Success)</span>
                            <span style="font-size: 0.8em; color: var(--text-muted);">When enabled, clicking the button instantly unlocks the reward!</span>
                        </div>
                        <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 26px;">
                            <input type="checkbox" name="manual_check_admin_mode" id="adminModeToggle" <?= ($settings['manual_check_admin_mode'] ?? false) ? 'checked' : '' ?> style="opacity: 0; width: 0; height: 0;">
                            <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; border-radius: 34px; border: 1px solid var(--border); background-color: #1e253b; transition: .4s;"></span>
                        </label>
                    </div>

                    <!-- Phone ID Field — only visible when Admin Mode is ON -->
                    <div id="phone-id-field" style="padding: 0 15px 15px 15px; display: <?= ($settings['manual_check_admin_mode'] ?? false) ? 'block' : 'none' ?>;">

                        <div style="border-top: 1px solid rgba(255,85,85,0.15); padding-top: 14px;">
                            <label style="display: flex; align-items: center; gap: 6px; font-size: 0.78em; font-weight: 700; color: #ff5555; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px;">
                                <span class="material-symbols-outlined" style="font-size: 15px;">smartphone</span>
                                Phone ID (Whitelist)
                            </label>
                            <div style="display: flex; gap: 10px;">
                                <input
                                    type="text"
                                    name="phone_whitelist_id"
                                    id="phone_whitelist_id"
                                    value="<?= htmlspecialchars($settings['phone_whitelist_id'] ?? '', ENT_QUOTES) ?>"
                                    placeholder="e.g. FP-XXXXXX"
                                    style="flex: 1; box-sizing: border-box; background: rgba(255,85,85,0.05); border: 1px solid rgba(255,85,85,0.3); border-radius: 10px; padding: 10px 14px; color: var(--text-main); font-size: 0.85em; outline: none; transition: border-color .2s;"
                                    onfocus="this.style.borderColor='#ff5555'" onblur="this.style.borderColor='rgba(255,85,85,0.3)'"
                                >
                                <button type="button" onclick="autoWhitelist(this)" style="background: #ff5555; color: white; border: none; padding: 0 15px; border-radius: 10px; font-weight: bold; font-size: 0.75em; cursor: pointer; white-space: nowrap; transition: 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">Whitelist This Device</button>
                            </div>
                            <script>
                                function autoWhitelist(btn) {
                                    if (typeof getDeviceFingerprint === 'function') {
                                        const id = getDeviceFingerprint();
                                        document.getElementById('phone_whitelist_id').value = id;
                                        const original = btn.textContent;
                                        btn.textContent = 'Added!';
                                        setTimeout(() => btn.textContent = original, 2000);
                                    } else {
                                        alert('Fingerprint utility not loaded yet.');
                                    }
                                }
                            </script>
                            <p style="margin-top: 8px; font-size: 0.75em; color: var(--text-muted); line-height: 1.5;">
                                <span class="material-symbols-outlined" style="font-size: 12px; vertical-align: middle;">info</span>
                                &nbsp;Success will only be triggered for the device whose fingerprint matches this ID.<br>
                                Any other device will get a <strong style="color: #ff5555;">False</strong> result. Leave empty to allow all devices.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 30px; padding: 20px; background: rgba(255, 255, 255, 0.02); border: 1px dashed var(--border); border-radius: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                        <span class="material-symbols-outlined" style="color: var(--primary);">person_search</span>
                        <h4 style="margin: 0; font-size: 1.1em; color: var(--text-main);">Your Device Identification</h4>
                    </div>
                    <p style="font-size: 0.8em; color: var(--text-muted); margin-bottom: 20px;">Use the information below to whitelist your current device. Open this page on the device you want to test with.</p>
                    
                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                        <div class="id-item" style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                            <span style="display: block; font-size: 10px; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 6px;">Your Device ID (Fingerprint)</span>
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <code id="my-fp-display" style="font-family: monospace; color: white; font-size: 1.1em;">Calculating...</code>
                                <button type="button" onclick="copyValue('my-fp-display', this)" style="background: none; border: 1px solid var(--primary); color: var(--primary); padding: 4px 10px; border-radius: 6px; font-size: 10px; cursor: pointer;">Copy</button>
                            </div>
                        </div>

                        <div class="id-item" style="background: rgba(0,0,0,0.2); padding: 12px; border-radius: 10px; border: 1px solid var(--border);">
                            <span style="display: block; font-size: 10px; font-weight: 800; color: var(--secondary); text-transform: uppercase; margin-bottom: 6px;">Your User Agent</span>
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                                <code id="my-ua-display" style="font-family: monospace; color: var(--text-muted); font-size: 0.8em; word-break: break-all; max-height: 40px; overflow-y: auto; flex: 1;">...</code>
                                <button type="button" onclick="copyValue('my-ua-display', this)" style="background: none; border: 1px solid var(--secondary); color: var(--secondary); padding: 4px 10px; border-radius: 6px; font-size: 10px; cursor: pointer;">Copy</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    function getDeviceFingerprint() {
                        const raw = [
                            navigator.userAgent,
                            screen.width + 'x' + screen.height,
                            screen.colorDepth,
                            Intl.DateTimeFormat().resolvedOptions().timeZone,
                            navigator.language,
                            navigator.hardwareConcurrency || 0
                        ].join('|');

                        let hash = 0;
                        for (let i = 0; i < raw.length; i++) {
                            hash = ((hash << 5) - hash) + raw.charCodeAt(i);
                            hash |= 0;
                        }
                        return 'FP-' + Math.abs(hash).toString(36).toUpperCase();
                    }

                    document.getElementById('my-fp-display').textContent = getDeviceFingerprint();
                    document.getElementById('my-ua-display').textContent = navigator.userAgent;

                    function copyValue(id, btn) {
                        const val = document.getElementById(id).textContent;
                        navigator.clipboard.writeText(val);
                        const originalText = btn.textContent;
                        btn.textContent = 'Copied!';
                        setTimeout(() => btn.textContent = originalText, 2000);
                    }
                </script>

                <style>
                    .switch input:checked + .slider { background-color: var(--primary); border-color: var(--primary); }
                    .switch input:checked + .slider:before { transform: translateX(24px); }
                    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
                </style>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Save Locker Settings</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show / Hide Phone ID field when Admin Mode toggle changes
        const adminToggle = document.getElementById('adminModeToggle');
        const phoneField  = document.getElementById('phone-id-field');

        function syncPhoneField() {
            phoneField.style.display = adminToggle.checked ? 'block' : 'none';
        }

        adminToggle.addEventListener('change', syncPhoneField);
        // Initialise on load (handles back-button restore)
        syncPhoneField();
    </script>
</body>
</html>
