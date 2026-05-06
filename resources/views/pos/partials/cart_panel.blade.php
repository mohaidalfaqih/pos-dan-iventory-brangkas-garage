{{-- KERANJANG --}}
<div class="bg-white rounded-2xl shadow p-4 transition-opacity duration-200">
   <div class="relative mb-4">

    {{-- JUDUL TENGAH --}}
    <div class="text-center">
        <div class="text-lg font-bold text-slate-800">
            Keranjang
        </div>
        <div class="text-xs text-gray-500">
            Detail transaksi
        </div>
    </div>

    {{-- TOMBOL KOSONGKAN --}}
    <button
        type="button"
        @click="post('{{ route('pos.clear') }}')"
        class="absolute right-0 top-0 text-xs text-red-600 hover:text-red-700"
    >
    ❌   
    </button>

</div>

    <div class="space-y-3 max-h-[52vh] overflow-y-auto pr-1">
        @forelse($cartItems as $it)
        <div class="bg-slate-50 rounded-xl p-2 transition-all duration-200 relative">
            <div class="flex justify-between items-start">
                <div class="flex-1 pr-2">
                    <div class="font-semibold text-sm truncate mt-1"
                         title="{{ $it['nama'] }}">
                        {{ implode(' ', array_slice(explode(' ', $it['nama']), 0, 3)) }}{{ count(explode(' ', $it['nama'])) > 3 ? '...' : '' }}
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 mt-1">
                    <div class="text-xs text-gray-400">{{ $it['kode'] }}</div>
                    <button
                        type="button"
                        @click="post('{{ route('pos.remove') }}', { sparepart_id: {{ $it['id'] }} })"
                    >✕</button>
                </div>
            </div>

            <div class="flex justify-between items-center mt-2">
                <div class="flex gap-1 items-center">
                    <button
                        type="button"
                        @click="post('{{ route('pos.dec') }}', { sparepart_id: {{ $it['id'] }} })"
                        class="w-6 h-6 bg-white border rounded text-xs flex items-center justify-center"
                    >-</button>

                    <div class="w-6 h-6 bg-white border rounded text-xs flex items-center justify-center">{{ $it['qty'] }}</div>

                    <button
                        type="button"
                        @click="post('{{ route('pos.inc') }}', { sparepart_id: {{ $it['id'] }} })"
                        class="w-6 h-6 bg-white border rounded text-xs flex items-center justify-center"
                        {{ $it['qty'] >= $it['stok'] ? 'disabled' : '' }}
                    >+</button>
                </div>

                <div class="text-right">
                    <div class="text-xs text-gray-400">Rp {{ number_format($it['price']) }}/pcs</div>
                    <div class="font-semibold text-sm">Rp {{ number_format($it['subtotal']) }}</div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-sm text-gray-500">Keranjang kosong.</div>
        @endforelse
    </div>

    <div class="border-t mt-4 pt-3 text-sm">
        <div class="flex justify-between">
            <span>Subtotal</span>
            <span id="cart-subtotal" data-subtotal="{{ $subtotalAll }}" class="font-semibold">
                Rp {{ number_format($subtotalAll) }}
            </span>
        </div>
    </div>

    <div class="mt-4">
        <button
            type="button"
            onclick="window.dispatchEvent(new CustomEvent('open-payment'))"
            class="w-full py-2 bg-slate-900 text-white rounded text-sm hover:bg-slate-800 disabled:opacity-50"
            {{ empty($cartItems) ? 'disabled' : '' }}
        >
            Proses Transaksi
        </button>
    </div>
</div>