@extends('layouts.app')

@section('content')
<div class="space-y-4">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div></div>

        <a href="{{ route('spareparts.create') }}"
           class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm hover:bg-slate-800 transition">
            + Tambah Sparepart
        </a>
    </div>

    {{-- NOTIF --}}
    @if(session('success'))
        <div class="bg-green-100 text-green-700 px-4 py-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- SEARCH + FILTER --}}
    @php
        $stock = $stock ?? request()->query('stock');
        $q = $q ?? request()->query('q');
    @endphp

    <div class="bg-white rounded-2xl shadow p-4 space-y-3">
        <form method="GET" class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
            <div class="flex-1">
                <div class="bg-slate-50 rounded-xl border px-4 py-2 flex items-center gap-2">
                    <span class="text-gray-400">🔍︎</span>
                    <input name="q" value="{{ $q ?? '' }}"
                        placeholder="Cari kode / nama barang..."
                        class="w-full border-0 focus:ring-0 outline-none text-sm bg-transparent">
                </div>
            </div>

            <div class="flex gap-2 flex-wrap">
                <button class="px-4 py-2 rounded-xl bg-slate-900 text-white text-sm hover:bg-slate-800 transition">
                    Cari
                </button>

                <a href="{{ route('spareparts.index', array_filter(['q'=>$q])) }}"
                   class="px-3 py-2 rounded-xl text-sm {{ empty($stock) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">
                    Semua
                </a>

                <a href="{{ route('spareparts.index', array_filter(['q'=>$q,'stock'=>'low'])) }}"
                   class="px-3 py-2 rounded-xl text-sm {{ ($stock==='low') ? 'bg-yellow-500 text-white' : 'bg-yellow-100 text-yellow-900' }}">
                    Stok Menipis
                </a>

                <a href="{{ route('spareparts.index', array_filter(['q'=>$q,'stock'=>'out'])) }}"
                   class="px-3 py-2 rounded-xl text-sm {{ ($stock==='out') ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700' }}">
                    Stok Habis
                </a>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left">Foto</th>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-center">Stok</th>
                        <th class="px-4 py-3 text-right">Harga Beli</th>
                        <th class="px-4 py-3 text-right">Harga Jual</th>
                        <th class="px-4 py-3 text-right">Tambah Stok</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                @forelse($spareparts as $sp)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-4 py-3">
                            <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-50 border-2 border-slate-100 flex items-center justify-center hover:border-blue-200 transition-colors cursor-pointer"
                                 onclick="showImageModal('{{ $sp->foto ? asset('storage/'.$sp->foto) : '' }}', '{{ e($sp->nama_barang) }}')">
                                @if($sp->foto)
                                    <img src="{{ asset('storage/'.$sp->foto) }}"
                                         alt="{{ $sp->nama_barang }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono text-xs font-semibold">
                                {{ $sp->kode }}
                            </span>
                        </td>

                        <td class="px-4 py-3" style="max-width:240px;">
                            <div class="truncate cursor-pointer hover:text-blue-600 transition-colors font-medium"
                                 title="{{ $sp->nama_barang }}"
                                 onclick="this.classList.toggle('truncate'); this.closest('td').style.maxWidth = this.classList.contains('truncate') ? '240px' : 'none';">
                                {{ $sp->nama_barang }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($sp->stok <= 0)
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                                        <circle cx="8" cy="8" r="8"/>
                                    </svg>
                                    HABIS
                                </span>
                            @elseif($sp->stok <= 3)
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    {{ $sp->stok }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                                    </svg>
                                    {{ $sp->stok }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right text-slate-600">
                            Rp {{ number_format($sp->harga_beli ?? 0) }}
                        </td>

                        <td class="px-4 py-3 text-right font-semibold text-slate-800">
                            Rp {{ number_format($sp->harga_jual ?? 0) }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition"
                                style="background:#0d2d52;"
                                onclick="openStockModal({{ $sp->id }}, '{{ e($sp->nama_barang) }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                Stok
                            </button>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex gap-1.5">
                                <a href="{{ route('spareparts.edit', $sp->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-white text-xs font-semibold hover:opacity-90 transition"
                                   style="background:#184E77;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>

                                <form method="POST" action="{{ route('spareparts.destroy', $sp->id) }}"
                                      onsubmit="return handleDelete(this)">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                            Data sparepart belum ada.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $spareparts->links() }}
        </div>
    </div>
</div>

{{-- MODAL PREVIEW FOTO --}}
<div id="imageModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeImageModal()"></div>
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl overflow-hidden max-w-sm w-[90%]">
        <div class="relative">
            <img id="imageModalImg" src="" alt="" class="w-full object-contain max-h-80">
            <button onclick="closeImageModal()"
                    class="absolute top-2 right-2 w-7 h-7 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center text-xs">✕</button>
        </div>
        <div id="imageModalName" class="px-4 py-3 text-sm font-medium text-slate-700 text-center border-t"></div>
    </div>
</div>

{{-- MODAL TAMBAH STOK --}}
<div id="stockModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" onclick="closeStockModal()"></div>

    <div class="absolute left-1/2 top-1/2 w-[92%] max-w-md -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow p-5">
        <div class="flex items-start justify-between">
            <div class="w-full">
                <div class="font-bold text-slate-800 text-lg text-center">Tambah Stok</div>
                <div id="stockModalName" class="text-sm text-slate-500 mt-1 text-center"></div>
            </div>
            <button class="text-slate-500 hover:text-slate-800" onclick="closeStockModal()">✕</button>
        </div>

        <form method="POST" id="stockForm" class="mt-4 space-y-3">
            @csrf

            <div>
                <label class="text-xs text-slate-600">Jumlah Stok Masuk</label>
                <input type="number" name="qty" min="1" required
                       class="w-full mt-1 border rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-slate-200">
            </div>

            <button class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-sm hover:bg-slate-800 transition">
                Simpan
            </button>
        </form>
    </div>
</div>

<script>
    function showImageModal(foto, nama) {
        if (!foto) return;
        document.getElementById('imageModalImg').src = foto;
        document.getElementById('imageModalImg').alt = nama;
        document.getElementById('imageModalName').textContent = nama;
        document.getElementById('imageModal').classList.remove('hidden');
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }

    function handleDelete(form) {
        if (!confirm('Yakin ingin menghapus sparepart ini?')) {
            return false;
        }

        const btn = form.querySelector('button');
        btn.disabled = true;
        btn.innerText = 'Menghapus...';

        return true;
    }

    function openStockModal(id, nama) {
        const modal = document.getElementById('stockModal');
        const nameEl = document.getElementById('stockModalName');
        const form = document.getElementById('stockForm');

        nameEl.textContent = nama;
        form.action = "{{ url('spareparts') }}/" + id + "/add-stock";

        modal.classList.remove('hidden');
    }

    function closeStockModal() {
        document.getElementById('stockModal').classList.add('hidden');
    }
</script>
@endsection