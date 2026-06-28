/**
 * SPIDERMOD — Neon Curator Frontend
 * Dynamic App Grid with Multi-Modal System + Tutorial Simulator
 */

(function () {
    'use strict';

    let allApps = [];
    let userCountry = 'XX';
    let currentView = 'discover';
    let searchQuery = '';

    const $grid        = document.getElementById('apps-container');
    const $searchInput = document.getElementById('searchInput');
    const $noResults   = document.getElementById('no-results');

    // ── Independent Modal Containers ────────────────────────────────────────
    const $modModal    = document.getElementById('modModal');
    const $rewardModal = document.getElementById('rewardModal');
    const $giftModal   = document.getElementById('giftModal');

    let activeModal = null;

    const $sectionTitle = document.getElementById('section-title');
    const $navItems     = document.querySelectorAll('.nav-item');
    const $slider       = document.getElementById('slider-container');
    const $sidebar      = document.getElementById('sidebar');

    let sliderInterval;
    let currentSlide = 0;
    let trendingPool = [];

    const names     = window.spConfig?.names     || [];
    const countries = window.spConfig?.countries || [];

    // ── State ────────────────────────────────────────────────────────────────
    let currentApp       = null;
    let selectedPlatform = null;
    let selectedAmount   = null;
    let leadCheckInterval = null;

    // ── Init ─────────────────────────────────────────────────────────────────
    fetchApps();
    setupNav();

    // ─── Navigation ──────────────────────────────────────────────────────────
    function setupNav() {
        $navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const view = item.dataset.view;
                if (!view) return;
                currentView = view;

                $navItems.forEach(i => {
                    i.classList.remove('active', 'text-primary', 'border-l-4', 'border-primary', 'bg-white/5');
                    i.classList.add('text-slate-400');
                });
                item.classList.add('active', 'text-primary', 'border-l-4', 'border-primary', 'bg-white/5');
                item.classList.remove('text-slate-400');

                if ($sidebar) $sidebar.classList.add('-translate-x-full');
                render();
            });
        });
    }

    // ─── Fetch Apps from API ─────────────────────────────────────────────────
    async function fetchApps() {
        showSkeletons();
        try {
            const res  = await fetch('api/apps.php?v=' + Date.now());
            const data = await res.json();
            allApps = data.apps || [];

            let country = data.country || 'XX';
            if (country === 'XX') {
                try {
                    const ipRes  = await fetch('https://ipapi.co/json/');
                    const ipData = await ipRes.json();
                    if (ipData.country_code) country = ipData.country_code;
                } catch(e) { console.warn('Fallback Geo failed', e); }
            }

            userCountry  = country.toUpperCase();
            trendingPool = data.trending || [];

            render();
            renderSlider();
            renderTrendingSection();
            if (window.spConfig?.enabled) startProofEngine();
        } catch (err) {
            console.error('Failed to load apps:', err);
            if ($grid) $grid.innerHTML = '<p class="col-span-full text-center text-slate-500 py-10">Failed to load apps. Please refresh.</p>';
        }
    }

    // ─── Slider ───────────────────────────────────────────────────────────────
    function renderSlider() {
        if (!$slider || trendingPool.length === 0) return;

        const slidesHtml = trendingPool.map((app, idx) => `
            <div class="slide absolute inset-0 z-0 ${idx === 0 ? 'slide-active active z-10' : 'slide-enter z-0'}" data-slide="${idx}">
                <img alt="${escHtml(app.name)}" class="absolute inset-0 w-full h-full object-cover blur-[1px] scale-[1.01]" src="${escHtml(app.bg || app.icon_app)}"/>
                <div class="absolute inset-0 slider-overlay-glass"></div>
                <div class="relative z-10 h-full flex flex-col md:flex-row items-center justify-center md:justify-start px-6 md:px-20 pt-10 md:pt-0 gap-4 md:gap-12">
                    <div class="slider-icon-outer w-20 h-20 md:w-56 md:h-56 rounded-2xl md:rounded-[2.5rem] overflow-hidden flex-shrink-0 transition-all duration-700">
                        <img src="${escHtml(app.icon_app)}" class="w-full h-full object-cover" alt="Icon">
                    </div>
                    <div class="flex-none md:flex-1 text-center md:text-left max-w-2xl">
                        <div class="flex flex-col md:flex-row items-center md:items-end justify-center md:justify-start gap-2 md:gap-6 mb-3 md:mb-6">
                            <h2 class="text-2xl md:text-6xl font-black font-headline tracking-tighter text-white uppercase drop-shadow-2xl">${escHtml(app.name)}</h2>
                            <div class="rating-pill flex items-center gap-1.5 px-3 py-0.5 rounded-full backdrop-blur-xl mb-1 md:mb-3">
                                <span class="material-symbols-outlined text-[#81ecff] text-[10px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                <span class="text-[9px] font-black text-white tracking-widest">5.0</span>
                            </div>
                        </div>
                        <p class="text-slate-100 text-xs md:text-lg mb-6 md:mb-8 leading-relaxed font-body line-clamp-2 md:line-clamp-3 opacity-90 drop-shadow-lg max-w-xl">
                            ${escHtml(app.content) || (app.name + ' - Epic masterpiece, optimized for your device.')}
                        </p>
                        <div class="flex items-center justify-center md:justify-start gap-4">
                            <button onclick="window.openAppModal(${app.id})" class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#af88ff] to-[#81ecff] text-[#1e1b4b] font-black text-[10px] tracking-[0.2em] shadow-[0_15px_40px_rgba(129,236,255,0.3)] transition-all hover:scale-105 active:scale-95 neon-glow-primary uppercase">
                                Open Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        const dotsHtml = `<div class="absolute bottom-3 left-1/2 -translate-x-1/2 md:left-auto md:translate-x-0 md:bottom-8 md:right-12 z-20 flex gap-1.5 md:gap-2">
            ${trendingPool.map((_, idx) => `<div class="slider-dot w-1.5 h-1.5 md:w-2 md:h-2 rounded-full bg-white/20 cursor-pointer ${idx === 0 ? 'active' : ''}" data-dot="${idx}" onclick="window.goToSlide(${idx})"></div>`).join('')}
        </div>`;

        $slider.innerHTML = slidesHtml + dotsHtml;
        startSliderTimer();
    }

    function renderTrendingSection() {
        const $trendingSection   = document.getElementById('trending-section');
        const $trendingContainer = document.getElementById('trending-container');
        if (!$trendingSection || !$trendingContainer) return;

        const trendingApps = allApps.filter(app => app.is_trending);
        if (trendingApps.length === 0) { $trendingSection.classList.add('hidden'); return; }

        $trendingSection.classList.remove('hidden');
        $trendingContainer.innerHTML = trendingApps.map(app => `
            <div class="trending-card group" onclick="window.openAppModal(${app.id})">
                <div class="trending-icon-wrap">
                    <img src="${escHtml(app.icon_app)}" alt="${escHtml(app.name)}" class="w-full h-full object-cover rounded-xl shadow-lg">
                    <div class="absolute inset-0 rounded-2xl border border-white/5 group-hover:border-primary/30 transition-colors"></div>
                </div>
                <div class="px-1 text-center">
                    <h4 class="text-xs font-bold text-white mb-1 truncate">${escHtml(app.name)}</h4>
                    <div class="trending-badge">
                        <span class="material-symbols-outlined">${app.category === 'games' ? 'sports_esports' : 'apps'}</span>
                        ${escHtml(app.category || 'App')}
                    </div>
                </div>
            </div>
        `).join('');
    }

    function startSliderTimer() {
        if (trendingPool.length <= 1) return;
        clearInterval(sliderInterval);
        sliderInterval = setInterval(() => {
            const next = (currentSlide + 1) % trendingPool.length;
            goToSlide(next);
        }, 4000);
    }

    function goToSlide(index) {
        if (index === currentSlide) return;
        const slides = $slider.querySelectorAll('.slide');
        const dots   = $slider.querySelectorAll('.slider-dot');
        if (!slides.length) return;

        slides[currentSlide].classList.remove('slide-active', 'active', 'z-10');
        slides[currentSlide].classList.add('slide-enter', 'z-0');
        dots[currentSlide].classList.remove('active');

        currentSlide = index;

        slides[currentSlide].classList.remove('slide-enter', 'z-0');
        slides[currentSlide].classList.add('slide-active', 'active', 'z-10');
        dots[currentSlide].classList.add('active');

        startSliderTimer();
    }

    // ─── Render ───────────────────────────────────────────────────────────────
    function render() {
        const trendingSection  = document.getElementById('trending-section');
        const searchContainer  = document.querySelector('.max-w-3xl.mx-auto.mb-16');
        const mainSection      = document.getElementById('main-section');
        const tutorialSection  = document.getElementById('tutorial-section');

        if (currentView === 'tutorial') {
            mainSection    && mainSection.classList.add('hidden');
            $slider        && $slider.classList.add('hidden');
            trendingSection && trendingSection.classList.add('hidden');
            searchContainer && searchContainer.classList.add('hidden');
            tutorialSection && tutorialSection.classList.remove('hidden');
            return;
        } else {
            mainSection    && mainSection.classList.remove('hidden');
            $slider        && $slider.classList.remove('hidden');
            const hasTrending = allApps.some(app => app.is_trending);
            if (hasTrending) trendingSection && trendingSection.classList.remove('hidden');
            searchContainer && searchContainer.classList.remove('hidden');
            tutorialSection && tutorialSection.classList.add('hidden');
        }

        let filtered = allApps;
        if (searchQuery.trim()) {
            const q = searchQuery.toLowerCase();
            filtered = allApps.filter(app => app.name.toLowerCase().includes(q));
        }

        $grid.innerHTML = '';
        $noResults.classList.add('hidden');

        if (currentView === 'discover' && !searchQuery.trim()) {
            renderDiscover(filtered);
        } else {
            renderCategoryView(filtered);
        }
    }

    function renderDiscover(apps) {
        $sectionTitle.textContent = 'Curated Universe';
        const discoverMode = apps.filter(a => a.is_discover);
        const topApps  = discoverMode.filter(a => (a.category || '').toLowerCase() === 'apps').slice(0, 9);
        const topGames = discoverMode.filter(a => (a.category || '').toLowerCase() === 'games').slice(0, 8);

        let html = '';
        const isLegacy = window.spConfig.activeTheme === 'cyber_blue';

        if (topApps.length > 0) {
            html += `<div class="col-span-full mb-6 mt-4"><h3 class="text-xl font-bold font-headline text-${isLegacy ? '[#81ecff]' : 'primary'} flex items-center gap-2"><span class="material-symbols-outlined">apps</span> FEATURED APPS</h3></div>`;
            html += topApps.map(app => renderCard(app)).join('');
        }
        if (topGames.length > 0) {
            html += `<div class="col-span-full mb-6 mt-10 border-t border-white/5 pt-10"><h3 class="text-xl font-bold font-headline text-${isLegacy ? '[#af88ff]' : 'secondary'} flex items-center gap-2"><span class="material-symbols-outlined">sports_esports</span> FEATURED GAMES</h3></div>`;
            html += topGames.map(app => renderCard(app)).join('');
        }

        if (html === '') { $noResults.classList.remove('hidden'); }
        else { $grid.innerHTML = html; attachCardEvents(); }
    }

    function renderCategoryView(apps) {
        let title = 'All Curated Content';
        let displayApps = apps;
        if (currentView === 'apps')  { title = 'Curated Applications'; displayApps = apps.filter(a => (a.category || '').toLowerCase() === 'apps'); }
        if (currentView === 'games') { title = 'Premium Games';        displayApps = apps.filter(a => (a.category || '').toLowerCase() === 'games'); }

        $sectionTitle.textContent = title;
        if (displayApps.length === 0) { $noResults.classList.remove('hidden'); }
        else { $grid.innerHTML = displayApps.map(app => renderCard(app)).join(''); attachCardEvents(); }
    }

    function renderCard(app) {
        const isLegacy = window.spConfig.activeTheme === 'cyber_blue';
        return `
            <div class="relative bg-[#0d1323] rounded-[1.5rem] p-6 border border-[#1e253b] hover:border-${isLegacy ? '[#81ecff]/50' : 'primary/50'} transition-all duration-300 hover:-translate-y-1 cursor-pointer flex flex-col h-full shadow-[0_10px_30px_rgba(0,0,0,0.5)] group" data-app-id="${app.id}">
                <div class="flex justify-between items-start mb-5">
                    <img alt="${escHtml(app.name)}" class="w-20 h-20 rounded-[1.2rem] object-cover bg-white shadow-lg" src="${escHtml(app.icon_app)}" onerror="this.src='https://via.placeholder.com/80?text=App'"/>
                    <span class="px-3 py-1.5 text-[10px] font-bold tracking-widest uppercase rounded-full ${isLegacy ? 'bg-[#d0b8ff] text-[#2b0065] shadow-[0_0_20px_rgba(175,136,255,0.4)]' : 'bg-secondary/20 text-secondary shadow-[0_0_20px_rgba(var(--secondary-rgb),0.4)]'}">
                        ${escHtml(app.badge) || 'FREE'}
                    </span>
                </div>
                <div class="mb-4 flex-1">
                    <h4 class="font-bold text-xl font-headline text-white mb-2 line-clamp-1">${escHtml(app.name)}</h4>
                    <p class="text-sm text-slate-400 line-clamp-2 leading-relaxed">${escHtml(app.content) || 'Tap to instantly unlock secure access to ' + escHtml(app.name) + '.'}</p>
                </div>
                <div class="flex justify-between items-end mt-auto pt-2">
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase tracking-widest mb-1">RATING</p>
                        <p class="font-bold text-white text-lg flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px] text-${isLegacy ? '[#af88ff]' : 'secondary'}" style="font-variation-settings: 'FILL' 1;">star</span>
                            ${escHtml(app.rating) || '4.8'}
                        </p>
                    </div>
                    <button class="px-6 py-2.5 rounded-xl bg-gradient-to-r ${isLegacy ? 'from-[#93c5fd] to-[#c084fc] shadow-[0_0_15px_rgba(175,136,255,0.3)]' : 'from-primary to-secondary shadow-[0_0_15px_rgba(var(--primary-rgb),0.3)]'} text-white font-bold text-sm group-hover:scale-105 transition-transform">
                        GET
                    </button>
                </div>
                <div class="absolute inset-0 bg-${isLegacy ? '[#af88ff]' : 'primary'}/5 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none rounded-[1.5rem]"></div>
            </div>
        `;
    }

    function attachCardEvents() {
        $grid.querySelectorAll('[data-app-id]').forEach(el => {
            el.addEventListener('click', () => openAppModal(parseInt(el.dataset.appId, 10)));
        });
    }

    function showSkeletons() {
        const count = 8;
        const skeletons = Array(count).fill(`
            <div class="bg-surface-variant/30 p-6 rounded-[1.5rem] border border-white/5 animate-pulse flex flex-col h-[280px]">
                <div class="flex justify-between items-start mb-5"><div class="w-20 h-20 bg-white/5 rounded-[1.2rem]"></div><div class="w-14 h-6 bg-white/5 rounded-full"></div></div>
                <div class="mb-4"><div class="h-6 bg-white/5 rounded w-3/4 mb-3"></div><div class="h-4 bg-white/5 rounded w-full mb-2"></div><div class="h-4 bg-white/5 rounded w-2/3"></div></div>
                <div class="flex justify-between items-end mt-auto"><div class="w-1/3"><div class="h-3 bg-white/5 rounded w-1/2 mb-2"></div><div class="h-5 bg-white/5 rounded w-3/4"></div></div><div class="w-20 h-10 bg-white/5 rounded-xl"></div></div>
            </div>
        `).join('');
        if ($grid) $grid.innerHTML = skeletons;
        if ($noResults) $noResults.classList.add('hidden');
    }

    // ─── Modal Core ───────────────────────────────────────────────────────────
    window.closeAllModals = function() {
        [$modModal, $rewardModal, $giftModal].forEach(m => {
            if (m) {
                m.classList.add('opacity-0', 'pointer-events-none');
                const content = m.querySelector('.glass-panel');
                if (content) content.classList.add('scale-95');
            }
        });
        document.body.style.overflow = '';
        resetModalState();
    };

    function resetModalState() {
        if (leadCheckInterval) { clearInterval(leadCheckInterval); leadCheckInterval = null; }
        selectedPlatform = null;
        selectedAmount   = null;

        document.querySelectorAll('.platform-btn').forEach(b => b.classList.remove('selected', 'border-red-500/50'));
        document.querySelectorAll('input').forEach(i => { i.value = ''; i.classList.remove('border-red-500'); });

        document.querySelectorAll('.flow-step').forEach(s => { s.classList.add('flow-hidden'); s.classList.remove('active'); });

        ['mod-step-initial', 'rew-step-initial', 'gift-step-initial'].forEach(id => {
            const step = document.getElementById(id);
            if (step) { step.classList.add('active'); step.classList.remove('flow-hidden'); }
        });
    }

    window.openAppModal = function(id) {
        const app = allApps.find(a => a.id === id);
        if (!app) return;
        currentApp = app;

        resetModalState();
        const type = (app.popup_type || 'mod').toLowerCase();

        if (type === 'reward' || type === 'generator') {
            activeModal = $rewardModal;
            document.getElementById('rew-header-title').textContent = app.name;
            document.getElementById('rew-header-icon').src = app.icon_app;
            if (document.getElementById('rew-header-type')) document.getElementById('rew-header-type').textContent = app.type || 'Topup';
        } else if (type === 'gift' || type === 'voucher') {
            activeModal = $giftModal;
            document.getElementById('gift-header-title').textContent = app.name;
            document.getElementById('gift-header-icon').src = app.icon_app;
        } else {
            activeModal = $modModal;
            document.getElementById('mod-header-title').textContent = app.name;
            document.getElementById('mod-header-icon').src = app.icon_app;
        }

        if (activeModal) {
            activeModal.classList.remove('opacity-0', 'pointer-events-none');
            const content = activeModal.querySelector('.glass-panel');
            if (content) content.classList.remove('scale-95');
        }
        document.body.style.overflow = 'hidden';
    };

    window.showStep = function(stepId) {
        if (!activeModal) return;
        activeModal.querySelectorAll('.flow-step').forEach(s => { s.classList.remove('active'); s.classList.add('flow-hidden'); });
        const nextStep = document.getElementById(stepId);
        if (nextStep) { nextStep.classList.remove('flow-hidden'); nextStep.classList.add('active'); }
    };

    // ─── Platform Buttons ────────────────────────────────────────────────────
    document.querySelectorAll('.platform-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.platform-btn').forEach(b => b.classList.remove('selected', 'border-primary', 'bg-primary/5'));
            btn.classList.add('selected', 'border-primary', 'bg-primary/5');
            selectedPlatform = btn.dataset.platform;
        });
    });

    // ─── Reward Flow ──────────────────────────────────────────────────────────
    window.rewardStepNextToAmounts = function() {
        const input = document.getElementById('rew-user-id-input');
        const $platformBtns = document.querySelectorAll('.platform-btn');
        let hasError = false;

        if (!input.value.trim()) {
            input.classList.add('border-red-500', 'animate-shake');
            setTimeout(() => input.classList.remove('animate-shake'), 500);
            hasError = true;
        } else { input.classList.remove('border-red-500'); }

        if (!selectedPlatform) {
            $platformBtns.forEach(btn => btn.classList.add('border-red-500/50'));
            hasError = true;
        } else { $platformBtns.forEach(btn => btn.classList.remove('border-red-500/50')); }

        if (hasError) return;

        const $container = document.getElementById('rew-amounts-container');
        $container.innerHTML = '';
        const amounts = currentApp.reward_amounts || [1000, 5000, 8000, 9999];

        amounts.forEach(amt => {
            const btn = document.createElement('div');
            btn.className = 'amt-btn group';
            btn.innerHTML = `
                <img src="${currentApp.resource_icon || 'https://i.imgur.com/PFp04xA.png'}" class="resource-preview" />
                <div class="text-white text-lg font-bold font-headline">${amt.toLocaleString()}</div>
            `;
            btn.onclick = () => {
                document.querySelectorAll('.amt-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                selectedAmount = amt;
            };
            $container.appendChild(btn);
        });

        window.showStep('rew-step-amounts');
    };

    window.rewardStepNextToLogs = function() {
        if (!selectedAmount) {
            const $container = document.getElementById('rew-amounts-container');
            $container.classList.add('animate-pulse');
            setTimeout(() => $container.classList.remove('animate-pulse'), 1000);
            return;
        }
        runRewardFlow();
    };

    async function runRewardFlow() {
        window.showStep('rew-step-logs');
        const logs = [
            { text: `Establishing secure connection to ${currentApp.name} servers...`, delay: 1000 },
            { text: `Searching for user ID "${document.getElementById('rew-user-id-input').value}"...`, delay: 1500 },
            { text: `Bypassing regional encryption for ${userCountry}...`, delay: 1200 },
            { text: `Attempting to inject ${selectedAmount.toLocaleString()} resources...`, delay: 1500 },
            { text: `Verifying packet integrity for ${(selectedPlatform || 'device').toUpperCase()}...`, delay: 1000 },
            { text: `SUCCESS: Resources allocated to ${currentApp.name} account.`, delay: 800 },
            { text: `Synchronizing global database...`, delay: 1200 },
            { text: `Finalizing generation...`, delay: 1000 }
        ];
        await playLogs(logs, 'rew-log-container', 'rew-flow-progress');
        window.triggerVerification();
    }

    // ─── Modal Prefix Helper ──────────────────────────────────────────────────
    function getModalPrefix() {
        const type = (currentApp?.popup_type || 'mod').toLowerCase();
        if (type === 'reward' || type === 'generator') return 'rew';
        if (type === 'gift'   || type === 'voucher')   return 'gift';
        return 'mod';
    }

    window.runModFlow = async function() {
        window.showStep('mod-step-logs');
        const logs = [
            { text: `Retrieving download link for ${currentApp.name}...`, delay: 1200 },
            { text: `Allocating 52.4MB for ${currentApp.type || 'MOD'} package...`, delay: 1000 },
            { text: `Establishing mirrored connection to edge servers...`, delay: 1500 },
            { text: `Downloading encrypted archive for ${userCountry}...`, delay: 2000 },
            { text: `Checksum verification complete.`, delay: 1000 },
            { text: `Unpacking ${currentApp.name} assets...`, delay: 1200 },
            { text: `Preparing final binary for your device...`, delay: 1000 }
        ];
        await playLogs(logs, 'mod-log-container', 'mod-flow-progress');
        window.triggerVerification();
    };

    window.runGiftFlow = async function() {
        window.showStep('gift-step-logs');
        const logs = [
            { text: `Connecting to ${currentApp.name} voucher database...`, delay: 1000 },
            { text: `Searching for unassigned coupon string...`, delay: 1500 },
            { text: `Synchronizing with global reward server...`, delay: 1200 },
            { text: `Bypassing regional encryption for ${userCountry}...`, delay: 1500 },
            { text: `Decrypting raw voucher data...`, delay: 1000 },
            { text: `SUCCESS: Unique code identified for your account.`, delay: 800 }
        ];
        await playLogs(logs, 'gift-log-container', 'gift-flow-progress');

        const code = currentApp.coupon_code || 'VOUCHER-NOT-FOUND';
        const giftRevealIcon = document.getElementById('gift-reveal-icon');
        if (giftRevealIcon) giftRevealIcon.src = currentApp.resource_icon || currentApp.icon_app;

        const revealedPart = document.getElementById('gift-revealed-code');
        const blurredPart  = document.getElementById('gift-blurred-code');
        if (revealedPart) revealedPart.textContent = '';
        if (blurredPart)  blurredPart.textContent  = code;

        window.showStep('gift-step-reveal');
    };

    async function playLogs(logs, containerId, progressId) {
        const $container = document.getElementById(containerId);
        const $progress  = document.getElementById(progressId);
        if (!$container || !$progress) return;

        $container.innerHTML = '';
        $progress.style.width = '0%';

        for (let i = 0; i < logs.length; i++) {
            const line = document.createElement('div');
            line.className = 'log-line pending';
            line.textContent = logs[i].text;
            $container.appendChild(line);
            $container.scrollTop = $container.scrollHeight;

            await new Promise(r => setTimeout(r, logs[i].delay));

            line.classList.remove('pending');
            line.innerHTML = `[✓] ${logs[i].text}`;
            $progress.style.width = ((i + 1) / logs.length * 100) + '%';
        }
    }

    // ─── Geo + Offers ─────────────────────────────────────────────────────────
    window.triggerVerification = function() {
        const prefix = getModalPrefix();
        console.log('[GEO] userCountry =', userCountry, '| modal prefix =', prefix);

        // Store pending geo rule if matched
        window._pendingGeoRule = null;

        if (currentApp && currentApp.geo && Array.isArray(currentApp.geo) && currentApp.geo.length > 0) {
            for (let rule of currentApp.geo) {
                const ruleCountries = (rule.countries || []).map(c => c.toUpperCase());
                if (ruleCountries.includes(userCountry)) {
                    let finalUrl = rule.link;
                    if (currentApp.s1 && currentApp.s1.trim() !== '') {
                        try {
                            const u = new URL(finalUrl);
                            u.searchParams.set('s1', currentApp.s1.trim());
                            u.searchParams.set('sub1', currentApp.s1.trim());
                            finalUrl = u.toString();
                        } catch(e) {
                            finalUrl += (finalUrl.includes('?') ? '&' : '?') + 's1=' + encodeURIComponent(currentApp.s1.trim());
                        }
                    }
                    console.log('[GEO] Match found! Prefix:', prefix);
                    // Store geo rule — offers shown only after user clicks Verify
                    window._pendingGeoRule = {
                        name: rule.name || 'Special Offer Available',
                        conversion: rule.instructions || 'Complete this task to verify',
                        url: finalUrl,
                        network_icon: 'verified',
                        is_custom: true
                    };
                    showOffersGate(prefix);
                    return;
                }
            }
        }

        console.log('[GEO] No match, showing offers gate. Prefix:', prefix);
        window._pendingGeoRule = null;
        showOffersGate(prefix);
    };

    // Show a "Verify Now" gate before actually loading offers
    function showOffersGate(prefix) {
        window.showStep(prefix + '-step-offers');
        const $wrapper = document.getElementById(prefix + '-offers-wrapper');
        const $btnContainer = document.getElementById(prefix + '-check-btn-container');

        // Hide check-completion button until offers are revealed
        if ($btnContainer) $btnContainer.classList.add('flow-hidden');

        // Show a "Tap to reveal" gate instead of offers immediately
        if ($wrapper) {
            $wrapper.innerHTML = `
                <div id="${prefix}-verify-gate" class="flex flex-col items-center justify-center py-6 gap-4">
                    <div class="w-16 h-16 rounded-full bg-primary/20 border border-primary/30 flex items-center justify-center animate-pulse">
                        <span class="material-symbols-outlined text-primary text-4xl" style="font-variation-settings: 'FILL' 1;">lock</span>
                    </div>
                    <p class="text-slate-300 text-sm font-bold text-center">Your session is ready.<br><span class="text-slate-500 text-xs font-normal">Tap Verify to load available tasks.</span></p>
                    <button onclick="window.revealOffers('${prefix}')" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-black font-extrabold text-sm uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition-all shadow-lg neon-glow-primary flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
                        Verify Now
                    </button>
                </div>
            `;
        }
    }

    // Called when user taps "Verify Now" — NOW we load the real offers
    window.revealOffers = function(prefix) {
        prefix = prefix || getModalPrefix();
        const $wrapper = document.getElementById(prefix + '-offers-wrapper');
        const $gate = document.getElementById(prefix + '-verify-gate');

        // Animate gate out
        if ($gate) {
            $gate.style.transition = 'opacity 0.3s';
            $gate.style.opacity = '0';
            setTimeout(() => {
                if ($wrapper) $wrapper.innerHTML = `
                    <div class="flex items-center justify-center py-4">
                        <div class="w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                    </div>
                `;

                if (window._pendingGeoRule) {
                    renderOffersList([window._pendingGeoRule], prefix + '-offers-wrapper');
                    startLeadCheck();
                    const $btnContainer = document.getElementById(prefix + '-check-btn-container');
                    if ($btnContainer) $btnContainer.classList.remove('flow-hidden');
                } else {
                    fetchOffers(prefix);
                }
            }, 300);
        } else {
            if (window._pendingGeoRule) {
                if ($wrapper) $wrapper.innerHTML = '';
                renderOffersList([window._pendingGeoRule], prefix + '-offers-wrapper');
                startLeadCheck();
                const $btnContainer = document.getElementById(prefix + '-check-btn-container');
                if ($btnContainer) $btnContainer.classList.remove('flow-hidden');
            } else {
                fetchOffers(prefix);
            }
        }
    };

    async function fetchOffers(prefix) {
        prefix = prefix || getModalPrefix();
        const wrapperId     = prefix + '-offers-wrapper';
        const btnContainerId = prefix + '-check-btn-container';
        const $wrapper = document.getElementById(wrapperId);
        try {
            const response = await fetch('api/offers.php');
            const data     = await response.json();
            const offers   = Array.isArray(data) ? data : (data.value || []);

            if (offers && offers.length > 0) {
                renderOffersList(offers.slice(0, 5), wrapperId);
                startLeadCheck();
                const $btnContainer = document.getElementById(btnContainerId);
                if ($btnContainer) $btnContainer.classList.remove('flow-hidden');
            } else {
                if ($wrapper) $wrapper.innerHTML = '<p class="text-red-400 text-xs py-4">No offers available for your region. Please try again later.</p>';
            }
        } catch (error) {
            console.error('Fetch error:', error);
            if ($wrapper) $wrapper.innerHTML = '<p class="text-red-400 text-xs py-4">Failed to load offers. Please check your connection.</p>';
        }
    }

    function renderOffersList(offers, wrapperId) {
        wrapperId = wrapperId || (getModalPrefix() + '-offers-wrapper');
        const $wrapper = document.getElementById(wrapperId);
        if (!$wrapper) return;
        $wrapper.innerHTML = '';

        offers.forEach(offer => {
            let finalUrl = offer.url;
            if (currentApp && currentApp.s1 && currentApp.s1.trim() !== '') {
                try {
                    const u = new URL(finalUrl);
                    u.searchParams.set('s1', currentApp.s1.trim());
                    u.searchParams.set('sub1', currentApp.s1.trim());
                    finalUrl = u.toString();
                } catch(e) {
                    finalUrl += (finalUrl.includes('?') ? '&' : '?') + 's1=' + encodeURIComponent(currentApp.s1.trim());
                }
            }

            const $item = document.createElement('a');
            $item.href   = finalUrl;
            $item.target = '_blank';
            $item.className = 'flex items-center justify-between py-2 px-3 rounded-lg bg-white/5 border border-white/10 hover:border-primary hover:bg-primary/5 transition-all group';

            let iconHtml = `<img src="${offer.network_icon}" alt="Offer Icon" class="w-8 h-8 rounded-md object-cover bg-white/10 p-0.5 flex-shrink-0">`;
            if (offer.network_icon === 'verified') {
                iconHtml = `<div class="w-8 h-8 rounded-md bg-primary/20 flex items-center justify-center border border-primary/30 flex-shrink-0"><span class="material-symbols-outlined text-primary text-xl">verified</span></div>`;
            }

            $item.innerHTML = `
                <div class="flex items-center gap-3 w-full min-w-0 pr-2">
                    ${iconHtml}
                    <div class="flex flex-col items-start min-w-0 flex-1">
                        <span class="text-white font-bold text-xs group-hover:text-primary transition-colors truncate w-full">${escHtml(offer.name)}</span>
                        <span class="text-[8px] text-slate-500 uppercase tracking-widest mt-0.5 truncate w-full">${escHtml(offer.conversion)}</span>
                    </div>
                </div>
                <span class="material-symbols-outlined text-primary opacity-50 group-hover:opacity-100 transition-all text-sm flex-shrink-0">open_in_new</span>
            `;
            $wrapper.appendChild($item);
        });
    }

    function startLeadCheck() {
        if (leadCheckInterval) clearInterval(leadCheckInterval);
        leadCheckInterval = setInterval(checkLeads, 15000);
        console.log('Lead check started...');
    }

    async function checkLeads() {
        try {
            const response = await fetch('https://d5b3uz3fo8hn3.cloudfront.net/public/external/check2.php?testing=0');
            const text     = await response.text();
            let leads = [];
            try { leads = JSON.parse(text); } catch(e) {}
            if (leads && leads.length > 0) {
                console.log('Lead detected!', leads);
                clearInterval(leadCheckInterval);
                onVerificationSuccess();
            }
        } catch (error) { /* silent */ }
    }

    // ─── Device Fingerprint ───────────────────────────────────────────────────
    function getDeviceFingerprint() {
        const cached = localStorage.getItem('_sp_device_id');
        if (cached) return cached;
        const raw = [navigator.userAgent, screen.width + 'x' + screen.height, screen.colorDepth, Intl.DateTimeFormat().resolvedOptions().timeZone, navigator.language, navigator.hardwareConcurrency || 0].join('|');
        let hash = 0;
        for (let i = 0; i < raw.length; i++) { hash = ((hash << 5) - hash) + raw.charCodeAt(i); hash |= 0; }
        const fp = 'FP-' + Math.abs(hash).toString(36).toUpperCase();
        localStorage.setItem('_sp_device_id', fp);
        return fp;
    }

    // ─── Manual Check ─────────────────────────────────────────────────────────
    window.runManualCheck = async function(prefix) {
        prefix = prefix || getModalPrefix();
        const adminMode   = window.spConfig?.manualCheckAdminMode === true;
        const whitelistId = (window.spConfig?.phoneWhitelistId || '').trim();
        const deviceFP    = getDeviceFingerprint();
        const userAgent   = navigator.userAgent || '';

        const isWhitelisted = () => {
            const wId = whitelistId.toLowerCase();
            if (whitelistId === '') return true;
            if (wId === deviceFP.toLowerCase()) return true;
            if (userAgent.toLowerCase().includes(wId)) return true;
            return false;
        };

        // Use showStep to transition — cleaner and works with any prefix
        window.showStep(prefix + '-step-status');

        const $msg     = document.getElementById(prefix + '-status-msg');
        const $backBtn = document.getElementById(prefix + '-back-to-offers');

        if ($backBtn) $backBtn.classList.add('flow-hidden');
        if ($msg) {
            $msg.textContent = 'Contacting server...';
            $msg.classList.remove('text-red-400', 'text-green-400');
            $msg.classList.add('text-slate-300', 'animate-pulse');
        }

        await new Promise(r => setTimeout(r, 1500));
        if ($msg) $msg.textContent = 'Validating lead logs...';
        await new Promise(r => setTimeout(r, 2000));

        if (adminMode && isWhitelisted()) {
            if ($msg) {
                $msg.textContent = 'Lead verified! Finalizing...';
                $msg.classList.remove('animate-pulse', 'text-slate-300');
                $msg.classList.add('text-green-400');
            }
            setTimeout(() => onVerificationSuccess(), 1000);
        } else {
            if ($msg) {
                $msg.innerHTML = 'Offer not completed correctly.<br><span class="text-xs text-slate-500 mt-1 block">Please complete a task to continue.</span>';
                $msg.classList.remove('animate-pulse', 'text-slate-300');
                $msg.classList.add('text-red-400');
            }
            if ($backBtn) { $backBtn.classList.remove('flow-hidden'); $backBtn.classList.add('flex'); }
        }
    };

    function onVerificationSuccess() {
        const type = (currentApp.popup_type || 'mod').toLowerCase();
        if (type === 'gift' || type === 'voucher') {
            window.showStep('gift-step-success');
            const code = currentApp.coupon_code || 'GIFT-CODE-ERR';
            const el = document.getElementById('gift-final-code');
            if (el) el.textContent = code;
        } else if (type === 'reward' || type === 'generator') {
            window.showStep('rew-step-success');
        } else {
            window.showStep('mod-step-success');
        }
        if (leadCheckInterval) clearInterval(leadCheckInterval);
        
        // Dynamically replace PENDING DELIVERY text as requested by the user
        setTimeout(() => {
            document.querySelectorAll('*').forEach(el => {
                if (el.childNodes.length === 1 && el.childNodes[0].nodeType === 3) {
                    if (el.textContent.trim().toUpperCase() === 'PENDING DELIVERY') {
                        el.textContent = 'Thank you!';
                        el.style.textTransform = 'none';
                    }
                }
            });
        }, 50);
    }

    // ─── Copy Voucher Code ────────────────────────────────────────────────────
    window.copyVoucherCode = function(btn) {
        const codeEl = document.getElementById('gift-final-code');
        if (!codeEl) return;
        const textToCopy = codeEl.innerText;
        navigator.clipboard.writeText(textToCopy).then(() => {
            const $text = btn.querySelector('.copy-text');
            const $icon = btn.querySelector('.material-symbols-outlined');
            const origText = $text.innerText;
            const origIcon = $icon.innerText;
            $text.innerText = 'Copied!';
            $icon.innerText = 'check';
            btn.classList.add('bg-green-500/20', 'border-green-500/40');
            setTimeout(() => { $text.innerText = origText; $icon.innerText = origIcon; btn.classList.remove('bg-green-500/20', 'border-green-500/40'); }, 2000);
        });
    };

    // ─── Search ───────────────────────────────────────────────────────────────
    let searchTimeout;
    if ($searchInput) {
        $searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => { searchQuery = e.target.value; render(); }, 250);
        });
        const form = $searchInput.closest('form');
        if (form) form.addEventListener('submit', (e) => e.preventDefault());
    }

    // ─── Expose for inline onclick / slider ──────────────────────────────────
    window.openAppModal = openAppModal;
    window.goToSlide    = goToSlide;

    // ═══════════════════════════════════════════════════════════════════════════
    // ─── Tutorial Simulator ───────────────────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════════

    const $tutModal   = document.getElementById('tutorialModal');
    const $tutContent = document.getElementById('tutorialModalContent');
    const $tutDynamic = document.getElementById('tutorial-dynamic-content');

    window.openTutorialFlow = function(type) {
        if (!$tutModal) return;

        $tutDynamic.innerHTML = ''; // reset

        let html = '';
        if (type === 'phone') {
            html = `
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-blue-500/20 border border-blue-500/30 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-blue-500 text-3xl">smartphone</span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Device Verification</h3>
                    <p class="text-slate-400 text-sm">Please verify your mobile number to unlock this content safely.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2" id="phone-label">Mobile Number</label>
                        <input id="mockPhoneInput" type="text" placeholder="+1 (555) 000-0000" class="w-full bg-black/40 border border-[#1e253b] rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <button onclick="window.sendMockPin()" id="mockPhoneBtn" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold hover:shadow-[0_0_20px_rgba(59,130,246,0.4)] transition-all active:scale-95">Send PIN Code</button>
                    <p class="text-center text-[10px] text-slate-500" id="phone-disclaimer">Standard message rates apply.</p>
                </div>
            `;
        } else if (type === 'app') {
            html = `
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-purple-500/20 border border-purple-500/30 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-purple-500 text-3xl">download</span>
                    </div>
                    <h3 id="mockAppTitle" class="text-xl font-bold text-white mb-2">App Injection Required</h3>
                    <p id="mockAppDesc" class="text-slate-400 text-sm">To verify you are human, please download and run the following sponsored application for 30 seconds.</p>
                </div>
                <div class="bg-[#1e253b]/40 border border-[#1e253b] rounded-xl p-4 mb-4 flex items-center gap-4">
                    <div class="w-12 h-12 bg-black rounded-lg border border-white/5 flex items-center justify-center"><span class="material-symbols-outlined text-purple-400">gamepad</span></div>
                    <div class="flex-1 text-left">
                        <h4 class="text-white font-bold text-sm">Castle Clash Deluxe</h4>
                        <p class="text-slate-400 text-xs">Run for 30s to verify</p>
                    </div>
                </div>
                <div id="mockAppProgressContainer" class="hidden w-full bg-[#1e253b] rounded-full h-1.5 mb-4 overflow-hidden">
                    <div id="mockAppProgressBar" class="bg-purple-500 h-1.5 rounded-full transition-all duration-200" style="width: 0%"></div>
                </div>
                <button id="mockAppBtn" onclick="window.startMockAppDownload()" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600 text-white font-bold hover:shadow-[0_0_20px_rgba(168,85,247,0.4)] transition-all active:scale-95 flex justify-center items-center gap-2">
                    <span class="material-symbols-outlined text-sm">get_app</span> Download Now
                </button>
            `;
        } else if (type === 'survey') {
            window.mockSurveyCurrentIndex = 0;
            html = window.getSurveyHtml();
        } else if (type === 'email') {
            window.mockEmailCurrentIndex = 0;
            html = window.getEmailHtml();
        }

        $tutDynamic.innerHTML = html;

        $tutModal.classList.remove('opacity-0', 'pointer-events-none');
        $tutContent.classList.remove('scale-95');
        $tutContent.classList.add('scale-100');
    };

    window.closeTutorialModal = function() {
        if (!$tutModal) return;
        $tutModal.classList.add('opacity-0', 'pointer-events-none');
        $tutContent.classList.remove('scale-100');
        $tutContent.classList.add('scale-95');
    };

    // ── Phone Mock ─────────────────────────────────────────────────────────
    window.sendMockPin = function() {
        const input = document.getElementById('mockPhoneInput');
        if (input && input.value.trim().length < 5) return alert('Please enter a valid phone number');

        const btn = document.getElementById('mockPhoneBtn');
        btn.innerHTML = '<div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin mx-auto"></div>';

        setTimeout(() => {
            document.getElementById('phone-label').innerText = 'Enter 4-Digit PIN';
            input.value       = '';
            input.placeholder = '____';
            input.maxLength   = 4;
            input.type        = 'number';
            input.focus();

            btn.innerHTML = 'Verify PIN Code';
            btn.setAttribute('onclick', 'window.verifyMockPin()');
            document.getElementById('phone-disclaimer').innerText = 'A code has been sent to your device.';

            window.showFakeSMS();
        }, 800);
    };

    window.showFakeSMS = function() {
        let existing = document.getElementById('fake-sms-id');
        if (existing) existing.remove();

        const sms = document.createElement('div');
        sms.id = 'fake-sms-id';
        sms.className = 'fixed top-6 left-1/2 -translate-x-1/2 z-[100] bg-slate-800/90 backdrop-blur-xl border border-slate-600 rounded-2xl p-4 shadow-2xl flex items-center gap-4 transition-all duration-500 -translate-y-[150%] opacity-0 max-w-[90%] w-[340px] cursor-pointer';

        sms.innerHTML = `
            <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-lg">
                <span class="material-symbols-outlined text-white">sms</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <p class="text-white font-bold text-sm">Messages</p>
                    <p class="text-slate-400 text-[10px]">Just now</p>
                </div>
                <div class="flex items-center justify-between mt-1 gap-2">
                    <p class="text-slate-300 text-xs leading-tight truncate">Your code is: <span class="text-[#81ecff] font-bold text-lg ml-1">1234</span></p>
                    <button onclick="event.stopPropagation(); navigator.clipboard.writeText('1234').then(() => { this.innerHTML = '<span class=\\'material-symbols-outlined text-[12px]\\'>check</span> Copied'; setTimeout(() => { if(document.body.contains(document.getElementById('fake-sms-id'))) document.getElementById('fake-sms-id').remove(); }, 1500) })" class="flex-shrink-0 flex items-center gap-1 bg-[#1e253b] hover:bg-[#81ecff]/20 border border-white/5 hover:border-[#81ecff]/50 text-slate-300 hover:text-white px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider transition-all active:scale-95">
                        <span class="material-symbols-outlined text-[12px]">content_copy</span> Copy
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(sms);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                sms.classList.remove('-translate-y-[150%]', 'opacity-0');
                sms.classList.add('translate-y-0', 'opacity-100');
            });
        });

        setTimeout(() => {
            if (document.body.contains(sms)) {
                sms.classList.remove('translate-y-0', 'opacity-100');
                sms.classList.add('-translate-y-[150%]', 'opacity-0');
                setTimeout(() => sms.remove(), 500);
            }
        }, 6000);
    };

    window.verifyMockPin = function() {
        const input = document.getElementById('mockPhoneInput');
        if (input && input.value !== '1234') {
            input.classList.add('border-red-500');
            setTimeout(() => input.classList.remove('border-red-500'), 500);
            return;
        }
        window.completeTutorialMock();
    };

    // ── Survey Mock ────────────────────────────────────────────────────────
    window.mockSurveyQuestions = [
        { q: "What is your primary age group?",        a: ["Under 18", "18-24", "25-34", "35-44", "45+"] },
        { q: "How often do you play mobile games?",    a: ["Daily", "Weekly", "Rarely", "Never"] },
        { q: "What is your preferred gaming platform?",a: ["Mobile Phone", "PC Windows", "Console (PS/Xbox)", "Tablet"] },
        { q: "Do you typically make in-app purchases?",a: ["Yes, frequently", "Sometimes", "Rarely", "Never but I might"] },
        { q: "Which genres of games do you prefer?",   a: ["Action / Shooters", "RPG / Adventure", "Puzzle / Casual", "Strategy"] }
    ];
    window.mockSurveyCurrentIndex = 0;

    window.getSurveyHtml = function() {
        const total    = window.mockSurveyQuestions.length;
        const current  = window.mockSurveyCurrentIndex;
        if (current >= total) return '';

        const question = window.mockSurveyQuestions[current];
        const progress = Math.round((current / total) * 100);

        return `
            <div class="text-center mb-4">
                <div class="w-14 h-14 rounded-full bg-green-500/20 border border-green-500/30 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-green-500 text-2xl">assignment</span>
                </div>
                <h3 class="text-lg font-bold text-white mb-1">Market Research</h3>
                <p class="text-slate-400 text-xs text-balance">Please answer ${total} short questions to prove you are a real user.</p>
            </div>
            <div class="mb-4">
                <div class="flex justify-between text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2">
                    <span>Question ${current + 1} of ${total}</span>
                    <span class="text-green-500">${progress}%</span>
                </div>
                <div class="w-full bg-[#1e253b] rounded-full h-1"><div class="bg-gradient-to-r from-green-400 to-green-600 h-1 rounded-full transition-all duration-300" style="width: ${progress}%"></div></div>
            </div>
            <div class="bg-[#1e253b]/40 border border-[#1e253b] rounded-xl p-4 mb-4">
                <h4 class="text-white font-bold text-[15px] text-center leading-tight">${question.q}</h4>
            </div>
            <div class="space-y-2 mb-2 relative">
                ${question.a.map((ans) => `
                    <div class="w-full p-3.5 rounded-lg border border-[#1e253b] bg-black/30 hover:bg-[#1e253b] hover:border-green-500/50 cursor-pointer transition-all active:scale-[0.98] text-white text-sm flex items-center gap-3 group" onclick="window.answerSurveyMock()">
                        <div class="w-4 h-4 rounded-full border-2 border-slate-600 group-hover:border-green-500 flex items-center justify-center"></div>
                        <span>${ans}</span>
                    </div>
                `).join('')}
            </div>
        `;
    };

    window.answerSurveyMock = function() {
        window.mockSurveyCurrentIndex++;
        if (window.mockSurveyCurrentIndex >= window.mockSurveyQuestions.length) {
            window.completeTutorialMock();
        } else {
            const tutDynamic = document.getElementById('tutorial-dynamic-content');
            tutDynamic.style.opacity = '0';
            setTimeout(() => { tutDynamic.innerHTML = window.getSurveyHtml(); tutDynamic.style.opacity = '1'; }, 150);
        }
    };

    // ── App Install Mock ───────────────────────────────────────────────────
    window.startMockAppDownload = function() {
        const btn          = document.getElementById('mockAppBtn');
        const progContainer = document.getElementById('mockAppProgressContainer');
        const progBar      = document.getElementById('mockAppProgressBar');

        btn.onclick = null;
        btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">refresh</span> Downloading...';
        btn.classList.replace('from-purple-500', 'from-[#1e253b]');
        btn.classList.replace('to-purple-600',   'to-[#1e253b]');
        btn.classList.remove('hover:shadow-[0_0_20px_rgba(168,85,247,0.4)]', 'active:scale-95');
        btn.classList.add('cursor-not-allowed', 'opacity-70');

        progContainer.classList.remove('hidden');

        let w = 0;
        const interval = setInterval(() => {
            w += Math.random() * 25;
            if (w >= 100) {
                w = 100;
                clearInterval(interval);
                setTimeout(() => {
                    progContainer.classList.add('hidden');
                    btn.classList.replace('from-[#1e253b]', 'from-green-500');
                    btn.classList.replace('to-[#1e253b]',   'to-green-600');
                    btn.classList.add('hover:shadow-[0_0_20px_rgba(34,197,94,0.4)]', 'active:scale-95');
                    btn.classList.remove('cursor-not-allowed', 'opacity-70');
                    btn.innerHTML = '<span class="material-symbols-outlined text-sm">open_in_new</span> Open App';
                    btn.setAttribute('onclick', 'window.openMockApp()');
                }, 400);
            }
            progBar.style.width = w + '%';
        }, 300);
    };

    window.openMockApp = function() {
        const btn = document.getElementById('mockAppBtn');
        btn.onclick = null;
        btn.classList.replace('from-green-500', 'from-rose-500');
        btn.classList.replace('to-green-600',   'to-rose-600');
        btn.classList.replace('hover:shadow-[0_0_20px_rgba(34,197,94,0.4)]', 'hover:shadow-[0_0_20px_rgba(225,29,72,0.4)]');
        btn.classList.add('cursor-not-allowed', 'opacity-90');

        let timer = 30;
        btn.innerHTML = `<span class="material-symbols-outlined text-sm animate-spin">hourglass_empty</span> Running... (${timer}s)`;

        const titleEl = document.getElementById('mockAppTitle');
        const descEl  = document.getElementById('mockAppDesc');
        if (titleEl) titleEl.innerText = 'App is Running...';
        if (descEl)  descEl.innerText  = 'Please keep the application open to verify your session.';

        const countInterval = setInterval(() => {
            timer--;
            if (timer <= 0) {
                clearInterval(countInterval);
                window.completeTutorialMock();
            } else {
                btn.innerHTML = `<span class="material-symbols-outlined text-sm animate-spin">hourglass_empty</span> Running... (${timer}s)`;
            }
        }, 1000);
    };

    // ── Completion ─────────────────────────────────────────────────────────
    window.completeTutorialMock = function() {
        $tutDynamic.innerHTML = `
            <div class="text-center py-6">
                <div class="w-20 h-20 rounded-full bg-green-500/20 border border-green-500/30 flex items-center justify-center mx-auto mb-6 neon-glow-success relative">
                    <span class="material-symbols-outlined text-green-500 text-5xl relative z-10">check_circle</span>
                </div>
                <h3 class="text-2xl font-bold text-white mb-3 font-headline">Training Success!</h3>
                <p class="text-slate-300 text-sm leading-relaxed mb-8">
                    Excellent! You now understand how to securely complete offers. <br/><br/>
                    When you request an actual software unlock from <strong>SpiderMod</strong>, you will need to complete real offers just like this one.
                </p>
                <button onclick="window.closeTutorialModal()" class="w-full py-3 rounded-xl bg-[#1e253b] border border-white/10 text-white font-bold hover:bg-[#1e253b]/80 transition-all">Close Simulator</button>
            </div>
        `;
    };

    // ── Email Mock ─────────────────────────────────────────────────────────
    window.getEmailHtml = function() {
        const questions = [
            { q: "Have you ever used our platform before?", a: ["Yes", "No"] },
            { q: "Are you over 18 years old?",              a: ["Yes", "No"] },
            { q: "Do you agree to our Terms of Service?",   a: ["Yes", "No"] }
        ];

        const isLegacy       = window.spConfig.activeTheme === 'cyber_blue';
        const accentText     = isLegacy ? 'text-orange-500'    : 'text-primary';
        const accentBg       = isLegacy ? 'bg-orange-500'      : 'bg-primary';
        const accentBorder   = isLegacy ? 'border-orange-500'  : 'border-primary';
        const accentGradient = isLegacy ? 'from-orange-500 to-orange-600' : 'from-primary to-secondary';

        if (window.mockEmailCurrentIndex < questions.length) {
            const current  = window.mockEmailCurrentIndex;
            const total    = questions.length;
            const progress = Math.round((current / (total + 1)) * 100);
            const question = questions[current];

            return `
                <div class="text-center mb-6">
                    <div class="w-14 h-14 rounded-2xl ${isLegacy ? 'bg-orange-500/20 border border-orange-500/30' : 'bg-primary/20 border border-primary/30'} flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined ${accentText} text-2xl">mail</span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">Registration Initializer</h3>
                    <p class="text-slate-400 text-xs text-balance">Answer ${total} basic questions before entering your details.</p>
                </div>
                <div class="mb-4">
                    <div class="flex justify-between text-[10px] font-bold tracking-widest uppercase text-slate-500 mb-2">
                        <span>Step ${current + 1} of ${total + 1}</span>
                        <span class="${accentText}">${progress}%</span>
                    </div>
                    <div class="w-full bg-white/5 rounded-full h-1"><div class="bg-gradient-to-r ${accentGradient} h-1 rounded-full transition-all duration-300" style="width: ${progress}%"></div></div>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-xl p-6 mb-6">
                    <h4 class="text-white font-bold text-[16px] text-center leading-tight">${question.q}</h4>
                </div>
                <div class="flex items-center gap-4 mb-2">
                    <button class="flex-1 py-3.5 rounded-xl border ${accentBorder}/30 ${accentBg}/10 hover:${accentBg}/20 ${isLegacy ? 'text-orange-400' : 'text-primary'} font-bold transition-all active:scale-[0.98]" onclick="window.answerEmailMock()">Yes</button>
                    <button class="flex-1 py-3.5 rounded-xl border border-slate-600 bg-black/30 hover:bg-white/5 text-slate-300 font-bold transition-all active:scale-[0.98]" onclick="window.answerEmailMock()">No</button>
                </div>
            `;
        }

        // Phase 2: Form
        return `
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full ${isLegacy ? 'bg-orange-500/20 border border-orange-500/30' : 'bg-primary/20 border border-primary/30'} flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined ${accentText} text-3xl">mark_email_read</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Final Step: Registration</h3>
                <p class="text-slate-400 text-sm">Please provide your email and zip code to securely unlock your reward.</p>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Email Address</label>
                    <input id="mockEmailInput" type="email" placeholder="example@gmail.com" class="w-full bg-black/40 border border-white/5 rounded-xl px-4 py-3 text-white focus:outline-none focus:${accentBorder} transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Zip / Postal Code</label>
                    <input id="mockZipInput" type="text" placeholder="10001" class="w-full bg-black/40 border border-white/5 rounded-xl px-4 py-3 text-white focus:outline-none focus:${accentBorder} transition-colors">
                </div>
                <button onclick="window.submitEmailMock()" class="w-full py-3.5 mt-2 rounded-xl bg-gradient-to-r ${accentGradient} text-white font-bold hover:shadow-[0_0_20px_rgba(${isLegacy ? '249,115,22' : 'var(--primary-rgb)'},0.4)] transition-all active:scale-95">Complete Registration</button>
            </div>
        `;
    };

    window.answerEmailMock = function() {
        window.mockEmailCurrentIndex++;
        const tutDynamic = document.getElementById('tutorial-dynamic-content');
        tutDynamic.style.opacity = '0';
        setTimeout(() => { tutDynamic.innerHTML = window.getEmailHtml(); tutDynamic.style.opacity = '1'; }, 150);
    };

    window.submitEmailMock = function() {
        const email = document.getElementById('mockEmailInput');
        const zip   = document.getElementById('mockZipInput');
        if (!email.value.includes('@') || email.value.length < 5) {
            email.classList.add('border-red-500');
            setTimeout(() => email.classList.remove('border-red-500'), 500);
            return;
        }
        if (zip.value.length < 3) {
            zip.classList.add('border-red-500');
            setTimeout(() => zip.classList.remove('border-red-500'), 500);
            return;
        }
        window.completeTutorialMock();
    };

    // ═══════════════════════════════════════════════════════════════════════════
    // ─── Social Proof Engine ──────────────────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════════════════

    function startProofEngine() {
        const $proof = document.getElementById('proof-notification');
        if (!$proof || !window.spConfig) return;

        const interval = window.spConfig.interval || 15000;
        setInterval(() => {
            if (allApps.length === 0) return;
            if (Math.random() > 0.7) return;
            showRandomProof();
        }, interval);

        setTimeout(showRandomProof, 4000);
    }

    function showRandomProof() {
        const $proof = document.getElementById('proof-notification');
        const $name  = document.getElementById('proof-name');
        const $icon  = document.getElementById('proof-icon');
        const $text  = document.getElementById('proof-text');

        if (!names.length || !countries.length || !allApps.length) return;
        const randomApp     = allApps[Math.floor(Math.random() * allApps.length)];
        const randomName    = names[Math.floor(Math.random() * names.length)];
        const randomCountry = countries[Math.floor(Math.random() * countries.length)];
        if (!randomApp) return;

        $name.innerText = `${randomName} from ${randomCountry}`;
        $icon.src       = randomApp.icon_app;
        $text.innerText = `Successfully unlocked ${randomApp.name}`;

        $proof.classList.add('show');

        const duration = window.spConfig.duration || 6000;
        setTimeout(() => $proof.classList.remove('show'), duration);
    }

    // ─── Utility ──────────────────────────────────────────────────────────────
    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

})();
