<?php
require_once dirname(__DIR__) . '/config.php';
require_once ROOT_DIR . '/includes/auth.php';
require_once ROOT_DIR . '/includes/functions.php';

requireLogin();
$settings = readSettings();

$id = (int)($_GET['id'] ?? 0);
$app = getAppById($id);

if (!$app) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }

    $apps = readApps();
    
    // Parse Geo Rules
    $geoRules = [];
    if (!empty($_POST['geo_countries']) && is_array($_POST['geo_countries'])) {
        foreach ($_POST['geo_countries'] as $i => $countriesArr) {
            $link = trim($_POST['geo_links'][$i] ?? '');
            if (!$link || empty($countriesArr) || !is_array($countriesArr)) continue;
            
            $cList = array_filter(array_map('strtoupper', array_map('trim', $countriesArr)));
            if ($cList) {
                $geoRules[] = [
                    'countries' => array_values($cList), 
                    'link' => $link,
                    'name' => trim($_POST['geo_names'][$i] ?? ''),
                    'instructions' => trim($_POST['geo_instructions'][$i] ?? '')
                ];
            }
        }
    }

    // Supported OS
    $os = [];
    if (!empty($_POST['os_android'])) $os[] = '../img/icons/android.png';
    if (!empty($_POST['os_ios'])) $os[] = '../img/icons/apple-logo.png';

    // Update App Data
    foreach ($apps as &$a) {
        if ((int)$a['id'] === $id) {
            $a['name']        = sanitize($_POST['name'] ?? '');
            $a['icon_app']    = sanitizeUrl($_POST['icon_app'] ?? '');
            $a['bg']          = sanitizeUrl($_POST['bg'] ?? '');
            $a['popup_type']     = sanitize($_POST['popup_type'] ?? 'mod');
            $a['resource_icon']  = sanitizeUrl($_POST['resource_icon'] ?? '');
            $a['reward_amounts'] = [
                (int)($_POST['amount_1'] ?? 1000),
                (int)($_POST['amount_2'] ?? 5000),
                (int)($_POST['amount_3'] ?? 8000),
                (int)($_POST['amount_4'] ?? 9999)
            ];
            $a['content']        = sanitize($_POST['content'] ?? '');
            $a['rating']         = (float)($_POST['rating'] ?? 0);
            $a['download']       = sanitize($_POST['download'] ?? '');
            $a['developer']      = sanitize($_POST['developer'] ?? '');
            $a['category']       = sanitize($_POST['category'] ?? '');
            $a['supportedOS']    = $os;
            $a['badge']          = sanitize($_POST['badge'] ?? '');
            $a['coupon_code']    = sanitize($_POST['coupon_code'] ?? '');
            $a['is_trending']    = isset($_POST['is_trending']);
            $a['is_slider']      = isset($_POST['is_slider']);
            $a['is_discover']    = isset($_POST['is_discover']);
            $a['type']           = sanitize($_POST['type'] ?? '');
            $a['s1']             = sanitize($_POST['s1'] ?? '');
            $a['geo']            = $geoRules;
            break;
        }
    }
    writeApps($apps);

    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit App | SpiderMod Admin</title>
    <link rel="stylesheet" href="assets/admin.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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

    <div class="container">
        <div class="card">
            <h2>Edit App: <?= sanitize($app['name']) ?></h2>
            <br>
            <form method="post" action="edit.php?id=<?= $id ?>">
                <?= csrfField() ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="<?= sanitize($app['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Developer</label>
                        <input type="text" name="developer" class="form-control" value="<?= sanitize($app['developer']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Icon URL</label>
                        <input type="url" name="icon_app" class="form-control" value="<?= sanitize($app['icon_app']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Background Image URL</label>
                        <input type="url" name="bg" class="form-control" value="<?= sanitize($app['bg']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Type (e.g., Mod, APK, Point)</label>
                        <input type="text" name="type" class="form-control" value="<?= sanitize($app['type'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control select2-basic" required>
                            <option value="games" <?= $app['category'] === 'games' ? 'selected' : '' ?>>Games</option>
                            <option value="apps" <?= $app['category'] === 'apps' ? 'selected' : '' ?>>Apps</option>
                        </select>
                    </div>
                    <div class="section-divider">POPUP CONFIGURATION</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Popup Flow Type</label>
                            <select name="popup_type" class="form-control select2-basic" required>
                                <option value="mod" <?= ($app['popup_type'] ?? 'mod') === 'mod' ? 'selected' : '' ?>>Mod Popup (Download Style)</option>
                                <option value="reward" <?= ($app['popup_type'] ?? '') === 'reward' ? 'selected' : '' ?>>Reward Popup (Generator Style)</option>
                                <option value="gift" <?= ($app['popup_type'] ?? '') === 'gift' ? 'selected' : '' ?>>Gift Card/Coupon (Voucher Style)</option>
                            </select>
                        </div>
                        <div class="form-group" id="resource-icon-group">
                            <label>Resource Icon URL</label>
                            <input type="url" name="resource_icon" class="form-control" value="<?= sanitize($app['resource_icon'] ?? '') ?>" placeholder="https://i.imgur.com/diamond.png">
                        </div>
                    </div>

                    <div class="form-group" id="coupon-code-group" style="display:none;">
                        <label>Voucher Code (e.g. SP-XXXX-XXXX-1234)</label>
                        <input type="text" name="coupon_code" class="form-control" value="<?= sanitize($app['coupon_code'] ?? '') ?>" placeholder="Enter the full code here">
                        <small style="color:var(--text-muted);">This code will be partially blurred in the frontend until verification is complete.</small>
                    </div>

                    <div class="form-group" id="amounts-group">
                        <label>Selectable Reward Amounts</label>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                            <?php $amts = $app['reward_amounts'] ?? [1000, 5000, 8000, 9999]; ?>
                            <input type="number" name="amount_1" class="form-control" value="<?= $amts[0] ?>">
                            <input type="number" name="amount_2" class="form-control" value="<?= $amts[1] ?>">
                            <input type="number" name="amount_3" class="form-control" value="<?= $amts[2] ?>">
                            <input type="number" name="amount_4" class="form-control" value="<?= $amts[3] ?>">
                        </div>
                    </div>



                    <div class="section-divider">APP DETAILS</div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Rating (e.g. 4.8)</label>
                            <input type="number" step="0.1" name="rating" class="form-control" value="<?= sanitize($app['rating'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Downloads (e.g. 1M)</label>
                            <input type="text" name="download" class="form-control" value="<?= sanitize($app['download'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Badge Label (e.g. FREE)</label>
                            <input type="text" name="badge" class="form-control" value="<?= sanitize($app['badge'] ?? '') ?>" placeholder="Default: FREE">
                        </div>
                        <div class="form-group">
                            <label>S1 Tracking ID (Optional)</label>
                            <input type="text" name="s1" class="form-control" value="<?= sanitize($app['s1'] ?? '') ?>" placeholder="e.g. app_freefire_en">
                            <p style="font-size: 0.75em; color: var(--text-muted); margin-top: 5px;">Appends &s1=... to offer links for postback tracking.</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Trending Status (Manual Selection)</label>
                            <label class="trending-switch">
                                <input type="checkbox" name="is_trending" value="1" <?= !empty($app['is_trending']) ? 'checked' : '' ?>>
                                <span class="trending-slider"></span>
                            </label>
                            <p style="font-size: 0.75em; color: var(--text-muted); margin-top: 5px;">When active, this app appears in the horizontal "Trending" row on the homepage.</p>
                        </div>
                        <div class="form-group">
                            <label>Slider Status (Top Cinematic Slider)</label>
                            <label class="trending-switch">
                                <input type="checkbox" name="is_slider" value="1" <?= !empty($app['is_slider']) ? 'checked' : '' ?>>
                                <span class="trending-slider" style="background-color: var(--secondary);"></span>
                            </label>
                            <p style="font-size: 0.75em; color: var(--text-muted); margin-top: 5px;">When active, this app appears in the large cinematic slider at the top of the homepage.</p>
                        </div>
                        <div class="form-group">
                            <label>Discover Status (Curated Section)</label>
                            <label class="trending-switch">
                                <input type="checkbox" name="is_discover" value="1" <?= !empty($app['is_discover']) ? 'checked' : '' ?>>
                                <span class="trending-slider" style="background-color: #7c3aed;"></span>
                            </label>
                            <p style="font-size: 0.75em; color: var(--text-muted); margin-top: 5px;">When active, this app appears in the "Discover" curated section (Limited to 9 apps / 8 games).</p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description Content</label>
                    <textarea name="content" required><?= sanitize($app['content']) ?></textarea>
                </div>

                <hr style="border:0; border-top:1px solid var(--border); margin: 30px 0;">
                
                <h3>Geo Rules (Optional)</h3>
                <p style="color:var(--text-muted); font-size:0.85em; margin-bottom:15px;">Target specific countries using a different link. Select from the list below.</p>
                
                <div id="geo-rules-container">
                    <?php if (!empty($app['geo'])): ?>
                        <?php foreach ($app['geo'] as $idx => $rule): ?>
                        <div class="geo-rule">
                            <div class="remove-rule" onclick="$(this).parent().remove()">? Remove</div>
                            <div class="form-group">
                                <label style="margin-bottom:8px;">Select Country</label>
                                <select class="form-control select2-countries" name="geo_countries[<?= $idx ?>][]" style="width: 100%;">
                                    <option value="" disabled <?= empty($rule['countries']) ? 'selected' : '' ?>>Search and select a country...</option>
                                    <?php foreach(getCountryList() as $code => $name): ?>
                                    <?php $isSelected = in_array(strtoupper($code), array_map('strtoupper', $rule['countries'] ?? [])); ?>
                                    <option value="<?= $code ?>" <?= $isSelected ? 'selected' : '' ?>><?= sanitize($name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-grid" style="margin-top:10px; grid-template-columns: 1fr 1fr; gap:15px;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label>Offer Name (Title)</label>
                                    <input type="text" name="geo_names[<?= $idx ?>]" class="form-control" value="<?= sanitize($rule['name'] ?? '') ?>" placeholder="e.g. PlayOno - Download Content" required>
                                </div>
                                <div class="form-group" style="margin-bottom:0;">
                                    <label>Offer Instructions (Subtext)</label>
                                    <input type="text" name="geo_instructions[<?= $idx ?>]" class="form-control" value="<?= sanitize($rule['instructions'] ?? '') ?>" placeholder="e.g. ENTER YOUR MOBILE NUMBER" required>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top:10px; margin-bottom:0;">
                                <label>Target Link</label>
                                <input type="url" name="geo_links[<?= $idx ?>]" class="form-control" value="<?= sanitize($rule['link'] ?? '') ?>" required>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="geo-controls">
                    <p class="text-muted" style="font-size:0.85em;font-weight:600;">GEO-TARGETING RULES</p>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addRule()">+ Add Geo Rule</button>
                </div>

                <div class="form-footer">
                    <a href="dashboard.php" class="btn btn-danger">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>



    <!-- Template for new rule -->
    <script type="text/template" id="geo-rule-template">
        <div class="geo-rule">
            <div class="remove-rule" onclick="$(this).parent().remove()">? Remove</div>
            <div class="form-group">
                <label style="margin-bottom:8px;">Select Country</label>
                <select class="form-control select2-countries" name="geo_countries[{INDEX}][]" style="width: 100%;">
                    <option value="" disabled selected>Search and select a country...</option>
                    <?php foreach(getCountryList() as $code => $name): ?>
                    <option value="<?= $code ?>"><?= sanitize($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-grid" style="margin-top:15px; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Offer Name (Title)</label>
                    <input type="text" name="geo_names[{INDEX}]" class="form-control" placeholder="e.g. PlayOno - Download Content" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Offer Instructions (Subtext)</label>
                    <input type="text" name="geo_instructions[{INDEX}]" class="form-control" placeholder="e.g. ENTER YOUR MOBILE NUMBER" required>
                </div>
            </div>
            <div class="form-group" style="margin-top:15px; margin-bottom:0;">
                <label>Target Link</label>
                <input type="url" name="geo_links[{INDEX}]" class="form-control" placeholder="https://..." required>
            </div>
        </div>
    </script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function togglePopupFields() {
            const type = $('select[name="popup_type"]').val();
            if (type === 'reward') {
                $('#resource-icon-group, #amounts-group').show();
                $('#coupon-code-group').hide();
            } else if (type === 'gift') {
                $('#resource-icon-group, #coupon-code-group').show();
                $('#amounts-group').hide();
            } else {
                $('#resource-icon-group, #amounts-group, #coupon-code-group').hide();
            }
        }
        
        $(document).ready(function() {
            togglePopupFields();
            $('select[name="popup_type"]').on('change', togglePopupFields);
            $('.select2-basic').select2({
                minimumResultsForSearch: Infinity
            });
            $('.select2-countries').select2();
        });
    </script>
    <script>
        let ruleIndex = <?= count($app['geo'] ?? []) + 10 ?>;

        function addRule() {
            ruleIndex++;
            const container = document.getElementById('geo-rules-container');
            const template = document.getElementById('geo-rule-template').innerHTML;
            
            const div = document.createElement('div');
            div.innerHTML = template.replace(/{INDEX}/g, ruleIndex);
            const newRule = div.firstElementChild;
            
            container.appendChild(newRule);
            
            $(newRule).find('.select2-countries').select2();
        }
        
        $(document).ready(function() {
            $('.select2-countries').select2();
        });
    </script>
</body>
</html>
