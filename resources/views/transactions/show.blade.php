@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Detail Struk</h1>
            <p class="text-slate-500 text-sm">Ringkasan transaksi.</p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="cetakStrukDetail()"
                    class="px-4 py-2 rounded-xl text-white text-sm font-semibold flex items-center gap-2 hover:opacity-90 transition"
                    style="background:#0d2d52;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Struk
            </button>
            <a href="{{ route('transactions.index') }}"
               class="px-4 py-2 rounded-xl bg-white shadow hover:bg-slate-50 text-sm font-semibold">
                ← Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm text-slate-500">Invoice</div>
                <div class="text-lg font-semibold">
                    {{ $transaction->invoice ?? ('TRX-'.str_pad($transaction->id, 4, '0', STR_PAD_LEFT)) }}
                </div>

                <div class="mt-3 text-sm text-slate-500">Pembeli</div>
                <div class="font-semibold">{{ $transaction->buyer_name }}</div>

                <div class="mt-3 text-sm text-slate-500">Waktu</div>
                <div class="text-slate-700">{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
            </div>

            @if(($transaction->status ?? 'OK') === 'OK')
                <div class="flex flex-col items-center">
                    <div class="h-16 w-16 rounded-full bg-green-100 flex items-center justify-center text-3xl">
                        ✅
                    </div>
                    <div class="mt-2 text-green-700 font-semibold">Pembayaran OK</div>
                </div>
            @else
                <div class="flex flex-col items-center">
                    <div class="h-16 w-16 rounded-full bg-red-100 flex items-center justify-center text-3xl">
                        ❗
                    </div>
                    <div class="mt-2 text-red-700 font-semibold">Uang Kurang</div>
                </div>
            @endif
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 border-b">
                        <th class="py-2">Kode</th>
                        <th class="py-2">Nama</th>
                        <th class="py-2 text-right">Harga</th>
                        <th class="py-2 text-right">Qty</th>
                        <th class="py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaction->items as $it)
                        <tr class="border-b last:border-b-0">
                            <td class="py-2 text-slate-600">{{ $it->kode }}</td>
                            <td class="py-2 font-semibold text-slate-800">{{ $it->nama }}</td>
                            <td class="py-2 text-right">Rp {{ number_format($it->harga) }}</td>
                            <td class="py-2 text-right">{{ $it->qty }}</td>
                            <td class="py-2 text-right font-semibold">Rp {{ number_format($it->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 border-t pt-4 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">Total</span>
                <span class="font-semibold">Rp {{ number_format($transaction->total) }}</span>
            </div>
            <div class="flex justify-between mt-1">
                <span class="text-slate-500">Dibayar</span>
                <span class="font-semibold">Rp {{ number_format($transaction->paid) }}</span>
            </div>

            @if(($transaction->status ?? 'OK') === 'OK')
                <div class="flex justify-between mt-1">
                    <span class="text-slate-500">Kembalian</span>
                    <span class="font-semibold">Rp {{ number_format($transaction->change ?? 0) }}</span>
                </div>
            @else
                <div class="flex justify-between mt-1">
                    <span class="text-slate-500">Kurang</span>
                    <span class="font-semibold text-red-600">Rp {{ number_format($transaction->lack ?? 0) }}</span>
                </div>
            @endif
        </div>
    </div>

</div>

<script>
function cetakStrukDetail() {
    const isOk = '{{ ($transaction->status ?? "OK") === "OK" ? "1" : "0" }}' === '1';
    let rows = '';
    @foreach($transaction->items as $it)
    rows += '<tr><td style="padding:2px 6px;">{{ $it->kode }}</td>'
          + '<td style="padding:2px 6px;">{{ addslashes($it->nama) }}</td>'
          + '<td style="padding:2px 6px;text-align:center;">{{ $it->qty }}</td>'
          + '<td style="padding:2px 6px;text-align:right;">Rp {{ number_format($it->harga) }}</td>'
          + '<td style="padding:2px 6px;text-align:right;">Rp {{ number_format($it->subtotal) }}</td></tr>';
    @endforeach

    const changeLabel = isOk ? 'Kembalian' : 'Kurang';
    const changeVal   = isOk ? 'Rp {{ number_format($transaction->change ?? 0) }}' : 'Rp {{ number_format($transaction->lack ?? 0) }}';

    const html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Struk</title>'
        + '<style>body{font-family:monospace;font-size:12px;max-width:300px;margin:0 auto;padding:16px;}'
        + 'h2,p{text-align:center;margin:2px 0;}hr{border:none;border-top:1px dashed #000;margin:8px 0;}'
        + 'table{width:100%;border-collapse:collapse;font-size:11px;}th{border-bottom:1px solid #000;padding:2px 6px;text-align:left;}'
        + '.row{display:flex;justify-content:space-between;margin:3px 0;}'
        + '@media print{.no-print{display:none;}}</style></head><body>'
        + '<h2>Brangkas Garage</h2><p>Inventori & POS Suku Cadang</p><hr>'
        + '<p>{{ $transaction->created_at->format("d/m/Y H:i") }}</p>'
        + '<p>Invoice: <b>{{ $transaction->invoice ?? ("TRX-".str_pad($transaction->id, 4, "0", STR_PAD_LEFT)) }}</b></p>'
        + '<p>Pembeli: <b>{{ $transaction->buyer_name }}</b></p><hr>'
        + '<table><tr><th>Kode</th><th>Nama</th><th>Qty</th><th>Harga</th><th>Total</th></tr>' + rows + '</table><hr>'
        + '<div class="row"><span>Total</span><span><b>Rp {{ number_format($transaction->total) }}</b></span></div>'
        + '<div class="row"><span>Dibayar</span><span>Rp {{ number_format($transaction->paid) }}</span></div>'
        + '<div class="row"><span><b>' + changeLabel + '</b></span><span><b>' + changeVal + '</b></span></div><hr>'
        + '<p>Terima kasih!</p><br>'
        + '<button class="no-print" onclick="window.print()" style="width:100%;padding:8px;cursor:pointer;font-size:13px;">🖨️ Cetak Sekarang</button>'
        + '</body></html>';

    const blob = new Blob([html], {type: 'text/html'});
    window.open(URL.createObjectURL(blob), '_blank');
}
</script>
@endsection