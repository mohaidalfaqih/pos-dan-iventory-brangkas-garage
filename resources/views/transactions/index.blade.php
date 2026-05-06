@extends('layouts.app')

@section('content')
<div class="space-y-5">

    <div>
        <a href="{{ route('transactions.export', array_filter(['q' => $q ?? '', 'date_from' => $dateFrom ?? '', 'date_to' => $dateTo ?? ''])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
           style="background-color:#0d2d52;">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            Export Excel
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    {{-- FILTER --}}
    <div class="bg-white rounded-2xl shadow p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            <div class="md:col-span-2">
                <label class="text-xs text-slate-500">Cari</label>
                <div class="mt-1 bg-slate-50 rounded-xl border px-4 py-2 flex items-center gap-2">
                    <span class="text-gray-400">🔍︎</span>
                    <input
                        type="text"
                        name="q"
                        value="{{ $q ?? '' }}"
                        placeholder="Cari invoice / nama / id..."
                        class="w-full border-0 focus:ring-0 outline-none text-sm bg-transparent"
                    >
                </div>
            </div>

            <div>
                <label class="text-xs text-slate-500">Dari Tanggal</label>
                <input
                    type="date"
                    name="date_from"
                    value="{{ $dateFrom ?? '' }}"
                    class="mt-1 w-full border rounded-xl px-3 py-2 text-sm"
                >
            </div>

            <div>
                <label class="text-xs text-slate-500">Sampai Tanggal</label>
                <input
                    type="date"
                    name="date_to"
                    value="{{ $dateTo ?? '' }}"
                    class="mt-1 w-full border rounded-xl px-3 py-2 text-sm"
                >
            </div>

            <div class="flex gap-2">
                <button
                    class="flex-1 px-4 py-2 rounded-xl bg-slate-900 text-white text-sm hover:bg-slate-800 transition">
                    Cari
                </button>

                <a
                    href="{{ route('transactions.index') }}"
                    class="flex-1 text-center px-4 py-2 rounded-xl bg-slate-200 text-slate-700 text-sm hover:bg-slate-300 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- TOTAL UANG --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <div class="text-sm text-slate-500">Total Uang Didapat</div>
        <div class="mt-2 text-3xl font-bold text-slate-900">
            Rp {{ number_format($totalRevenue) }}
        </div>
        <div class="mt-1 text-xs text-slate-500">
            @if($dateFrom || $dateTo)
                Berdasarkan filter tanggal
                @if($dateFrom) dari {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} @endif
                @if($dateTo) sampai {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }} @endif
            @else
                Berdasarkan seluruh data transaksi
            @endif
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-2xl shadow p-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500 border-b">
                    <th class="py-3 px-2">Tanggal</th>
                    <th class="py-3 px-2">Invoice</th>
                    <th class="py-3 px-2">Pembeli</th>
                    <th class="py-3 px-2">Item</th>
                    <th class="py-3 px-2">Total</th>
                    <th class="py-3 px-2">Bayar</th>
                    <th class="py-3 px-2">Status</th>
                    <th class="py-3 px-2 text-right">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($transactions as $t)
                    <tr class="border-b last:border-b-0">
                        <td class="py-3 px-2 text-slate-600">
                            {{ $t->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="py-3 px-2 font-semibold text-slate-800">
                            {{ $t->invoice ?? ('TRX-'.$t->id) }}
                        </td>

                        <td class="py-3 px-2 text-slate-700">
                            {{ $t->buyer_name }}
                        </td>

                        <td class="py-3 px-2 text-slate-600">
                            {{ $t->items_count }}
                        </td>

                        <td class="py-3 px-2 font-semibold">
                            Rp {{ number_format($t->total) }}
                        </td>

                        <td class="py-3 px-2 text-slate-600">
                            Rp {{ number_format($t->paid) }}
                        </td>

                        <td class="py-3 px-2">
                            @if(($t->status ?? 'OK') === 'OK')
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 font-semibold">
                                    OK
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700 font-semibold">
                                    KURANG
                                </span>
                            @endif
                        </td>

                        <td class="py-3 px-2 text-right">
                            <a href="{{ route('transactions.show', $t->id) }}"
                               class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-xs transition">
                                Lihat Struk
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-slate-500">
                            Belum ada transaksi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>

</div>
@endsection