@extends('layouts.app')

@section('content')
<div class="max-w-3xl">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-2xl mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-[28px] shadow-sm p-6" id="receiptArea">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xl font-bold text-slate-800">Brangkas Garage</div>
                <div class="text-sm text-slate-500">Struk Penjualan</div>
            </div>
            <div class="text-right text-sm text-slate-600">
                <div class="font-semibold">{{ $transaction->invoice }}</div>
                <div>{{ $transaction->sold_at->format('d-m-Y H:i') }}</div>
            </div>
        </div>

        <div class="mt-4 text-sm text-slate-700">
            <div>Kasir: <span class="font-semibold">{{ $transaction->user->name ?? '-' }}</span></div>
            <div>Pembeli: <span class="font-semibold">{{ $transaction->buyer_name ?: '-' }}</span></div>
        </div>

        <div class="mt-5 border-t pt-4">
            <table class="w-full text-sm">
                <thead class="text-slate-500">
                    <tr>
                        <th class="text-left py-2">Barang</th>
                        <th class="text-right py-2">Qty</th>
                        <th class="text-right py-2">Harga</th>
                        <th class="text-right py-2">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($transaction->items as $it)
                        <tr>
                            <td class="py-2">
                                <div class="font-semibold text-slate-800">{{ $it->sparepart->nama_barang ?? '-' }}</div>
                                <div class="text-xs text-slate-500">{{ $it->sparepart->kode ?? '-' }}</div>
                            </td>
                            <td class="py-2 text-right">{{ $it->qty }}</td>
                            <td class="py-2 text-right">Rp {{ number_format($it->price) }}</td>
                            <td class="py-2 text-right font-semibold">Rp {{ number_format($it->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-5 border-t pt-4 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-600">Total</span>
                <span class="font-bold text-slate-800">Rp {{ number_format($transaction->total) }}</span>
            </div>
            <div class="flex justify-between mt-2">
                <span class="text-slate-600">Dibayar</span>
                <span class="font-semibold">Rp {{ number_format($transaction->paid) }}</span>
            </div>

            @if($transaction->change_amount >= 0)
                <div class="flex justify-between mt-2">
                    <span class="text-slate-600">Kembalian</span>
                    <span class="font-bold text-green-700">Rp {{ number_format($transaction->change_amount) }}</span>
                </div>
            @else
                <div class="flex justify-between mt-2">
                    <span class="text-slate-600">Kurang</span>
                    <span class="font-bold text-red-700">Rp {{ number_format(abs($transaction->change_amount)) }}</span>
                </div>
            @endif
        </div>

        <div class="mt-6 text-center text-xs text-slate-500">
            Terima kasih sudah berbelanja 🙏
        </div>
    </div>

    <div class="mt-4 flex gap-3">
        <a href="{{ route('pos.index') }}"
           class="px-4 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 transition font-semibold">
            Kembali ke POS
        </a>
        <button onclick="window.print()"
                class="px-4 py-3 rounded-2xl bg-slate-900 text-white hover:bg-slate-800 transition font-semibold">
            Cetak
        </button>
    </div>
</div>

<style>
@media print {
    body { background: white !important; }
    header, aside, .no-print { display: none !important; }
    #receiptArea { box-shadow: none !important; border: 0 !important; }
}
</style>
@endsection