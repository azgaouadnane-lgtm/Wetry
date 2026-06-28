<?php
require_once __DIR__ . '/../includes/license_check.php';
require_once __DIR__ . '/../includes/functions.php';
$settings = readSettings();
$themes = getThemes();
$activeTheme = $themes[$settings['active_theme']] ?? $themes['cyber_blue'];
$colorMode = $settings['color_mode'] ?? 'dark';
?>
<!DOCTYPE html>
<html class="<?= $colorMode ?>" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= sanitize($settings['site_title']) ?></title>
    <meta name="description" content="<?= sanitize($settings['meta_description']) ?>">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <script>
      window.spConfig = <?= json_encode([
          'enabled'               => (bool)$settings['proof_enabled'],
          'interval'              => (int)$settings['proof_interval'] * 1000,
          'duration'              => (int)$settings['proof_duration'] * 1000,
          'names'                 => $settings['proof_names'],
          'countries'             => $settings['proof_countries'],
          'manualCheckEnabled'    => (bool)$settings['manual_check_enabled'],
          'manualCheckAdminMode'  => (bool)$settings['manual_check_admin_mode'],
          'phoneWhitelistId'      => trim($settings['phone_whitelist_id'] ?? '')
      ]) ?>;
    </script>
    <?php if (!empty($settings['ga_measurement_id'])): ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $settings['ga_measurement_id'] ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '<?= $settings['ga_measurement_id'] ?>');
    </script>
    <?php endif; ?>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "<?= $activeTheme['primary'] ?>",
                        "primary-container": "<?= $activeTheme['primary_container'] ?>",
                        secondary: "<?= $activeTheme['secondary'] ?>",
                        background: "<?= $colorMode === 'dark' ? '#090e1c' : '#f8fafc' ?>",
                        "surface-variant": "<?= $colorMode === 'dark' ? '#1e253b' : '#ffffff' ?>",
                    },
                    fontFamily: {
                        headline: ["Space Grotesk"],
                        body: ["Inter"],
                        label: ["Inter"]
                    },
                    keyframes: {
                        shake: {
                            '0%, 100%': { transform: 'translateX(0)' },
                            '25%': { transform: 'translateX(-5px)' },
                            '75%': { transform: 'translateX(5px)' },
                        }
                    },
                    animation: {
                        shake: 'shake 0.2s ease-in-out 0s 2',
                    }
                }
            }
        };
    </script>

    <script>
        window.spConfig = {
            ...(window.spConfig || {}),
            activeTheme: '<?= $settings['active_theme'] ?>'
        };
    </script>
<?php
    $isLegacy = ($settings['active_theme'] === 'cyber_blue');
