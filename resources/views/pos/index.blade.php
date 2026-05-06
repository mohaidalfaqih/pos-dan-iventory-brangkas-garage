@extends('layouts.app')

@section('content')
<style>
    [x-cloak] { display: none !important; }
</style>

<div
    x-data="posPage()"
    x-init="$nextTick(() => { window.addEventListener('open-payment', () => { openPayment = true; }) })"
    class="flex flex-col lg:flex-row gap-4 lg:h-[calc(100vh-140px)]"
>
    {{-- Hidden inputs --}}
    <input type="hidden" id="__pos_show_payment__" value="{{ session()->has('show_payment') ? '1' : '0' }}">
    <input type="hidden" id="__pos_show_receipt__" value="{{ session()->has('show_receipt') ? '1' : '0' }}">
    <input type="hidden" id="__pos_subtotal__" value="{{ $subtotalAll ?? 0 }}">

    {{-- ================= LEFT (PRODUK) ================= --}}
    <div class="flex-1 flex flex-col gap-3 min-w-0 lg:overflow-hidden">

        {{-- Header + Search --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="shrink-0">
                <h1 class="text-xl font-semibold text-slate-800">Kasir Brangkas Garage</h1>
                <p class="text-slate-500 text-xs">Pilih barang untuk transaksi.</p>
            </div>
            <form method="GET" class="flex-1">
                <div class="bg-white rounded-xl shadow px-3 py-2 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input name="q" value="{{ $q ?? '' }}"
                           placeholder="Cari kode / nama..."
                           class="w-full border-0 focus:ring-0 outline-none text-sm">
                </div>
            </form>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded-xl text-sm">{{ session('error') }}</div>
        @endif

        {{-- Grid Produk --}}
        <div class="bg-white rounded-2xl shadow p-3 flex-1 overflow-y-auto">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-2">
                @foreach($spareparts as $p)
                <div class="bg-slate-50 rounded-xl p-2 flex flex-col hover:shadow-md hover:bg-white transition-all duration-150 border border-transparent hover:border-slate-200">

                    <!-- Foto -->
                    <div class="w-full aspect-square rounded-lg overflow-hidden bg-white border border-slate-100 flex items-center justify-center cursor-pointer"
                         onclick="showDetail({{ $p->id }}, '{{ e($p->nama_barang) }}', '{{ e($p->kode) }}', '{{ $p->foto ? asset('storage/'.$p->foto) : '' }}', {{ $p->harga_jual }}, {{ $p->harga_beli }}, {{ $p->stok }})">
                        @if($p->foto)
                            <img src="{{ asset('storage/'.$p->foto) }}"
                                 alt="{{ $p->nama_barang }}"
                                 class="h-full w-full object-contain block">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        @endif
                    </div>

                    <!-- Nama -->
                    <div class="mt-1.5 text-[11px] font-semibold text-slate-800 leading-tight truncate"
                         title="{{ $p->nama_barang }}">
                        {{ implode(' ', array_slice(explode(' ', $p->nama_barang), 0, 2)) }}{{ count(explode(' ', $p->nama_barang)) > 2 ? '...' : '' }}
                    </div>

                    <!-- Harga -->
                    <div class="mt-0.5 text-[11px] font-bold text-slate-700">
                        Rp {{ number_format($p->harga_jual) }}
                    </div>

                    <!-- Stok + Kode -->
                    <div class="mt-0.5 flex items-center justify-between text-[10px]">
                        <span class="{{ $p->stok <= 3 ? 'text-red-500 font-semibold' : 'text-slate-400' }}">
                            Stok: {{ $p->stok }}
                        </span>
                        <span class="text-slate-400">{{ $p->kode }}</span>
                    </div>

                    <!-- Tombol -->
                    <div class="mt-1.5">
                        <button
                            type="button"
                            @click="post('{{ route('pos.add') }}', { sparepart_id: {{ $p->id }} })"
                            class="w-full py-1.5 text-[11px] font-semibold rounded-lg text-white transition hover:opacity-90 disabled:opacity-40"
                            style="background:#0d2d52;"
                            {{ $p->stok <= 0 ? 'disabled' : '' }}
                        >
                            + Tambah
                        </button>
                    </div>

                </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $spareparts->links() }}
            </div>
        </div>
    </div>

    {{-- ================= RIGHT (KERANJANG) ================= --}}
    <div class="w-full lg:w-80 xl:w-96 shrink-0 lg:sticky lg:top-0 lg:self-start lg:max-h-[calc(100vh-140px)] lg:overflow-y-auto">
        <div id="cart-panel">
            @include('pos.partials.cart_panel')
        </div>
    </div>

    {{-- OVERLAY --}}
    <div
        x-show="openPayment || openReceipt"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 z-50"
        style="display:none"
        @click="openPayment=false; openReceipt=false"
    ></div>

    {{-- MODAL PEMBAYARAN --}}
    <div
        x-show="openPayment"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none"
        @keydown.escape.window="openPayment=false"
    >
        <div
            class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-5 relative"
            @click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        >
            <button class="absolute right-4 top-4 text-slate-400 hover:text-slate-700"
                    @click="openPayment=false">✕</button>

            <div class="text-center mb-3">
                <div class="text-lg font-bold text-slate-800">Pembayaran</div>
                <div class="text-xs text-slate-500 mt-1">Isi nama pembeli & uang dibayar</div>
            </div>

            <div class="mt-4 text-sm bg-slate-50 rounded-xl p-3 flex justify-between">
                <span class="text-slate-600">Total</span>
                <span class="font-semibold" x-text="formatRupiah(subtotal)"></span>
            </div>

            <form method="POST" action="{{ route('pos.finish') }}"
                  @submit="openPayment=false"
                  class="space-y-3 mt-4">
                @csrf

                <div>
                    <label class="text-xs">Nama Pembeli</label>
                    <input name="buyer_name" required
                           class="w-full mt-1 border rounded-lg px-3 py-2 text-sm"
                           placeholder="Contoh: Budi">
                </div>

                <div>
                    <label class="text-xs">Uang Dibayar</label>
                    <input type="number" name="paid" required min="0"
                           class="w-full mt-1 border rounded-lg px-3 py-2 text-sm"
                           placeholder="0">
                </div>

                <button class="w-full py-2 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-800 transition">
                    Proses
                </button>
            </form>
        </div>
    </div>

    {{-- MODAL STRUK --}}
    <div
        x-show="openReceipt"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display:none"
        @keydown.escape.window="openReceipt=false"
    >
        <div
            class="bg-white w-full max-w-sm rounded-3xl shadow-2xl relative overflow-hidden"
            @click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            {{-- Tombol tutup --}}
            <button class="absolute right-4 top-4 w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-500 text-xs z-10"
                    @click="openReceipt=false">✕</button>

            @if(session('receipt'))
                @php($r = session('receipt'))
                @php($isOk = (($r['status'] ?? '') === 'OK'))

                {{-- Header --}}
                <div class="pt-8 pb-5 px-6 flex flex-col items-center text-center border-b border-dashed border-slate-200">
                    {{-- Icon --}}
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-2xl
                                {{ $isOk ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                        {{ $isOk ? '✓' : '!' }}
                    </div>

                    <div class="mt-3 text-xl font-bold text-slate-800">
                        {{ $isOk ? 'Transaksi Berhasil' : 'Pembayaran Kurang' }}
                    </div>
                    <div class="text-xs text-slate-400 mt-1">{{ $r['time'] ?? '' }}</div>

                    {{-- Pembeli badge --}}
                    <div class="mt-3 px-4 py-1.5 rounded-full text-sm" style="background:#f0f4ff;">
                        <span class="text-slate-500">Pembeli: </span>
                        <span class="font-semibold text-slate-800">{{ $r['buyer'] ?? '-' }}</span>
                    </div>
                </div>

                {{-- Items --}}
                <div class="px-5 py-4 max-h-[28vh] overflow-y-auto space-y-2">
                    @foreach(($r['items'] ?? []) as $it)
                        <div class="flex justify-between items-start gap-2 bg-slate-50 rounded-xl px-3 py-2">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-semibold text-slate-800 truncate">
                                    {{ implode(' ', array_slice(explode(' ', $it['nama']), 0, 3)) }}{{ count(explode(' ', $it['nama'])) > 3 ? '...' : '' }}
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $it['kode'] }} &bull; {{ $it['qty'] }} x Rp {{ number_format($it['harga']) }}
                                </div>
                            </div>
                            <div class="text-xs font-bold text-slate-800 shrink-0">
                                Rp {{ number_format($it['subtotal']) }}
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Summary --}}
                <div class="px-5 pb-2 space-y-1.5 border-t border-dashed border-slate-200 pt-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Total</span>
                        <span class="font-semibold text-slate-800">Rp {{ number_format($r['total'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Dibayar</span>
                        <span class="font-semibold text-slate-800">Rp {{ number_format($r['paid'] ?? 0) }}</span>
                    </div>

                    @if($isOk)
                        <div class="flex justify-between items-center mt-2 bg-green-50 border border-green-100 rounded-2xl px-4 py-2.5">
                            <span class="text-green-700 font-semibold text-sm">Kembalian</span>
                            <span class="text-green-700 font-bold text-base">Rp {{ number_format($r['change'] ?? 0) }}</span>
                        </div>
                    @else
                        <div class="flex justify-between items-center mt-2 bg-red-50 border border-red-100 rounded-2xl px-4 py-2.5">
                            <span class="text-red-600 font-semibold text-sm">Kurang</span>
                            <span class="text-red-600 font-bold text-base">Rp {{ number_format($r['lack'] ?? 0) }}</span>
                        </div>
                        <p class="text-[10px] text-red-400 text-center">Transaksi tetap disimpan.</p>
                    @endif
                </div>

                {{-- Inject data struk ke JS --}}
                <script>
                    window.strukData = {!! json_encode([
                        'status' => $r['status'] ?? 'OK',
                        'time'   => $r['time'] ?? '',
                        'buyer'  => $r['buyer'] ?? '-',
                        'items'  => $r['items'] ?? [],
                        'total'  => $r['total'] ?? 0,
                        'paid'   => $r['paid'] ?? 0,
                        'change' => $r['change'] ?? 0,
                        'lack'   => $r['lack'] ?? 0,
                    ]) !!};
                </script>

                {{-- Tombol --}}
                <div class="px-5 pb-5 pt-3 flex gap-2">
                    <button onclick="cetakStruk()"
                            class="flex-1 py-3 rounded-2xl text-sm font-semibold transition hover:opacity-90 border-2 flex items-center justify-center gap-2"
                            style="border-color:#0d2d52;color:#0d2d52;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak
                    </button>
                    <form method="POST" action="{{ route('pos.resetReceipt') }}" class="flex-1">
                        @csrf
                        <button class="w-full py-3 rounded-2xl text-white text-sm font-semibold transition hover:opacity-90"
                                style="background:#0d2d52;">
                            Transaksi Baru
                        </button>
                    </form>
                </div>

            @else
                <div class="p-6 text-sm text-slate-500 text-center">Struk belum tersedia.</div>
            @endif
        </div>
    </div>

</div>{{-- MODAL DETAIL PRODUK --}}
<div id="modalDetail" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDetail()"></div>
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl w-[92%] max-w-sm overflow-hidden">

        <!-- Header foto -->
        <div class="relative bg-slate-100 flex items-center justify-center" style="height:220px;">
            <div id="detailImg" class="w-full h-full flex items-center justify-center">
                <span class="text-slate-400 text-xs">No Image</span>
            </div>
            <!-- Tombol tutup -->
            <button onclick="closeDetail()"
                class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/80 hover:bg-white shadow flex items-center justify-center text-slate-500 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <!-- Body -->
        <div class="p-5">
            <!-- Nama -->
            <div class="mb-4">
                <div id="detailNama" class="text-slate-800 text-sm leading-snug"></div>
            </div>

            <!-- Info grid -->
            <div class="grid grid-cols-2 gap-2 text-center text-xs">
                <div class="bg-green-50 rounded-2xl p-3">
                    <div class="text-green-500 font-medium mb-1">Harga</div>
                    <div id="detailJual" class="text-slate-700 text-sm"></div>
                </div>
                <div class="bg-slate-50 rounded-2xl p-3">
                    <div class="text-slate-400 font-medium mb-1">Stok</div>
                    <div id="detailStok" class="text-sm"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDetail(id, nama, kode, foto, jual, beli, stok) {
    document.getElementById('detailNama').textContent = nama;
    document.getElementById('detailJual').textContent = 'Rp ' + Number(jual).toLocaleString('id-ID');
    const stokEl = document.getElementById('detailStok');
    stokEl.textContent = stok;
    stokEl.className = 'text-sm font-semibold ' + (stok <= 0 ? 'text-red-600' : stok <= 3 ? 'text-yellow-600' : 'text-green-600');
    const imgEl = document.getElementById('detailImg');
    imgEl.innerHTML = foto ? '<img src="' + foto + '" class="w-full h-full object-contain p-4">' : '<span class="text-slate-400 text-xs">No Image</span>';
    document.getElementById('modalDetail').classList.remove('hidden');
}

function closeDetail() {
    document.getElementById('modalDetail').classList.add('hidden');
}

function cetakStruk() {
    const data = window.strukData;
    if (!data) { alert('Data struk tidak tersedia.'); return; }

    const isOk = data.status === 'OK';
    let rows = '';
    (data.items || []).forEach(it => {
        rows += '<tr><td>' + it.kode + '</td><td>' + it.nama + '</td><td style="text-align:center">' + it.qty + '</td><td style="text-align:right">Rp ' + Number(it.harga).toLocaleString('id-ID') + '</td><td style="text-align:right">Rp ' + Number(it.subtotal).toLocaleString('id-ID') + '</td></tr>';
    });

    const changeLabel = isOk ? 'Kembalian' : 'Kurang';
    const changeVal   = Number(isOk ? (data.change||0) : (data.lack||0)).toLocaleString('id-ID');

    const html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Struk</title>'
        + '<style>body{font-family:monospace;font-size:12px;max-width:300px;margin:0 auto;padding:16px;}'
        + 'h2,p{text-align:center;margin:2px 0;}hr{border:none;border-top:1px dashed #000;margin:8px 0;}'
        + 'table{width:100%;border-collapse:collapse;font-size:11px;}th{border-bottom:1px solid #000;padding:2px 4px;text-align:left;}'
        + 'td{padding:2px 4px;}.row{display:flex;justify-content:space-between;margin:3px 0;}'
        + '@media print{.no-print{display:none;}}</style></head><body>'
        + '<h2>Brangkas Garage</h2><p>Inventori & POS Suku Cadang</p><hr>'
        + '<p>' + (data.time||'') + '</p><p>Pembeli: <b>' + (data.buyer||'-') + '</b></p><hr>'
        + '<table><tr><th>Kode</th><th>Nama</th><th>Qty</th><th>Harga</th><th>Total</th></tr>' + rows + '</table><hr>'
        + '<div class="row"><span>Total</span><span><b>Rp ' + Number(data.total||0).toLocaleString('id-ID') + '</b></span></div>'
        + '<div class="row"><span>Dibayar</span><span>Rp ' + Number(data.paid||0).toLocaleString('id-ID') + '</span></div>'
        + '<div class="row"><span><b>' + changeLabel + '</b></span><span><b>Rp ' + changeVal + '</b></span></div><hr>'
        + '<p>Terima kasih!</p><br>'
        + '<button class="no-print" onclick="window.print()" style="width:100%;padding:8px;cursor:pointer;font-size:13px;">🖨️ Cetak Sekarang</button>'
        + '</body></html>';

    const blob = new Blob([html], {type: 'text/html'});
    const url  = URL.createObjectURL(blob);
    window.open(url, '_blank');
}
</script>
@endsection