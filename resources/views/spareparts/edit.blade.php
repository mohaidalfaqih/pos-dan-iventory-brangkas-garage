@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800">Edit Sparepart</h1>
        <p class="text-slate-500 text-sm">Perbarui data sparepart.</p>
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
        <form action="{{ route('spareparts.update', $sparepart->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-slate-700">Kode</label>
                    <input value="{{ $sparepart->kode }}" disabled
                           class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-100">
                    {{-- Hidden input agar kode tetap terkirim saat submit --}}
                    <input type="hidden" name="kode" value="{{ $sparepart->kode }}">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Nama Barang</label>
                    <input name="nama_barang" value="{{ old('nama_barang',$sparepart->nama_barang) }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Stok</label>
                    <input type="number" name="stok" value="{{ old('stok',$sparepart->stok) }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', optional($sparepart->tanggal_masuk)->format('Y-m-d')) }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Harga Beli</label>
                    <input type="number" name="harga_beli" value="{{ old('harga_beli',$sparepart->harga_beli) }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Harga Jual</label>
                    <input type="number" name="harga_jual" value="{{ old('harga_jual',$sparepart->harga_jual) }}"
                           class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>
            </div>

            <div>
                <label class="text-sm font-semibold text-slate-700">Foto (opsional)</label>
                <input type="file" name="foto" id="foto"
                       class="mt-2 w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">

                <div class="mt-3 flex items-center gap-4">
                    @if($sparepart->foto)
                        <img src="{{ asset('storage/'.$sparepart->foto) }}" class="h-20 w-20 rounded-2xl object-cover border border-slate-200" id="preview">
                    @else
                        <img id="preview" class="hidden h-20 w-20 rounded-2xl object-cover border border-slate-200" />
                    @endif
                    <div class="text-xs text-slate-500">Upload foto baru untuk mengganti foto lama.</div>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('spareparts.index') }}"
                   class="px-4 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 transition font-semibold">
                    Kembali
                </a>
                <button class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('foto')?.addEventListener('change', function(e){
    const file = e.target.files?.[0];
    const img = document.getElementById('preview');
    if(!file){ return; }
    img.src = URL.createObjectURL(file);
    img.classList.remove('hidden');
});
</script>
@endsection