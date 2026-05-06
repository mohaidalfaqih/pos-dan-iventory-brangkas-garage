{{-- BACKDROP MOBILE --}}
<div
    x-show="sidebarOpen"
    x-transition.opacity
    class="fixed inset-0 bg-black/40 z-40 lg:hidden"
    @click="closeMobileSidebar()"
    style="display: none;"
></div>

{{-- SIDEBAR --}}
<aside
    id="main-sidebar"
    class="fixed top-0 left-0 z-50 h-screen text-white flex flex-col transform-gpu"
    style="background: linear-gradient(180deg, #0d2040 0%, #071428 100%);"
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    ]"
>
    {{-- BRAND --}}
    <div class="p-4 border-b border-white/10 flex items-center justify-between">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center font-bold shrink-0">
                BG
            </div>

            <div x-show="!sidebarMini" class="min-w-0">
                <div class="font-semibold truncate">Brangkas Garage</div>
                <div class="text-xs text-white/60 truncate">Inventori & POS</div>
            </div>
        </div>

        {{-- tombol close mobile --}}
        <button
            class="lg:hidden text-white/70 hover:text-white"
            @click="closeMobileSidebar()"
            type="button"
        >
            ✕
        </button>
    </div>

    {{-- NAV --}}
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">

        {{-- Dashboard: admin saja --}}
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10 transition font-semibold {{ request()->routeIs('dashboard') ? 'border border-white/50' : '' }}">
            <span class="shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </span>
            <span x-show="!sidebarMini">Dashboard</span>
        </a>
        @endif

        {{-- ADMIN ONLY --}}
        @if(auth()->user()->role === 'admin')

        <a href="{{ route('spareparts.index') }}"
           class="flex items-center justify-between px-4 py-3 rounded-xl hover:bg-white/10 transition font-semibold {{ request()->is('spareparts*') ? 'border border-white/50' : '' }}">
            <div class="flex items-center gap-3 min-w-0">
                <span class="shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 12v4m0 0l-2-2m2 2l2-2"/>
                    </svg>
                </span>
                <span x-show="!sidebarMini" class="truncate">Data Sparepart</span>
            </div>
            @if(($outOfStockCount ?? 0) > 0)
                <span x-show="!sidebarMini"
                    class="min-w-[22px] h-[22px] px-2 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center">
                    {{ $outOfStockCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('inventory.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10 transition font-semibold {{ request()->routeIs('inventory.*') ? 'border border-white/50' : '' }}">
            <span class="shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </span>
            <span x-show="!sidebarMini">Riwayat Inventory</span>
        </a>

        @endif

        {{-- POS: kasir saja --}}
        @if(auth()->user()->role === 'kasir')
        <a href="{{ route('pos.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10 transition font-semibold {{ request()->routeIs('pos.*') ? 'border border-white/50' : '' }}">
            <span class="shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </span>
            <span x-show="!sidebarMini">POS</span>
        </a>
        @endif

        <a href="{{ route('transactions.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/10 transition font-semibold {{ request()->routeIs('transactions.*') ? 'border border-white/50' : '' }}">
            <span class="shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </span>
            <span x-show="!sidebarMini">Riwayat Transaksi</span>
        </a>

    </nav>

    {{-- FOOTER --}}
    <div class="p-4 border-t border-white/10 space-y-2">

        {{-- tombol collapse desktop --}}
        <button
            type="button"
            class="hidden lg:flex w-full items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white/10 hover:bg-white/15 transition font-semibold"
            @click="toggleSidebarMini()"
        >
            <span x-text="sidebarMini ? '»' : '«'"></span>
            <span x-show="!sidebarMini">Kecilkan</span>
        </button>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full px-4 py-3 rounded-xl bg-white/10 hover:bg-white/15 flex items-center justify-center gap-2 transition font-semibold"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span x-show="!sidebarMini">Logout</span>
            </button>
        </form>
    </div>
</aside>