@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <div class="flex items-center justify-between">
            <a href="{{ route('inventory.export', array_filter(['q' => $q ?? '', 'date_from' => $dateFrom ?? '', 'date_to' => $dateTo ?? ''])) }}"
               target="_blank"
               class="flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold transition hover:opacity-90"
               style="background-color:#0d2d52;">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Export Excel
            </a>
            <div></div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            <div class="md:col-span-2">
                <label class="text-xs text-slate-500">Cari</label>
                <div class="mt-1 bg-slate-50 rounded-xl border px-4 py-2 flex items-center gap-2">
                    <span class="text-gray-400">🔍︎</span>
                    <input
                        type="text"
                        name="q"
                        value="{{ $q ?? '' }}"
                        placeholder="Cari kode / nama barang..."
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
                    href="{{ route('inventory.index') }}"
                    class="flex-1 text-center px-4 py-2 rounded-xl bg-slate-200 text-slate-700 text-sm hover:bg-slate-300 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl shadow p-5">
            <div class="text-sm text-slate-500">Total Barang Masuk (IN)</div>
            <div class="mt-2 text-3xl font-bold text-green-700">
                {{ number_format($totalIn) }}
            </div>
            <div class="mt-1 text-xs text-slate-500">
                @if($dateFrom || $dateTo)
                    Berdasarkan filter tanggal
                @else
                    Berdasarkan seluruh data inventory
                @endif
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-5">
            <div class="text-sm text-slate-500">Total Barang Keluar (OUT)</div>
            <div class="mt-2 text-3xl font-bold text-red-700">
                {{ number_format($totalOut) }}
            </div>
            <div class="mt-1 text-xs text-slate-500">
                @if($dateFrom || $dateTo)
                    Berdasarkan filter tanggal
                @else
                    Berdasarkan seluruh data inventory
                @endif
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Barang</th>
                        <th class="px-4 py-3 text-center">Type</th>
                        <th class="px-4 py-3 text-center">Qty</th>
                        <th class="px-4 py-3 text-left">Catatan</th>
                        <th class="px-4 py-3 text-left">ID Ref</th>
                        <th class="px-4 py-3 text-left">User</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 text-slate-700">
                                {{ optional($m->created_at)->format('d/m/Y H:i') }}
                            </td>

                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $m->sparepart->kode ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-slate-700" style="max-width:200px;">
                                <div class="truncate cursor-pointer hover:text-slate-900"
                                     title="{{ $m->sparepart->nama_barang ?? '-' }}"
                                     onclick="this.classList.toggle('truncate'); this.closest('td').style.maxWidth = this.classList.contains('truncate') ? '200px' : 'none';">
                                    {{ $m->sparepart->nama_barang ?? '-' }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-center">
                                @if($m->type === 'IN')
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        IN
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        OUT
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3 text-center font-semibold">
                                {{ $m->qty }}
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                {{ $m->note ?? '-' }}
                            </td>

                            <td class="px-4 py-3 text-slate-600 font-mono text-xs">
                                @if($m->reference_id)
                                    @if($m->type === 'OUT')
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                            TRX-{{ str_pad($m->reference_id, 4, '0', STR_PAD_LEFT) }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                                            SP-{{ str_pad($m->reference_id, 4, '0', STR_PAD_LEFT) }}
                                        </span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-4 py-3 text-slate-600">
                                {{ $m->user->name ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                Belum ada riwayat inventory.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $movements->links() }}
        </div>
    </div>

</div>
@endsection