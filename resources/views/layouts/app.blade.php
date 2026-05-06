<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Brangkas Garage') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        html, body { background-color: #f1f5f9; }

        /* Sidebar lebar — dikontrol CSS, bukan Alpine */
        #main-sidebar { width: 18rem; }
        html.sidebar-mini #main-sidebar,
        body.sidebar-mini #main-sidebar { width: 6rem; }

        /* Padding konten */
        #main-wrapper { padding-left: 18rem; }
        #main-topbar  { left: 18rem; }
        html.sidebar-mini #main-wrapper,
        body.sidebar-mini #main-wrapper { padding-left: 6rem; }
        html.sidebar-mini #main-topbar,
        body.sidebar-mini #main-topbar  { left: 6rem; }

        /* Transisi HANYA aktif setelah layout siap (cegah animasi saat load) */
        body.layout-ready #main-sidebar { transition: width 0.3s; }
        body.layout-ready #main-wrapper { transition: padding-left 0.3s; }
        body.layout-ready #main-topbar  { transition: left 0.3s; }

        /* Mobile */
        @media (max-width: 1023px) {
            #main-wrapper { padding-left: 0 !important; }
            #main-topbar  { left: 0 !important; }
        }
    </style>

    <script>
        // Set class sidebar-mini SEBELUM render agar tidak ada layout shift
        (function() {
            if (localStorage.getItem('sidebarMini') === 'true') {
                document.documentElement.classList.add('sidebar-mini');
            }
        })();
    </script>

    <script>
    // SPA Navigation — intercept link, ganti hanya #main-content
    (function() {
        function shouldIntercept(href) {
            try {
                const u = new URL(href, location.href);
                if (u.hostname !== location.hostname) return false;
                if (u.pathname === location.pathname && u.search === location.search) return false;
                const skip = ['/logout', '/export', '/storage', '/password'];
                if (skip.some(s => u.pathname.includes(s))) return false;
                return true;
            } catch(e) { return false; }
        }

        function navigate(url, push) {
            const content = document.getElementById('main-content');
            if (!content) { location.href = url; return; }

            content.style.opacity = '0.5';

            fetch(url, { headers: { 'X-Requested-With': 'SPA' } })
                .then(r => r.redirected ? (location.href = r.url, null) : r.text())
                .then(html => {
                    if (!html) return;
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const nc  = doc.getElementById('main-content');
                    if (!nc) { location.href = url; return; }

                    content.innerHTML = nc.innerHTML;
                    if (push) history.pushState({}, '', url);
                    if (doc.title) document.title = doc.title;
                    content.style.opacity = '1';

                    // Re-run inline scripts
                    content.querySelectorAll('script:not([src])').forEach(old => {
                        const s = document.createElement('script');
                        s.textContent = old.textContent;
                        old.replaceWith(s);
                    });

                    // Re-init Alpine
                    if (window.Alpine) try { window.Alpine.initTree(content); } catch(e) {}

                    // Update sidebar active state
                    const path = new URL(url, location.href).pathname;
                    document.querySelectorAll('nav a[href]').forEach(a => {
                        const ap = new URL(a.href, location.href).pathname;
                        const active = ap === path || (ap !== '/' && path.startsWith(ap));
                        a.classList.toggle('border', active);
                        a.classList.toggle('border-white/50', active);
                    });

                    // Update judul topbar
                    const titleEl = document.getElementById('topbar-page-title');
                    if (titleEl) {
                        const titles = [
                            ['/dashboard',    'Overview'],
                            ['/spareparts',   'Data Sparepart'],
                            ['/inventory',    'Riwayat Inventory'],
                            ['/pos',          'Kasir POS'],
                            ['/transactions', 'Riwayat Transaksi'],
                            ['/profile',      'Profil'],
                        ];
                        const found = titles.find(([p]) => path === p || path.startsWith(p + '/') || path.startsWith(p + '?'));
                        titleEl.textContent = found ? found[1] : 'Brangkas Garage';
                    }

                    window.scrollTo(0, 0);
                })
                .catch(() => { location.href = url; });
        }

        document.addEventListener('click', e => {
            const a = e.target.closest('a[href]');
            if (!a || e.ctrlKey || e.metaKey || e.shiftKey || a.target) return;
            if (!shouldIntercept(a.href)) return;
            e.preventDefault();
            navigate(a.href, true);
        });

        document.addEventListener('submit', e => {
            const f = e.target;
            if (!f.method || f.method.toLowerCase() !== 'get') return;
            const u = new URL(f.action || location.href);
            new FormData(f).forEach((v, k) => u.searchParams.set(k, v));
            if (!shouldIntercept(u.href)) return;
            e.preventDefault();
            navigate(u.href, true);
        });

        window.addEventListener('popstate', () => navigate(location.href, false));
    })();
    </script>
