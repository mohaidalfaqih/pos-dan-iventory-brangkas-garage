@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- ===== STAT CARDS ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">

        {{-- Omzet Hari Ini --}}
        <div class="bg-white rounded-2xl shadow p-5 col-span-2 lg:col-span-1 border-l-4" style="border-color:#0d2d52;">
            <div class="mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Omzet Hari Ini</span>
            </div>
            <div class="text-2xl font-bold text-slate-800">
                Rp <span class="counter" data-to="{{ (int)$todayRevenue }}" data-duration="900">0</span>
            </div>
            <div class="mt-2">
                @if($revenueChangeDirection === 'up')
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-semibold">
                        ▲ {{ $revenueChangePercent }}% vs kemarin
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">
                        ▼ {{ $revenueChangePercent }}% vs kemarin
                    </span>
                @endif
            </div>
        </div>

        {{-- Transaksi Hari Ini --}}
        <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-blue-400">
            <div class="mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Transaksi</span>
            </div>
            <div class="text-2xl font-bold text-slate-800">
                <span class="counter" data-to="{{ (int)$todayTransactions }}" data-duration="700">0</span>
            </div>
            <div class="text-xs text-slate-400 mt-2">Hari ini</div>
        </div>

        {{-- Omzet Bulan Ini --}}
        <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-purple-400">
            <div class="mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Bulan Ini</span>
            </div>
            <div class="text-2xl font-bold text-slate-800">
                Rp <span class="counter" data-to="{{ (int)$monthlyRevenue }}" data-duration="900">0</span>
            </div>
            <div class="text-xs text-slate-400 mt-2">{{ now()->translatedFormat('F Y') }}</div>
        </div>

        {{-- Total Produk --}}
        <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-emerald-400">
            <div class="mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Produk</span>
            </div>
            <div class="text-2xl font-bold text-slate-800">
                <span class="counter" data-to="{{ (int)$totalProducts }}" data-duration="700">0</span>
            </div>
            <div class="text-xs text-slate-400 mt-2">Total SKU</div>
        </div>

        {{-- Stok Habis --}}
        <a href="{{ route('spareparts.index', ['stock' => 'out']) }}"
           class="bg-white rounded-2xl shadow p-5 border-l-4 border-red-400 block hover:shadow-md transition">
            <div class="mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Stok Habis</span>
            </div>
            <div class="text-2xl font-bold text-red-600">
                <span class="counter" data-to="{{ (int)$outOfStock }}" data-duration="700">0</span>
            </div>
            <div class="text-xs text-slate-400 mt-2">Klik untuk lihat →</div>
        </a>

    </div>

    {{-- ===== GRAFIK ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <div class="bg-white rounded-2xl shadow p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-700">Penjualan 7 Hari Terakhir</h2>
            </div>
            <div class="h-52">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-700">Penjualan Bulanan {{ now()->year }}</h2>
            </div>
            <div class="h-52">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

    </div>

    {{-- ===== REKAP TAHUNAN ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Tabel ringkasan per tahun --}}
        <a href="{{ route('rekap.index') }}"
           class="bg-white rounded-2xl shadow p-5 block hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#e8f0fe;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#0d2d52" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-semibold text-slate-700">Rekap Tahunan</h2>
                </div>
                <span class="text-xs font-semibold group-hover:underline" style="color:#0d2d52;">Lihat detail →</span>
            </div>

            @if($yearlyStats->isEmpty())
                <div class="text-sm text-slate-400 text-center py-6">Belum ada data transaksi.</div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-500 border-b">
                            <th class="pb-2 font-semibold">Tahun</th>
                            <th class="pb-2 font-semibold text-right">Transaksi</th>
                            <th class="pb-2 font-semibold text-right">Total Omzet</th>
                            <th class="pb-2 font-semibold text-right">Rata-rata/Trx</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($yearlyStats as $ys)
                            @php
                                $avg = $ys['transactions'] > 0 ? (int)($ys['revenue'] / $ys['transactions']) : 0;
                                $isCur = $ys['year'] == now()->year;
                            @endphp
                            <tr class="{{ $isCur ? 'bg-blue-50' : '' }}">
                                <td class="py-2.5 font-bold {{ $isCur ? 'text-blue-700' : 'text-slate-800' }}">
                                    {{ $ys['year'] }}
                                    @if($isCur)
                                        <span class="ml-1 text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full">Ini</span>
                                    @endif
                                </td>
                                <td class="py-2.5 text-right text-slate-700">{{ number_format($ys['transactions']) }}</td>
                                <td class="py-2.5 text-right font-semibold text-slate-800">Rp {{ number_format($ys['revenue']) }}</td>
                                <td class="py-2.5 text-right text-slate-500 text-xs">Rp {{ number_format($avg) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if($yearlyStats->count() > 1)
                    <tfoot>
                        <tr class="border-t-2 border-slate-200">
                            <td class="pt-2.5 font-bold text-slate-700">Total</td>
                            <td class="pt-2.5 text-right font-bold text-slate-700">{{ number_format($yearlyStats->sum('transactions')) }}</td>
                            <td class="pt-2.5 text-right font-bold text-slate-800">Rp {{ number_format($yearlyStats->sum('revenue')) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            @endif
        </a>

        {{-- Grafik perbandingan omzet tahunan --}}
        <a href="{{ route('rekap.index') }}"
           class="bg-white rounded-2xl shadow p-5 block hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#e8f0fe;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#0d2d52" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-semibold text-slate-700">Perbandingan Omzet Tahunan</h2>
                </div>
            </div>
            <div class="h-52">
                <canvas id="yearlyChart"></canvas>
            </div>
        </a>

    </div>

    {{-- ===== BARANG TERLARIS + STOK MENIPIS + STOK HABIS ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="bg-white rounded-2xl shadow p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:#e8f0fe;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#0d2d52" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <h2 class="text-sm font-semibold text-slate-700">Barang Terlaris</h2>
            </div>
            @if($topProducts->count() === 0)
                <div class="text-sm text-slate-400 text-center py-6">Belum ada data penjualan.</div>
            @else
                <div class="space-y-3">
                    @foreach($topProducts as $i => $item)
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                                 style="background:{{ $i === 0 ? '#0d2d52' : ($i === 1 ? '#184E77' : '#2B648F') }};">
                                {{ $i + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-slate-700 truncate cursor-pointer"
                                     title="{{ $item->nama }}"
                                     onclick="this.classList.toggle('truncate')">
                                    {{ implode(' ', array_slice(explode(' ', $item->nama), 0, 3)) }}{{ count(explode(' ', $item->nama)) > 3 ? '...' : '' }}
                                </div>
                                <div class="mt-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    @php $maxQty = $topProducts->max('total_qty'); @endphp
                                    <div class="h-full rounded-full" style="width:{{ $maxQty > 0 ? round(($item->total_qty / $maxQty) * 100) : 0 }}%;background:#0d2d52;"></div>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-slate-800 shrink-0">{{ $item->total_qty }} pcs</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-semibold text-slate-700">Stok Menipis</h2>
                </div>
                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-semibold">≤ {{ $lowStockLimit }}</span>
            </div>

            @if($lowStockItems->count() === 0)
                <div class="text-sm text-slate-400 text-center py-6">Semua stok aman ✓</div>
            @else
                <div class="space-y-2">
                    @foreach($lowStockItems as $sp)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-orange-50">
                            <span class="text-sm text-slate-700 truncate cursor-pointer flex-1 mr-3"
                                  title="{{ $sp->nama_barang }}"
                                  onclick="this.classList.toggle('truncate')">
                                {{ $sp->nama_barang }}
                            </span>
                            <span class="text-sm font-bold shrink-0 px-2 py-0.5 rounded-full bg-orange-200 text-orange-700">
                                {{ $sp->stok }} pcs
                            </span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('spareparts.index', ['stock' => 'low']) }}"
                   class="mt-3 block text-center text-xs font-semibold hover:underline" style="color:#0d2d52;">
                    Lihat semua →
                </a>
            @endif
        </div>

        {{-- Panel Stok Habis --}}
        <div class="bg-white rounded-2xl shadow p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-semibold text-slate-700">Stok Habis</h2>
                </div>
                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-semibold">{{ $outOfStock }} item</span>
            </div>

            @if($outOfStockItems->count() === 0)
                <div class="text-sm text-slate-400 text-center py-6">Tidak ada stok habis ✓</div>
            @else
                <div class="space-y-2">
                    @foreach($outOfStockItems as $sp)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-red-50">
                            <span class="text-sm text-slate-700 truncate cursor-pointer flex-1 mr-3"
                                  title="{{ $sp->nama_barang }}"
                                  onclick="this.classList.toggle('truncate')">
                                {{ $sp->nama_barang }}
                            </span>
                            <span class="text-sm font-bold shrink-0 px-2 py-0.5 rounded-full bg-red-200 text-red-700">
                                HABIS
                            </span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('spareparts.index', ['stock' => 'out']) }}"
                   class="mt-3 block text-center text-xs font-semibold text-red-600 hover:underline">
                    Lihat semua →
                </a>
            @endif
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function initDashboardCharts() {
        if (typeof Chart === 'undefined') {
            // Chart.js belum siap, tunggu sebentar
            setTimeout(initDashboardCharts, 100);
            return;
        }

        // Destroy chart lama jika ada
        ['salesChart','monthlyChart','yearlyChart'].forEach(id => {
            const existing = Chart.getChart ? Chart.getChart(id) : null;
            if (existing) existing.destroy();
        });

    const navyColor = '#0d2d52';
    const navyLight = 'rgba(13,45,82,0.1)';

    // Grafik 7 hari
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($salesChart->pluck('date')) !!},
            datasets: [{
                data: {!! json_encode($salesChart->pluck('total')) !!},
                borderColor: navyColor,
                backgroundColor: navyLight,
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: navyColor,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: {
                callbacks: {
                    label: ctx => ' Rp ' + Number(ctx.raw).toLocaleString('id-ID')
                }
            }},
            scales: {
                y: { ticks: { callback: v => 'Rp ' + Number(v).toLocaleString('id-ID'), font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });

    // Grafik bulanan
    const monthMap = {1:'Jan',2:'Feb',3:'Mar',4:'Apr',5:'Mei',6:'Jun',7:'Jul',8:'Agu',9:'Sep',10:'Okt',11:'Nov',12:'Des'};
    const monthlyLabels = {!! json_encode($monthlyChart->pluck('month')) !!}.map(m => monthMap[m] ?? m);

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                data: {!! json_encode($monthlyChart->pluck('total')) !!},
                backgroundColor: navyColor,
                borderRadius: 6,
                hoverBackgroundColor: '#184E77',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: {
                callbacks: {
                    label: ctx => ' Rp ' + Number(ctx.raw).toLocaleString('id-ID')
                }
            }},
            scales: {
                y: { ticks: { callback: v => 'Rp ' + Number(v).toLocaleString('id-ID'), font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });

    // Grafik tahunan
    new Chart(document.getElementById('yearlyChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($yearlyChart->pluck('year')) !!},
            datasets: [{
                data: {!! json_encode($yearlyChart->pluck('revenue')) !!},
                backgroundColor: {!! json_encode($yearlyChart->map(fn($y) => $y['year'] == now()->year ? '#0d2d52' : '#93c5fd')->values()) !!},
                borderRadius: 6,
                hoverBackgroundColor: '#184E77',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' Rp ' + Number(ctx.raw).toLocaleString('id-ID') } }
            },
            scales: {
                y: { ticks: { callback: v => 'Rp ' + Number(v).toLocaleString('id-ID'), font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                x: { ticks: { font: { size: 11, weight: 'bold' } }, grid: { display: false } }
            }
        }
    });

    // Counter animation
    function animateCounter(el) {
        const to = Number(el.dataset.to || 0);
        const duration = Number(el.dataset.duration || 800);
        const startTime = performance.now();
        function tick(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(to * eased).toLocaleString('id-ID');
            if (progress < 1) requestAnimationFrame(tick);
            else el.textContent = to.toLocaleString('id-ID');
        }
        requestAnimationFrame(tick);
    }
    document.querySelectorAll('.counter').forEach(animateCounter);
    }

    initDashboardCharts();
</script>
@endsection
