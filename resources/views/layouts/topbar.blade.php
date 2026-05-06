<div id="main-topbar" class="px-4 sm:px-6 pt-4 sm:pt-6">
    <div class="bg-white rounded-2xl shadow px-4 sm:px-5 py-4 flex items-center justify-between gap-4">

        {{-- LEFT --}}
        <div class="flex items-center gap-3 min-w-0">

            {{-- Tombol ☰ putih: hanya desktop (lg:hidden dihapus, diganti hidden di mobile) --}}
            <button
                class="hidden lg:hidden w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700"
                @click="openMobileSidebar()"
                type="button"
            >
                ☰
            </button>

            <div class="flex items-center gap-2 min-w-0">
                {{-- Tombol Setting (desktop) / Buka Sidebar (mobile) --}}
                <div class="relative" x-data="{ openSetting: false }">

                    {{-- Desktop: buka dropdown setting --}}
                    <button
                        type="button"
                        @click="openSetting = !openSetting"
                        class="hidden lg:flex flex-col justify-center gap-1.5 w-10 h-10 rounded-xl bg-white hover:bg-slate-50 shadow-sm px-2 transition"
                        title="Pengaturan"
                    >
                        <span class="block h-1.5 rounded-full" style="width:100%;background:#184E77;"></span>
                        <span class="block h-1.5 rounded-full" style="width:85%;background:#0d2d52;"></span>
                        <span class="block h-1.5 rounded-full" style="width:70%;background:#071428;"></span>
                    </button>

                    {{-- Mobile: buka sidebar --}}
                    <button
                        type="button"
                        @click="openMobileSidebar()"
                        class="lg:hidden flex flex-col justify-center gap-1.5 w-10 h-10 rounded-xl bg-white hover:bg-slate-50 shadow-sm px-2 transition"
                        title="Menu"
                    >
                        <span class="block h-1.5 rounded-full" style="width:100%;background:#184E77;"></span>
                        <span class="block h-1.5 rounded-full" style="width:85%;background:#0d2d52;"></span>
                        <span class="block h-1.5 rounded-full" style="width:70%;background:#071428;"></span>
                    </button>

                    {{-- Dropdown Setting --}}
                    <div
                        x-show="openSetting"
                        @click.outside="openSetting = false"
                        x-transition
                        class="absolute left-0 top-10 w-52 bg-white rounded-2xl shadow-lg border border-slate-100 z-50 py-2"
                        style="display:none"
                    >
                        <div class="px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Pengaturan</div>

                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profil Saya
                        </a>

                        <a href="{{ route('profile.password') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Ganti Password
                        </a>
                    </div>
                </div>

                <div id="topbar-page-title" class="text-lg sm:text-xl font-semibold text-slate-800 truncate hidden sm:block"
                     x-show="sidebarMini">
                    @if(request()->routeIs('dashboard'))
                        Overview
                    @elseif(request()->routeIs('spareparts.*'))
                        Data Sparepart
                    @elseif(request()->routeIs('inventory.*'))
                        Riwayat Inventory
                    @elseif(request()->routeIs('pos.*'))
                        Kasir POS
                    @elseif(request()->routeIs('transactions.*'))
                        Riwayat Transaksi
                    @elseif(request()->routeIs('profile.*'))
                        Profil
                    @else
                        Brangkas Garage
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="flex items-center gap-3 sm:gap-6 shrink-0">
            <div class="flex items-center gap-3">
                <div class="text-right hidden md:block">
                    <div class="font-semibold text-slate-800">
                        {{ auth()->user()->name ?? 'User' }}
                    </div>
                    <div class="text-xs text-slate-500">
                        {{ auth()->user()->email ?? '-' }}
                    </div>
                </div>

                {{-- Avatar dengan dropdown --}}
                <div class="relative" x-data="{ openAvatar: false }">
                    <button
                        type="button"
                        @click="openAvatar = !openAvatar"
                        class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-slate-700 shadow hover:bg-slate-200 transition cursor-pointer"
                        title="Akun Saya">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </button>

                    <div
                        x-show="openAvatar"
                        @click.outside="openAvatar = false"
                        x-transition
                        class="absolute right-0 top-13 w-52 bg-white rounded-2xl shadow-lg border border-slate-100 z-50 py-2 mt-1"
                        style="display:none;">

                        <div class="px-4 py-2 border-b border-slate-100">
                            <div class="text-sm font-semibold text-slate-800">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-xs text-slate-500">{{ auth()->user()->email ?? '-' }}</div>
                        </div>

                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profil Saya
                        </a>

                        <a href="{{ route('profile.password') }}"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Ganti Password
                        </a>

                        <div class="border-t border-slate-100 my-1"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

