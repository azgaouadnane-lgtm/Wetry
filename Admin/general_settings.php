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

    if ($action === 'save_license') {
        $keyToValidate = sanitize($_POST['license_key'] ?? '');

        // ── Validate key with License Manager API before saving ──
        $validationPassed = false;
        $validationError  = 'Could not connect to the license server. Please try again.';

        if (!empty($keyToValidate)) {
            $edp = base64_decode(str_rot13('nUE0pUZ6Yl9vpzScozc1pl5mnKEyY2SjnF92MKWcMaxhpTuj'));
            $currentDomain = $_SERVER['HTTP_HOST'];

            $postData = http_build_query([
                'serial_key' => $keyToValidate,
                'domain'     => $currentDomain,
                'product_id' => $settings['product_id'] ?? 'UnknownProduct'
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $edp);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $response = curl_exec($ch);
            curl_close($ch);


            if ($response !== false) {
                $secretKey     = 'Sp1d3rM0d_L1c3ns3_S3cr3t_K3y_999';
                $encMethod     = 'AES-256-CBC';
                $decoded       = base64_decode($response);
                $ivLen         = openssl_cipher_iv_length($encMethod);
                $iv            = substr($decoded, 0, $ivLen);
                $cipher        = substr($decoded, $ivLen);
                $decryptedJson = openssl_decrypt($cipher, $encMethod, $secretKey, 0, $iv);


                if ($decryptedJson !== false) {
                    $data = json_decode($decryptedJson, true);
                    if ($data && $data['success'] === true) {
                        $validationPassed = true;
                    } else {
                        $validationError = $data['message'] ?? 'Invalid License Key.';
                    }
                } else {
                    $validationError = 'Invalid or tampered server response.';
                }
            }
        } else {
            $validationError = 'License key cannot be empty.';
        }

        if ($validationPassed) {
            $settings['license_key'] = $keyToValidate;
            if (writeSettings($settings)) {
                $success = 'License Key validated and saved successfully.';
            } else {
                $error = 'Failed to save License Key.';
            }
        } else {
            $error = '❌ Invalid License Key: ' . $validationError;
        }
    } elseif ($action === 'remove_license') {
        $settings['license_key'] = '';
        
        if (writeSettings($settings)) {
            $success = 'License Key removed successfully.';
        } else {
            $error = 'Failed to remove License Key.';
        }
    } elseif ($action === 'save_seo') {
        $settings['brand_name'] = sanitize($_POST['brand_name'] ?? 'SpiderMod');
        $settings['logo_url'] = sanitize($_POST['logo_url'] ?? 'public/assets/img/555.png');
        $settings['site_title'] = sanitize($_POST['site_title'] ?? '');
        $settings['meta_description'] = sanitize($_POST['meta_description'] ?? '');
        
        if (writeSettings($settings)) {
            $success = 'SEO settings updated successfully.';
        } else {
            $error = 'Failed to save settings.';
        }
    } elseif ($action === 'save_ga4') {
        $settings['ga_measurement_id'] = sanitize($_POST['ga_measurement_id'] ?? '');
        
        if (writeSettings($settings)) {
            $success = 'GA4 tracking updated successfully.';
        } else {
            $error = 'Failed to save settings.';
        }
    } elseif ($action === 'save_proof') {
        $settings['proof_enabled'] = isset($_POST['proof_enabled']);
        $settings['proof_interval'] = (int)($_POST['proof_interval'] ?? 15);
        $settings['proof_duration'] = (int)($_POST['proof_duration'] ?? 6);
        
        // Convert multiline text to array, filtering out empty lines
        $namesText = $_POST['proof_names'] ?? '';
        $settings['proof_names'] = array_values(array_filter(array_map('trim', explode("\n", $namesText))));
        
        $countriesText = $_POST['proof_countries'] ?? '';
        $settings['proof_countries'] = array_values(array_filter(array_map('trim', explode("\n", $countriesText))));

        if (writeSettings($settings)) {
            $success = 'Social Proof settings updated successfully.';
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
    <title>General Settings | SpiderMod Admin</title>
    <link rel="stylesheet" href="assets/admin.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        .settings-card { margin-bottom: 40px; }
        .ga-preview {
            background: rgba(0,0,0,0.3);
            border-radius: 12px;
            padding: 20px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.85em;
            color: #af88ff;
            margin-top: 20px;
            border: 1px solid var(--border);
            white-space: pre-wrap;
            word-break: break-all;
        }
        .ga-preview span { color: #81ecff; }
        .ga-preview .tag { color: #f87171; }

    </style>
</head>
<body>
    <nav class="admin-nav">
        <div class="brand">
            <img src="<?= sanitize((strpos($settings['logo_url'] ?? '', 'http') === 0) ? $settings['logo_url'] : '../' . ltrim($settings['logo_url'] ?? 'public/assets/img/555.png', '/')) ?>" alt="SpiderMod Logo" style="width: 24px; height: 24px; object-fit: contain; filter: drop-shadow(0 0 5px #81ecff);">
            <?= htmlspecialchars($settings['brand_name'] ?? 'SpiderMod') ?>
        </div>
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link">
                <span class="material-symbols-outlined">dashboard</span>
                Dashboard
            </a>
            <a href="general_settings.php" class="nav-link active">
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
        <?php echo base64_decode(str_rot13("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")); ?>

    <div class="container" style="max-width: 900px;">
        <div class="card-header">
            <h2>General Settings</h2>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- LICENSE MANAGER SETTINGS -->
        <div class="card settings-card" style="border: 1px solid rgba(99,102,241,0.3); background: rgba(99,102,241,0.05);">
            <div class="card-header">
                <div>
                    <h3 style="font-size: 1.4em; color:#a5b4fc; font-family:var(--font-headline);">License Configuration</h3>
                    <p style="color:var(--text-muted); font-size: 0.9em; margin-top: 5px;">Configure the License Key required to activate this script.</p>
                </div>
            </div>
            
            <form method="post">
                <?= csrfField() ?>

                <!-- Product ID (Read-only for users) -->
                <div class="form-group" style="margin-bottom:1.2rem;">
                    <label style="display:flex; align-items:center; gap:6px; text-transform:uppercase; font-size:0.75rem; letter-spacing:1px; color:var(--text-muted);">
                        <span class="material-symbols-outlined" style="font-size:18px; color:#a5b4fc;">sell</span>
                        Product ID <span style="font-size:0.85em; text-transform:none;">(Provide this to your seller to get your key)</span>
                    </label>
                    <div style="display:flex; gap:10px; align-items:center; margin-top:8px;">
                        <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(99,102,241,0.3); color:#a5b4fc; font-family:monospace; font-size:1rem; padding:0.6rem 1rem; border-radius:8px; font-weight:600; min-width:200px;">
                            <?= sanitize($settings['product_id'] ?? 'Not set') ?>
                        </div>
                        
                        <a href="https://t.me/medhachim" target="_blank" style="display:inline-flex; align-items:center; gap:6px; color:#38bdf8; text-decoration:none; font-weight:600; font-size:0.85rem; padding:0.6rem 1rem; background:rgba(56,189,248,0.1); border-radius:8px; border:1px solid rgba(56,189,248,0.25); transition:all 0.2s; box-shadow:0 2px 10px rgba(56,189,248,0.1);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            Buy Lifetime Key
                        </a>
                    </div>
                </div>

                <div class="form-group">
                    <label>Product License Key</label>
                    <div style="position:relative;">
                        <span class="material-symbols-outlined" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#a5b4fc; font-size:18px;">key</span>
                        <input type="text" name="license_key" class="form-control" style="padding-left:45px; font-family:monospace; color:#e0e7ff; background:rgba(0,0,0,0.2);" value="<?= sanitize($settings['license_key'] ?? '') ?>" placeholder="XXXX-XXXX-XXXX-XXXX" required>
                    </div>
                </div>

                <div class="form-footer" style="display:flex; gap:10px; justify-content:flex-end;">
                    <?php if (!empty($settings['license_key'])): ?>
                        <button type="submit" name="action" value="remove_license" formnovalidate class="btn btn-danger" style="background: rgba(239,68,68,0.2); border: 1px solid rgba(239,68,68,0.5); color: #ef4444;">Remove Key</button>
                    <?php endif; ?>
                    <button type="submit" name="action" value="save_license" class="btn btn-primary" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); box-shadow: 0 4px 14px rgba(99,102,241,0.3); color:#fff;">Save License Key</button>
                </div>
            </form>
        </div>

        <!-- SEO SETTINGS -->
        <div class="card settings-card">
            <div class="card-header">
                <div>
                    <h3 style="font-size: 1.4em; color:var(--text-main); font-family:var(--font-headline);">Page title & meta description</h3>
                    <p style="color:var(--text-muted); font-size: 0.9em; margin-top: 5px;">You can edit the page title and description meta tags below.</p>
                </div>
            </div>
            
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save_seo">

                <div class="form-group">
                    <label>Brand Name (Logo Text)</label>
                    <div style="position:relative;">
                        <span class="material-symbols-outlined" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:18px;">branding_watermark</span>
                        <input type="text" name="brand_name" class="form-control" style="padding-left:45px;" value="<?= sanitize($settings['brand_name'] ?? 'SpiderMod') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Brand Logo URL</label>
                    <div style="position:relative;">
                        <span class="material-symbols-outlined" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:18px;">image</span>
                        <input type="text" name="logo_url" class="form-control" style="padding-left:45px;" value="<?= sanitize($settings['logo_url'] ?? 'public/assets/img/555.png') ?>" placeholder="https://example.com/logo.png" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Meta Title Tag</label>
                    <div style="position:relative;">
                        <span class="material-symbols-outlined" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:18px;">label</span>
                        <input type="text" name="site_title" class="form-control" style="padding-left:45px;" value="<?= sanitize($settings['site_title']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Meta Description Tag</label>
                    <textarea name="meta_description" class="form-control" rows="4"><?= sanitize($settings['meta_description']) ?></textarea>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Save SEO Settings</button>
                </div>
            </form>
        </div>

        <!-- GA4 SETTINGS -->
        <div class="card settings-card">
            <div class="card-header">
                <div>
                    <h3 style="font-size: 1.4em; color:var(--text-main); font-family:var(--font-headline);">Google Analytics 4 Tracking</h3>
                    <p style="color:var(--text-muted); font-size: 0.9em; margin-top: 5px;">Insert your Google Analytics 4 Measurement ID. Leave empty to disable tracking.</p>
                </div>
            </div>

            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save_ga4">

                <div class="form-group">
                    <label>GA4 Measurement ID</label>
                    <div style="position:relative;">
                        <span class="material-symbols-outlined" style="position:absolute; left:15px; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:18px;">analytics</span>
                        <input type="text" name="ga_measurement_id" class="form-control" style="padding-left:45px;" value="<?= sanitize($settings['ga_measurement_id']) ?>" placeholder="G-XXXXXXXXXX">
                    </div>
                </div>

                <?php if (!empty($settings['ga_measurement_id'])): ?>
                    <p style="font-size: 0.8em; color: var(--text-muted); margin-top: 20px;">
                        <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 16px;">info</span>
                        The following GA4 tracking code snippet will be included in the main index.php file just before the closing <span>&lt;/head&gt;</span> tag:
                    </p>
                    <div class="ga-preview">
<span class="tag">&lt;!-- Google tag (gtag.js) --&gt;</span>
<span class="tag">&lt;script async src="</span>https://www.googletagmanager.com/gtag/js?id=<?= $settings['ga_measurement_id'] ?><span>"&gt;&lt;/script&gt;</span>
<span class="tag">&lt;script&gt;</span>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= $settings['ga_measurement_id'] ?>');
<span class="tag">&lt;/script&gt;</span></div>
                <?php endif; ?>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Save GA4 Settings</button>
                </div>
            </form>
        </div>

        <!-- SOCIAL PROOF SETTINGS -->
        <div class="card settings-card">
            <div class="card-header">
                <div>
                    <h3 style="font-size: 1.4em; color:var(--text-main); font-family:var(--font-headline);">Social Proof Configuration</h3>
                    <p style="color:var(--text-muted); font-size: 0.9em; margin-top: 5px;">Manage how conversion notifications appear on your storefront.</p>
                </div>
            </div>

            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save_proof">

                <div class="form-group" style="display: flex; align-items: center; gap: 15px; margin-bottom: 40px; padding: 15px; background: rgba(129, 236, 255, 0.03); border-radius: 12px; border: 1px solid var(--border);">
                    <div style="flex: 1;">
                        <span style="display: block; font-weight: 700; color: var(--text-main); font-size: 0.9em;">Enable Social Proof</span>
                        <span style="font-size: 0.8em; color: var(--text-muted);">Show or hide conversion notifications.</span>
                    </div>
                    <label class="switch" style="position: relative; display: inline-block; width: 50px; height: 26px;">
                        <input type="checkbox" name="proof_enabled" <?= $settings['proof_enabled'] ? 'checked' : '' ?> style="opacity: 0; width: 0; height: 0;">
                        <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #1e253b; transition: .4s; border-radius: 34px; border: 1px solid var(--border);"></span>
                    </label>
                </div>

                <style>
                    input:checked + .slider { background-color: var(--primary); border-color: var(--primary); }
                    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
                    input:checked + .slider:before { transform: translateX(24px); }
                </style>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Interval (Seconds)</label>
                        <p style="font-size: 0.75em; color: var(--text-muted); margin-bottom: 10px;">Time between notifications.</p>
                        <input type="number" name="proof_interval" class="form-control" value="<?= (int)$settings['proof_interval'] ?>" min="5" max="300">
                    </div>
                    <div class="form-group">
                        <label>Display Duration (Seconds)</label>
                        <p style="font-size: 0.75em; color: var(--text-muted); margin-bottom: 10px;">How long the notification stays visible.</p>
                        <input type="number" name="proof_duration" class="form-control" value="<?= (int)$settings['proof_duration'] ?>" min="2" max="20">
                    </div>
                </div>

                <div class="form-grid" style="margin-top: 20px;">
                    <div class="form-group">
                        <label>Names Pool (One per line)</label>
                        <textarea name="proof_names" class="form-control" rows="10" placeholder="Ahmed&#10;Sarah&#10;..."><?= implode("\n", $settings['proof_names'] ?? []) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Countries Pool (One per line)</label>
                        <textarea name="proof_countries" class="form-control" rows="10" placeholder="Morocco&#10;USA&#10;..."><?= implode("\n", $settings['proof_countries'] ?? []) ?></textarea>
                    </div>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn btn-primary">Save Social Proof settings</button>
                </div>
            </form>
    </div>
</body>
</html>