</head>
<body class="bg-slate-100 text-slate-800">
    <div
        x-data="layoutState()"
        x-init="init()"
        @touchstart.passive="handleTouchStart($event)"
        @touchmove.passive="handleTouchMove($event)"
        @touchend="handleTouchEnd()"
        class="min-h-screen overflow-x-hidden"
    >
        @include('layouts.sidebar')

        <div id="main-wrapper" class="min-h-screen">

            {{-- Topbar fixed --}}
            <div id="main-topbar" class="fixed top-0 right-0 z-40 bg-slate-100">
                @include('layouts.topbar')
            </div>

            {{-- Spacer --}}
            <div class="h-[88px]"></div>

            <main id="main-content" class="px-4 sm:px-6 py-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function layoutState() {
            return {
                sidebarOpen: false,
                sidebarMini: false,
                isReady: false,
                touchStartX: 0, touchStartY: 0,
                touchEndX: 0,   touchEndY: 0,

                init() {
                    const saved = localStorage.getItem('sidebarMini');
                    if (saved !== null) this.sidebarMini = saved === 'true';
                    document.body.classList.toggle('sidebar-mini', this.sidebarMini);
                    document.documentElement.classList.toggle('sidebar-mini', this.sidebarMini);
                    // Aktifkan transisi setelah posisi awal sudah benar
                    this.$nextTick(() => {
                        this.isReady = true;
                        setTimeout(() => document.body.classList.add('layout-ready'), 50);
                    });
                },

                toggleSidebarMini() {
                    this.sidebarMini = !this.sidebarMini;
                    localStorage.setItem('sidebarMini', this.sidebarMini);
                    document.body.classList.toggle('sidebar-mini', this.sidebarMini);
                    document.documentElement.classList.toggle('sidebar-mini', this.sidebarMini);
                },

                openMobileSidebar()  { this.sidebarOpen = true; },
                closeMobileSidebar() { this.sidebarOpen = false; },

                handleTouchStart(e) {
                    if (!e.touches?.length) return;
                    this.touchStartX = this.touchEndX = e.touches[0].clientX;
                    this.touchStartY = this.touchEndY = e.touches[0].clientY;
                },
                handleTouchMove(e) {
                    if (!e.touches?.length) return;
                    this.touchEndX = e.touches[0].clientX;
                    this.touchEndY = e.touches[0].clientY;
                },
                handleTouchEnd() {
                    const dx = this.touchEndX - this.touchStartX;
                    const dy = this.touchEndY - this.touchStartY;
                    if (Math.abs(dy) > Math.abs(dx) || window.innerWidth >= 1024) return;
                    if (dx > 70 && this.touchStartX < 40 && !this.sidebarOpen) this.sidebarOpen = true;
                    if (dx < -70 && this.sidebarOpen) this.sidebarOpen = false;
                }
            }
        }

        function posPage() {
            return {
                openPayment: false,
                openReceipt: false,
                subtotal: 0,

                init() {
                    this.openPayment = document.getElementById('__pos_show_payment__')?.value === '1';
                    this.openReceipt = document.getElementById('__pos_show_receipt__')?.value === '1';
                    const s = document.getElementById('__pos_subtotal__');
                    if (s) this.subtotal = parseInt(s.value) || 0;
                    window.addEventListener('open-payment', () => { this.openPayment = true; });
                },

                async post(url, payload = {}) {
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (!token) return;
                        const panel = document.getElementById('cart-panel');
                        if (panel) panel.classList.add('opacity-60');
                        await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        await this.refreshCart();
                    } catch(e) {
                        console.error(e);
                    } finally {
                        const panel = document.getElementById('cart-panel');
                        if (panel) panel.classList.remove('opacity-60');
                    }
                },

                async refreshCart() {
                    const html = await fetch(window.location.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(r => r.text());
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newPanel = doc.querySelector('#cart-panel');
                    if (newPanel) document.querySelector('#cart-panel').innerHTML = newPanel.innerHTML;
                    const sub = document.querySelector('#cart-subtotal');
                    if (sub) {
                        const raw = sub.getAttribute('data-subtotal');
                        if (raw) this.subtotal = parseInt(raw, 10) || 0;
                    }
                },

                formatRupiah(n) {
                    return 'Rp ' + (Number(n) || 0).toLocaleString('id-ID');
                }
            }
        }
    </script>
</body>
</html>
