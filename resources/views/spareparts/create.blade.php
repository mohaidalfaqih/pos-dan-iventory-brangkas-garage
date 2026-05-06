@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800">Tambah Sparepart</h1>
        <p class="text-slate-500 text-sm">Masukkan data sparepart baru.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-[28px] shadow-sm p-6">
        <form action="{{ route('spareparts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Kode</label>
                    <input name="kode" value="{{ old('kode') }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="001" required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Nama Barang</label>
                    <input name="nama_barang" value="{{ old('nama_barang') }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Nama Barang" required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Stok</label>
                    <input type="number" name="stok" value="{{ old('stok',0) }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Harga Beli</label>
                    <input type="number" name="harga_beli" value="{{ old('harga_beli',0) }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Harga Jual</label>
                    <input type="number" name="harga_jual" value="{{ old('harga_jual',0) }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Foto (opsional)</label>
                <input type="file" name="foto" id="foto"
                       class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <div class="mt-3">
                    <img id="preview" class="hidden h-28 w-28 rounded-2xl object-cover border border-slate-200" />
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('spareparts.index') }}"
                   class="px-4 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 transition font-semibold">
                    Kembali
                </a>
                <button class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition">
                 Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('foto')?.addEventListener('change', function(e){
    const file = e.target.files?.[0];
    const img = document.getElementById('preview');
    if(!file){ img.classList.add('hidden'); return; }
    img.src = URL.createObjectURL(file);
    img.classList.remove('hidden');
});
</script>
@endsection