?>

    <style>
        :root {
            --primary: <?= $activeTheme['primary'] ?>;
            --secondary: <?= $activeTheme['secondary'] ?>;
            --primary-container: <?= $activeTheme['primary_container'] ?>;
            --primary-glow: <?= $activeTheme['primary'] ?>33;
            --primary-glow-strong: <?= $activeTheme['primary'] ?>66;
            --primary-gradient: <?= $isLegacy ? 'linear-gradient(135deg, #93c5fd, #c084fc)' : 'linear-gradient(135deg, ' . $activeTheme['primary'] . ', ' . $activeTheme['secondary'] . ')' ?>;
        }
        body { background-color: #090e1c; color: #e1e4fa; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .glass-panel {
            background: rgba(30,37,59,0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .neon-glow-primary { box-shadow: 0 0 20px <?= $isLegacy ? 'rgba(129,236,255,0.3)' : 'var(--primary-glow)' ?>; }
        .neon-glow-secondary { box-shadow: 0 0 20px <?= $isLegacy ? 'rgba(175,136,255,0.3)' : $activeTheme['secondary'] . '33' ?>; }
        .neon-glow-success { box-shadow: 0 0 30px rgba(34,197,94,0.35), 0 0 60px rgba(34,197,94,0.15); }
        .btn-gradient { background: var(--primary-gradient); box-shadow: 0 0 15px <?= $isLegacy ? 'rgba(175,136,255,0.3)' : 'var(--primary-glow)' ?>; transition: all 0.3s; }
        .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 0 25px var(--primary-glow-strong); }

        /* Hide scrollbars but keep functionality */
        * { -ms-overflow-style: none !important; scrollbar-width: none !important; }
        *::-webkit-scrollbar { display: none !important; }

        /* Slider */
        #slider-container .slide { transition: opacity 0.8s ease-in-out, transform 0.8s ease-in-out; }
        .slide-enter { opacity: 0; transform: scale(1.02); }
        .slide-active { opacity: 1; transform: scale(1); }
        .slider-dot { transition: all 0.3s ease; }
        .slider-dot.active { background: var(--primary); width: 24px; }
        .slider-overlay-glass {
            background: linear-gradient(90deg,
                rgba(9, 14, 28, 0.98) 0%,
                rgba(9, 14, 28, 0.85) 30%,
                rgba(9, 14, 28, 0.4) 70%,
                rgba(9, 14, 28, 0.1) 100%
            );
        }
        @media (max-width: 768px) {
            .slider-overlay-glass {
                background: linear-gradient(0deg,
                    rgba(9, 14, 28, 1) 0%,
                    rgba(9, 14, 28, 0.7) 60%,
                    rgba(9, 14, 28, 0.3) 100%
                );
            }
        }
        .rating-pill {
            background: rgba(129, 236, 255, 0.15);
            border: 1px solid rgba(129, 236, 255, 0.3);
            box-shadow: 0 0 15px rgba(129, 236, 255, 0.1);
        }
        .slider-icon-outer {
            box-shadow: 0 15px 40px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05);
        }

        /* Flow & Progress */
        .progress-bar-container { height: 10px; background: rgba(255,255,255,0.05); border-radius: 5px; overflow: hidden; margin: 15px 0; border: 1px solid var(--primary-glow); }
        .progress-bar-fill { height: 100%; background: var(--primary-gradient); width: 0%; transition: width 0.3s ease; box-shadow: 0 0 15px var(--primary-glow-strong); }
        .flow-step { display: none; }
        .flow-step.active { display: block; animation: fadeIn 0.3s ease; }
        /* Status steps need flex layout */
        [id$="-step-status"].active { display: flex !important; flex-direction: column; align-items: center; justify-content: center; }
        .flow-hidden { display: none !important; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes logPulse { 0% { opacity: 0.5; } 50% { opacity: 1; } 100% { opacity: 0.5; } }
        .log-line { font-family: 'Space Grotesk', sans-serif; font-size: 0.8em; color: var(--primary); margin-bottom: 6px; border-left: 2px solid var(--primary); background: var(--primary-glow); padding: 5px 10px; border-radius: 4px; }
        .log-line.pending { animation: logPulse 1.5s infinite; color: var(--secondary); border-color: var(--secondary); }

        /* Amounts grid */
        .amounts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .amt-btn { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 15px; text-align: center; cursor: pointer; transition: 0.2s; }
        .amt-btn:hover { background: var(--primary-glow); border-color: var(--primary); transform: translateY(-2px); }
        .amt-btn.selected { background: var(--primary-gradient); border-color: var(--primary); color: #000; font-weight: 800; box-shadow: 0 0 20px var(--primary-glow-strong); }

        /* Platform buttons */
        .platform-btn.selected {
            border-color: var(--primary) !important;
            background: var(--primary-glow) !important;
            box-shadow: 0 0 25px var(--primary-glow-strong) !important;
        }
        .platform-btn.selected svg { fill: var(--primary) !important; opacity: 1 !important; }
        .platform-btn.selected span { color: var(--primary) !important; }

        .resource-preview { width: 50px; height: 50px; object-fit: contain; margin: 0 auto 5px; filter: drop-shadow(0 0 5px var(--primary-glow-strong)); }

        /* Modals scrollable on mobile */
        .glass-panel { max-height: 90vh; overflow-y: auto; overflow-x: hidden; }
        @media (max-width: 640px) {
            .glass-panel { max-height: 88vh; margin: 10px; }
        }

        /* Trending cards */
        .trending-card { flex-shrink: 0; width: 140px; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); cursor: pointer; }
        .trending-card:hover { transform: translateY(5px) scale(0.98); opacity: 0.9; }
        .trending-icon-wrap { position: relative; background: #1e253b; border: 1px solid rgba(129,236,255,0.1); padding: 15px; border-radius: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); margin-bottom: 12px; transition: all 0.3s; }
        .trending-card:hover .trending-icon-wrap { border-color: var(--primary); box-shadow: 0 15px 35px rgba(129,236,255,0.15); }
        .trending-badge { display: inline-flex; align-items: center; gap: 4px; background: rgba(129,236,255,0.1); border: 1px solid rgba(129,236,255,0.2); color: #81ecff; font-size: 9px; font-weight: 800; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .trending-badge .material-symbols-outlined { font-size: 12px; }

        /* Proof Notifications */
        #proof-notification.show { transform: translateY(0); opacity: 1; pointer-events: auto; }
        @media (max-width: 768px) {
            #proof-notification { bottom: 24px; left: 20px; right: 20px; max-width: none; }
        }
    </style>
</head>
<body class="bg-background overflow-x-hidden font-body selection:bg-primary/30 selection:text-white">

    <!-- Sidebar Navbar -->
    <aside class="h-screen w-[260px] fixed left-0 top-0 z-40 bg-[#0d1323]/80 backdrop-blur-xl shadow-[20px_0_40px_rgba(129,236,255,0.05)] flex flex-col py-8 transition-transform duration-300 md:translate-x-0 -translate-x-full border-r border-[#1e253b]/50" id="sidebar">
        <button id="closeSidebar" class="md:hidden absolute top-4 right-4 text-slate-400 hover:text-white">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="px-6 mb-10">
            <div class="flex items-center gap-3">
                <img src="<?= sanitize((strpos($settings['logo_url'] ?? '', 'http') === 0) ? $settings['logo_url'] : ltrim($settings['logo_url'] ?? 'public/assets/img/555.png', '/')) ?>" alt="SpiderMod Logo" class="w-10 h-10 object-contain brand-icon" style="filter: drop-shadow(0 0 8px <?= $isLegacy ? '#81ecff' : 'var(--primary)' ?>);">
                <div>
                    <?php if ($isLegacy): ?>
                        <h1 class="text-xl font-bold tracking-tighter text-[#81ecff] font-headline" style="text-shadow: 0 0 12px rgba(129,236,255,0.5);"><?= htmlspecialchars($settings['brand_name'] ?? 'SpiderMod') ?></h1>
                    <?php else: ?>
                        <h1 class="text-xl font-bold tracking-tighter bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary font-headline filter drop-shadow-[0_0_8px_var(--primary-glow)]"><?= htmlspecialchars($settings['brand_name'] ?? 'SpiderMod') ?></h1>
                    <?php endif; ?>
                    <p class="text-[9px] tracking-[0.2em] text-slate-500 uppercase font-label">Premium Access</p>
                </div>
            </div>
        </div>
        <nav class="flex-1 space-y-2" id="main-nav">
            <a class="nav-item active <?= $isLegacy ? 'text-[#81ecff] border-[#00e3fd]' : 'text-primary border-primary' ?> border-l-4 bg-[#1e253b]/50 px-6 py-4 flex items-center gap-4 transition-all" href="#" data-view="discover">
                <span class="material-symbols-outlined">explore</span>
                <span class="font-bold tracking-wide">Discover</span>
            </a>
            <a class="nav-item text-slate-400 px-6 py-4 flex items-center gap-4 hover:text-primary hover:bg-white/5 transition-all duration-300" href="#" data-view="apps">
                <span class="material-symbols-outlined">apps</span>
                <span class="font-label tracking-widest text-sm">Apps</span>
            </a>
            <a class="nav-item text-slate-400 px-6 py-4 flex items-center gap-4 hover:text-primary hover:bg-white/5 transition-all duration-300" href="#" data-view="games">
                <span class="material-symbols-outlined">sports_esports</span>
                <span class="font-label tracking-widest text-sm">Games</span>
            </a>
            <a class="nav-item text-slate-400 px-6 py-4 flex items-center gap-4 hover:text-primary hover:bg-white/5 transition-all duration-300" href="#" data-view="tutorial">
                <span class="material-symbols-outlined">help_center</span>
                <span class="font-label tracking-widest text-sm">How it work !</span>
            </a>
        </nav>

        <?php
        $socials = [
            'social_telegram'  => ['icon' => 'send',         'label' => 'Telegram'],
            'social_instagram' => ['icon' => 'photo_camera', 'label' => 'Instagram'],
            'social_youtube'   => ['icon' => 'play_circle',  'label' => 'YouTube'],
            'social_tiktok'    => ['icon' => 'music_note',   'label' => 'TikTok'],
            'social_facebook'  => ['icon' => 'facebook',     'label' => 'Facebook'],
        ];
        $hasAnySocial = false;
        foreach ($socials as $key => $data) {
            if (!empty($settings[$key])) { $hasAnySocial = true; break; }
        }
        ?>

        <?php if ($hasAnySocial): ?>
        <div class="px-6 py-6 border-t border-[#1e253b]/50">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-4">Join Community</p>

            <?php if (!empty($settings['social_telegram'])): ?>
                <a href="<?= sanitize($settings['social_telegram']) ?>" target="_blank" class="flex items-center gap-3 px-3 py-3 mb-4 rounded-xl bg-gradient-to-r from-primary/20 to-secondary/20 border border-primary/30 text-primary hover:scale-[1.02] transition-all group overflow-hidden relative shadow-[0_0_20px_rgba(var(--primary-rgb),0.1)]">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <span class="material-symbols-outlined text-xl group-hover:rotate-12 transition-transform relative z-10">send</span>
                    <div class="relative z-10 min-w-0">
                        <p class="text-[9px] font-black uppercase tracking-wider leading-none mb-1 opacity-70">Official News</p>
                        <p class="text-[10px] font-black uppercase tracking-wider truncate">Join Telegram Channel</p>
                    </div>
                </a>
            <?php endif; ?>

            <div class="grid grid-cols-5 gap-2">
                <?php foreach ($socials as $key => $data): ?>
                    <?php if ($key !== 'social_telegram' && !empty($settings[$key])): ?>
                        <a href="<?= sanitize($settings[$key]) ?>" target="_blank" title="<?= $data['label'] ?>" class="w-10 h-10 rounded-xl bg-white/5 border border-white/5 flex items-center justify-center text-slate-400 hover:bg-white/10 hover:text-white transition-all group">
                            <span class="material-symbols-outlined text-lg group-hover:scale-110 transition-transform"><?= $data['icon'] ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Main Workspace -->
    <main class="md:ml-[260px] min-h-screen">

        <!-- Topheader -->
        <header class="fixed top-0 right-0 w-full md:w-[calc(100%-260px)] h-20 z-30 bg-[#090e1c]/80 backdrop-blur-md flex items-center justify-between px-6 md:px-12 border-b border-[#1e253b]/50">
            <button id="openSidebar" class="md:hidden text-slate-400 hover:text-primary mr-4">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <div class="flex items-center flex-1 max-w-xl md:opacity-0 pointer-events-none"></div>
        </header>

        <!-- Canvas -->
        <div class="pt-28 px-6 md:px-12 pb-20 max-w-7xl mx-auto">

            <!-- Dynamic Cinematic Slider -->
            <section id="slider-container" class="relative h-[380px] md:h-[450px] w-full rounded-3xl overflow-hidden mb-12 lg:mb-16 shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-[#1e253b] bg-[#0d1323]">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-10 h-10 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
                </div>
            </section>

            <!-- Trending Selection Row -->
            <section id="trending-section" class="hidden mb-6 lg:mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold font-headline tracking-tight text-white/90">Curated Trending</h3>
                </div>
                <div id="trending-container" class="flex justify-center gap-6 overflow-x-auto pb-6 -mx-2 px-2">
                    <!-- JS populates -->
                </div>
            </section>

            <!-- Search Bar -->
            <div class="max-w-3xl mx-auto mb-16">
                <div class="relative w-full group">
                    <span class="material-symbols-outlined absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-primary transition-colors z-10 text-2xl">search</span>
                    <input id="searchInput" class="w-full bg-[#000000]/40 backdrop-blur-md border border-white/10 rounded-2xl py-4 pl-14 pr-6 text-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder:text-slate-500 outline-none text-white shadow-2xl" placeholder="Search curated universe..." type="text"/>
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-primary/5 to-secondary/5 opacity-0 group-focus-within:opacity-100 transition-opacity pointer-events-none"></div>
                </div>
            </div>

            <section id="main-section">
                <div class="flex items-center justify-between mb-8">
                    <h3 id="section-title" class="text-2xl font-bold font-headline tracking-tight text-white">Curated Collection</h3>
                </div>
                <div id="apps-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                    <div class="col-span-full py-10 flex justify-center">
                        <div class="w-8 h-8 rounded-full border-2 border-primary border-t-transparent animate-spin"></div>
                    </div>
                </div>
                <div id="no-results" class="hidden text-center py-20 text-slate-500">
                    <span class="material-symbols-outlined text-6xl mb-4 opacity-50">search_off</span>
                    <h3 class="text-xl font-headline text-white mb-2">No matches found</h3>
                    <p>Try adjusting your search query</p>
                </div>
            </section>

            <!-- How it Work! Tutorial Section -->
            <section id="tutorial-section" class="hidden pb-12">
                <div class="flex items-center gap-3 mb-6">
                    <span class="material-symbols-outlined text-primary text-3xl">school</span>
                    <div>
                        <h3 class="text-2xl font-bold font-headline tracking-tight text-white">How it work !</h3>
                        <p class="text-slate-400 text-sm mt-1">Select a task type to simulate and learn the completion process safely.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">

                    <!-- Phone Verification -->
                    <div class="bg-white/5 border border-white/10 hover:border-primary/50 rounded-2xl p-6 transition-all duration-300 group cursor-pointer" onclick="openTutorialFlow('phone')">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary to-primary-container flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-white text-2xl">smartphone</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-white mb-1">Phone Verification</h4>
                                <p class="text-slate-400 text-sm mb-3">Simulate entering your mobile number to receive a secure SMS PIN code.</p>
                                <button class="w-full py-2.5 rounded-lg bg-primary/10 text-primary font-bold text-sm hover:bg-primary/20 transition-colors">Start Training</button>
                            </div>
                        </div>
                    </div>

                    <!-- App Install -->
                    <div class="bg-white/5 border border-white/10 hover:border-secondary/50 rounded-2xl p-6 transition-all duration-300 group cursor-pointer" onclick="openTutorialFlow('app')">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-secondary to-purple-600 flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-white text-2xl">download</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-white mb-1">Application Install</h4>
                                <p class="text-slate-400 text-sm mb-3">Learn how to securely download, install, and run required sponsored apps.</p>
                                <button class="w-full py-2.5 rounded-lg bg-secondary/10 text-secondary font-bold text-sm hover:bg-secondary/20 transition-colors">Start Training</button>
                            </div>
                        </div>
                    </div>

                    <!-- Survey -->
                    <div class="bg-white/5 border border-white/10 hover:border-green-500/50 rounded-2xl p-6 transition-all duration-300 group cursor-pointer" onclick="openTutorialFlow('survey')">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-white text-2xl">assignment</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-white mb-1">Survey Completion</h4>
                                <p class="text-slate-400 text-sm mb-3">Practice answering brief questions accurately to bypass market research lockers.</p>
                                <button class="w-full py-2.5 rounded-lg bg-green-500/10 text-green-500 font-bold text-sm hover:bg-green-500/20 transition-colors">Start Training</button>
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="bg-[#1e253b]/40 border border-[#1e253b] hover:border-orange-500/50 rounded-2xl p-6 transition-all duration-300 group cursor-pointer" onclick="openTutorialFlow('email')">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-white text-2xl">mail</span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-bold text-white mb-1">Email Registration</h4>
                                <p class="text-slate-400 text-sm mb-3">See how a standard email sign-up offer functions to unlock your rewards.</p>
                                <button class="w-full py-2.5 rounded-lg bg-orange-500/10 text-orange-400 font-bold text-sm hover:bg-orange-500/20 transition-colors">Start Training</button>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>
    </main>

    <!-- ═══════════════════════════════════════════════════════════════════════
         MODALS — Each popup lives in its own independent file
         ═══════════════════════════════════════════════════════════════════════ -->

    <?php
    if ($GLOBALS['IS_LICENSE_VALID'] === true) {
        $token = $GLOBALS['SPIDERMOD_UNLOCK_TOKEN'];
        $partials = ['modal-mod.enc', 'modal-reward.enc', 'modal-gift.enc'];
        foreach ($partials as $encFile) {
            $payloadPath = __DIR__ . '/partials/' . $encFile;
            if (file_exists($payloadPath)) {
                $decoded = base64_decode(file_get_contents($payloadPath));
                $ivLen = openssl_cipher_iv_length('aes-256-cbc');
                $iv = substr($decoded, 0, $ivLen);
                $ciphertext = substr($decoded, $ivLen);
                $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $token, 0, $iv);
                if ($decrypted !== false) {
                    eval('?>' . $decrypted);
                }
            }
        }
    } else {
        // Show uncancelable overlay
        ?>
        <div style="position:fixed; inset:0; z-index:9999999; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.85); backdrop-filter:blur(8px);">
            <div style="background:#0f172a; border:2px solid #ef4444; border-radius:24px; padding:40px; max-width:400px; text-align:center; box-shadow:0 25px 50px -12px rgba(239,68,68,0.25);">
                <span class="material-symbols-outlined" style="color:#ef4444; font-size:48px; margin-bottom:16px;">lock</span>
                <h2 style="color:#f87171; font-size:24px; font-weight:800; margin:0 0 16px 0; font-family:sans-serif;">License Missing</h2>
                <p style="color:#94a3b8; font-size:15px; line-height:1.6; margin:0 0 24px 0; font-family:sans-serif;">
                    <?= htmlspecialchars($GLOBALS['LICENSE_ERROR_MESSAGE'] ?? 'License is missing or invalid.') ?>
                </p>
                <a href="Admin/index.php" style="display:inline-block; background:#ef4444; color:#fff; font-weight:bold; text-decoration:none; padding:12px 24px; border-radius:12px; font-family:sans-serif;">
                    Go to Admin Panel
                </a>
            </div>
        </div>
        <style>
            /* Disable scrolling when locked */
            body { overflow: hidden !important; }
        </style>
        <?php
    }
    ?>

    <!-- ─── Tutorial Simulation Modal ────────────────────────────────────── -->
    <div id="tutorialModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-md" onclick="closeTutorialModal()"></div>
        <div class="relative w-full max-w-md bg-[#0a0f1d] rounded-3xl border border-[#1e253b] shadow-[0_20px_50px_rgba(0,0,0,0.9)] overflow-hidden scale-95 transition-all duration-300" id="tutorialModalContent">
            <button onclick="closeTutorialModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-slate-400 hover:text-white transition-all z-10">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
            <div id="tutorial-dynamic-content" class="p-6 pt-10 min-h-[350px]">
                <!-- Injected via JS -->
            </div>
        </div>
    </div>

    <script src="public/assets/js/app.js?v=<?= time() ?>"></script>

    <!-- Mobile Sidebar Logic -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');
        openBtn.addEventListener('click', () => sidebar.classList.remove('-translate-x-full'));
        closeBtn.addEventListener('click', () => sidebar.classList.add('-translate-x-full'));
    </script>

    <!-- Social Proof Notification -->
    <div id="proof-notification" class="fixed bottom-6 left-6 z-50 flex items-center gap-4 bg-[#090e1c]/80 backdrop-blur-xl border border-white/10 rounded-2xl p-4 shadow-2xl transition-all duration-700 translate-y-20 opacity-0 pointer-events-none max-w-[320px]">
        <div id="proof-icon-container" class="w-12 h-12 rounded-xl bg-black border border-white/5 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-lg">
            <img id="proof-icon" src="" alt="" class="w-full h-full object-cover">
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
                <p id="proof-name" class="text-white font-bold text-sm truncate"></p>
                <span class="flex items-center gap-1 text-[10px] text-green-400 font-bold uppercase tracking-wider">
                    <span class="material-symbols-outlined text-[12px]">verified</span> Validated
                </span>
            </div>
            <p id="proof-text" class="text-slate-400 text-xs mt-0.5 leading-tight truncate"></p>
            <p id="proof-time" class="text-slate-500 text-[9px] mt-1 uppercase tracking-tighter">Just Now</p>
        </div>
    </div>

</body>
</html>
