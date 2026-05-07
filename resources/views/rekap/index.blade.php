@extends('layouts.app')

@section('content')
@php
$monthNames = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
    5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
    9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
];
@endphp

<div class="space-y-6">

    {{-- Header + Filter --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Rekap Tahunan</h1>
            <p class="text-sm text-slate-500 mt-0.5">Ringkasan omzet dan transaksi per tahun & bulan</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Pilih tahun --}}
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <select name="year" onchange="this.form.submit()"
                            style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 0.875rem; appearance: none; -webkit-appearance: none; background-color: #fff; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 0.5rem 2.5rem 0.5rem 1rem; color: #1e293b; cursor: pointer; outline: none; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                        @foreach($availableYears as $yr)
                            <option value="{{ $yr }}" {{ $yr == $selectedYear ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                    {{-- Ikon panah --}}
                    <div style="pointer-events:none; position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); color:#64748b;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </form>

            {{-- Export CSV --}}
            <a href="{{ route('rekap.export', ['year' => $selectedYear]) }}"
               target="_blank"
               class="flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition"
               style="background:#0d2d52;">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    {{-- Stat cards tahun terpilih --}}
    @php
        $selStat = $yearlyStats->firstWhere('year', $selectedYear);
        $selRevenue = $selStat ? $selStat['revenue'] : 0;
        $selTrx     = $selStat ? $selStat['transactions'] : 0;
        $selAvg     = $selTrx > 0 ? (int)($selRevenue / $selTrx) : 0;

        // Cari tahun sebelumnya untuk perbandingan
        $prevStat    = $yearlyStats->firstWhere('year', $selectedYear - 1);
        $prevRevenue = $prevStat ? $prevStat['revenue'] : 0;
        $growthPct   = $prevRevenue > 0 ? round((($selRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : null;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow p-5 border-l-4" style="border-color:#0d2d52;">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Omzet {{ $selectedYear }}</div>
            <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($selRevenue) }}</div>
            @if($growthPct !== null)
                <div class="mt-2">
                    @if($growthPct >= 0)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-semibold">▲ {{ $growthPct }}% vs {{ $selectedYear - 1 }}</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">▼ {{ abs($growthPct) }}% vs {{ $selectedYear - 1 }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-blue-400">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Total Transaksi {{ $selectedYear }}</div>
            <div class="text-2xl font-bold text-slate-800">{{ number_format($selTrx) }}</div>
            <div class="text-xs text-slate-400 mt-2">transaksi</div>
        </div>

        <div class="bg-white rounded-2xl shadow p-5 border-l-4 border-purple-400">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Rata-rata per Transaksi</div>
            <div class="text-2xl font-bold text-slate-800">Rp {{ number_format($selAvg) }}</div>
            <div class="text-xs text-slate-400 mt-2">per transaksi</div>
        </div>
    </div>

    {{-- Grafik bulanan --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Omzet Bulanan {{ $selectedYear }}</h2>
        <div class="h-64">
            <canvas id="monthlyRekapChart"></canvas>
        </div>
    </div>

    {{-- Tabel breakdown bulanan --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Detail Per Bulan — {{ $selectedYear }}</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 border-b uppercase tracking-wide">
                        <th class="pb-3 font-semibold">Bulan</th>
                        <th class="pb-3 font-semibold text-right">Transaksi</th>
                        <th class="pb-3 font-semibold text-right">Total Omzet</th>
                        <th class="pb-3 font-semibold text-right">Total Dibayar</th>
                        <th class="pb-3 font-semibold text-right">Rata-rata/Trx</th>
                        <th class="pb-3 font-semibold">Proporsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($months as $m)
                        @php
                            $avg  = $m['transactions'] > 0 ? (int)($m['revenue'] / $m['transactions']) : 0;
                            $pct  = $selRevenue > 0 ? round(($m['revenue'] / $selRevenue) * 100, 1) : 0;
                            $isCurrent = $m['month'] == now()->month && $selectedYear == now()->year;
                        @endphp
                        <tr class="{{ $isCurrent ? 'bg-blue-50' : ($m['revenue'] > 0 ? '' : 'opacity-40') }}">
                            <td class="py-3 font-semibold {{ $isCurrent ? 'text-blue-700' : 'text-slate-800' }}">
                                {{ $monthNames[$m['month']] }}
                                @if($isCurrent)
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full">Ini</span>
                                @endif
                            </td>
                            <td class="py-3 text-right text-slate-700">{{ number_format($m['transactions']) }}</td>
                            <td class="py-3 text-right font-semibold text-slate-800">
                                {{ $m['revenue'] > 0 ? 'Rp '.number_format($m['revenue']) : '-' }}
                            </td>
                            <td class="py-3 text-right text-slate-600">
                                {{ $m['paid'] > 0 ? 'Rp '.number_format($m['paid']) : '-' }}
                            </td>
                            <td class="py-3 text-right text-slate-500 text-xs">
                                {{ $avg > 0 ? 'Rp '.number_format($avg) : '-' }}
                            </td>
                            <td class="py-3 w-32">
                                @if($pct > 0)
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full" style="width:{{ $pct }}%;background:#0d2d52;"></div>
                                        </div>
                                        <span class="text-xs text-slate-500 w-8 text-right">{{ $pct }}%</span>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-300 bg-slate-50">
                        <td class="pt-3 pb-2 font-bold text-slate-800">TOTAL</td>
                        <td class="pt-3 pb-2 text-right font-bold text-slate-800">{{ number_format($months->sum('transactions')) }}</td>
                        <td class="pt-3 pb-2 text-right font-bold text-slate-800">Rp {{ number_format($months->sum('revenue')) }}</td>
                        <td class="pt-3 pb-2 text-right font-bold text-slate-800">Rp {{ number_format($months->sum('paid')) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Tabel perbandingan semua tahun --}}
    @if($yearlyStats->count() > 1)
    <div class="bg-white rounded-2xl shadow p-5">
        <h2 class="text-sm font-semibold text-slate-700 mb-4">Perbandingan Semua Tahun</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 border-b uppercase tracking-wide">
                        <th class="pb-3 font-semibold">Tahun</th>
                        <th class="pb-3 font-semibold text-right">Transaksi</th>
                        <th class="pb-3 font-semibold text-right">Total Omzet</th>
                        <th class="pb-3 font-semibold text-right">Rata-rata/Trx</th>
                        <th class="pb-3 font-semibold text-right">Growth</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($yearlyStats as $idx => $ys)
                        @php
                            $avg2  = $ys['transactions'] > 0 ? (int)($ys['revenue'] / $ys['transactions']) : 0;
                            $prev2 = $yearlyStats->get($idx + 1);
                            $growth2 = ($prev2 && $prev2['revenue'] > 0)
                                ? round((($ys['revenue'] - $prev2['revenue']) / $prev2['revenue']) * 100, 1)
                                : null;
                            $isCur = $ys['year'] == now()->year;
                        @endphp
                        <tr class="{{ $isCur ? 'bg-blue-50' : '' }}">
                            <td class="py-3 font-bold {{ $isCur ? 'text-blue-700' : 'text-slate-800' }}">
                                {{ $ys['year'] }}
                                @if($isCur)
                                    <span class="ml-1 text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full">Ini</span>
                                @endif
                            </td>
                            <td class="py-3 text-right text-slate-700">{{ number_format($ys['transactions']) }}</td>
                            <td class="py-3 text-right font-semibold text-slate-800">Rp {{ number_format($ys['revenue']) }}</td>
                            <td class="py-3 text-right text-slate-500 text-xs">Rp {{ number_format($avg2) }}</td>
                            <td class="py-3 text-right">
                                @if($growth2 !== null)
                                    @if($growth2 >= 0)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-semibold">▲ {{ $growth2 }}%</span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">▼ {{ abs($growth2) }}%</span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-300 bg-slate-50">
                        <td class="pt-3 pb-2 font-bold text-slate-800">TOTAL</td>
                        <td class="pt-3 pb-2 text-right font-bold text-slate-800">{{ number_format($yearlyStats->sum('transactions')) }}</td>
                        <td class="pt-3 pb-2 text-right font-bold text-slate-800">Rp {{ number_format($yearlyStats->sum('revenue')) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    const monthMap = {1:'Jan',2:'Feb',3:'Mar',4:'Apr',5:'Mei',6:'Jun',7:'Jul',8:'Agu',9:'Sep',10:'Okt',11:'Nov',12:'Des'};
    const labels   = {!! json_encode($chartLabels) !!}.map(m => monthMap[m] ?? m);
    const data     = {!! json_encode($chartRevenue) !!};
    const curMonth = {{ now()->month }};
    const selYear  = {{ $selectedYear }};
    const nowYear  = {{ now()->year }};

    const colors = labels.map((_, i) =>
        (selYear === nowYear && (i + 1) === curMonth) ? '#0d2d52' : '#93c5fd'
    );

    function init() {
        if (typeof Chart === 'undefined') { setTimeout(init, 100); return; }
        const existing = Chart.getChart ? Chart.getChart('monthlyRekapChart') : null;
        if (existing) existing.destroy();

        new Chart(document.getElementById('monthlyRekapChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
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
                    x: { ticks: { font: { size: 11 } }, grid: { display: false } }
                }
            }
        });
    }
    init();
})();
</script>
@endsection
