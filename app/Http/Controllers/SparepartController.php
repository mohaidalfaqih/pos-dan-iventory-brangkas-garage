<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SparepartController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $stock = $request->query('stock');

        $spareparts = Sparepart::query()
            ->when($q, function ($query) use ($q) {
                $query->where('kode', 'like', "%{$q}%")
                      ->orWhere('nama_barang', 'like', "%{$q}%");
            })
            ->when($stock === 'out', fn($query) => $query->where('stok', '<=', 0))
            ->when($stock === 'low', fn($query) => $query->whereBetween('stok', [1, 3]))
            ->orderBy('kode')
            ->paginate(10)
            ->withQueryString();

        return view('spareparts.index', compact('spareparts', 'q', 'stock'));
    }

    public function create()
    {
        return view('spareparts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => ['required','string','max:50','unique:spareparts,kode'],
            'nama_barang' => ['required','string','max:255'],
            'stok' => ['required','integer','min:0'],
            'harga_beli' => ['nullable','numeric','min:0'],
            'harga_jual' => ['required','numeric','min:0'],
            'foto' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('spareparts', 'public');
        }

        DB::transaction(function () use ($data) {
            $sp = Sparepart::create($data);

            if ((int)$sp->stok > 0) {
                InventoryMovement::create([
                    'sparepart_id' => $sp->id,
                    'type' => 'IN',
                    'qty' => (int)$sp->stok,
                    'note' => 'Stok awal',
                    'user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('spareparts.index')
            ->with('success', 'Sparepart berhasil ditambahkan.');
    }

    public function edit(Sparepart $sparepart)
    {
        return view('spareparts.edit', compact('sparepart'));
    }

    public function update(Request $request, Sparepart $sparepart)
    {
        $data = $request->validate([
            'kode' => ['required','string','max:50','unique:spareparts,kode,'.$sparepart->id],
            'nama_barang' => ['required','string','max:255'],
            'stok' => ['required','integer','min:0'],
            'harga_beli' => ['nullable','numeric','min:0'],
            'harga_jual' => ['required','numeric','min:0'],
            'foto' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            if ($sparepart->foto) {
                Storage::disk('public')->delete($sparepart->foto);
            }
            $data['foto'] = $request->file('foto')->store('spareparts', 'public');
        }

        $sparepart->update($data);

        return redirect()->route('spareparts.index')
            ->with('success', 'Sparepart berhasil diupdate.');
    }

    public function destroy(Sparepart $sparepart)
    {
        DB::transaction(function () use ($sparepart) {

            // 🔥 Hapus foto jika ada
            if ($sparepart->foto) {
                Storage::disk('public')->delete($sparepart->foto);
            }

            // 🔥 Hapus relasi inventory movement
            InventoryMovement::where('sparepart_id', $sparepart->id)->delete();

            // 🔥 Hapus produk (transaction_items otomatis aman karena nullOnDelete)
            $sparepart->delete();
        });

        return redirect()
            ->route('spareparts.index')
            ->with('success', 'Sparepart berhasil dihapus.');
    }

    // ==========================
    // TAMBAH STOK (IN)
    // ==========================
    public function addStock(Request $request, Sparepart $sparepart)
    {
        $data = $request->validate([
            'qty' => ['required','integer','min:1'],
        ]);

        DB::transaction(function () use ($sparepart, $data) {
            $sparepart->increment('stok', (int)$data['qty']);

            InventoryMovement::create([
                'sparepart_id' => $sparepart->id,
                'type'         => 'IN',
                'qty'          => (int)$data['qty'],
                'note'         => 'Restock',
                'reference_id' => $sparepart->id,
                'user_id'      => auth()->id(),
            ]);
        });

        return redirect()->route('spareparts.index')
            ->with('success', 'Stok berhasil ditambahkan.');
    }
}