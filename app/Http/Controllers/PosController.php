<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\Sparepart;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $spareparts = Sparepart::query()
            ->when($q, function ($query) use ($q) {
                $query->where('kode', 'like', "%{$q}%")
                    ->orWhere('nama_barang', 'like', "%{$q}%");
            })
            ->orderBy('nama_barang')
            ->paginate(24)
            ->withQueryString();

        [$cartItems, $subtotalAll] = $this->buildCartItems();

        return view('pos.index', compact('spareparts', 'q', 'cartItems', 'subtotalAll'));
    }

    // ✅ STEP: buka modal pembayaran
    public function startPayment()
    {
        $cart = session('pos_cart', []);
        if (count($cart) === 0) {
            return back()->with('error', 'Keranjang masih kosong.');
        }

        // hanya trigger modal pembayaran di halaman pos
        return redirect()->route('pos.index')->with('show_payment', true);
    }

    // ================= CART ACTIONS =================

    public function add(Request $request)
    {
        $data = $request->validate([
            'sparepart_id' => ['required', 'integer', 'exists:spareparts,id'],
        ]);

        $sp = Sparepart::findOrFail($data['sparepart_id']);

        if ((int)$sp->stok <= 0) {
            return $this->respondError('Stok habis.');
        }

        $cart = session('pos_cart', []);

        if (!isset($cart[$sp->id])) {
            $cart[$sp->id] = ['qty' => 1];
        } else {
            $newQty = (int)$cart[$sp->id]['qty'] + 1;
            if ($newQty > (int)$sp->stok) {
                return $this->respondError('Qty melebihi stok.');
            }
            $cart[$sp->id]['qty'] = $newQty;
        }

        session(['pos_cart' => $cart]);

        return $this->respondOk('Ditambahkan.');
    }

    public function inc(Request $request)
    {
        $data = $request->validate([
            'sparepart_id' => ['required', 'integer', 'exists:spareparts,id'],
        ]);

        $sp = Sparepart::findOrFail($data['sparepart_id']);
        $cart = session('pos_cart', []);

        if (!isset($cart[$sp->id])) {
            return $this->respondError('Item tidak ada di keranjang.');
        }

        $qty = (int)$cart[$sp->id]['qty'];
        if ($qty + 1 > (int)$sp->stok) {
            return $this->respondError('Qty melebihi stok.');
        }

        $cart[$sp->id]['qty'] = $qty + 1;
        session(['pos_cart' => $cart]);

        return $this->respondOk('Qty bertambah.');
    }

    public function dec(Request $request)
    {
        $data = $request->validate([
            'sparepart_id' => ['required', 'integer', 'exists:spareparts,id'],
        ]);

        $sp = Sparepart::findOrFail($data['sparepart_id']);
        $cart = session('pos_cart', []);

        if (!isset($cart[$sp->id])) {
            return $this->respondError('Item tidak ada di keranjang.');
        }

        $qty = (int)$cart[$sp->id]['qty'] - 1;

        if ($qty <= 0) {
            unset($cart[$sp->id]);
        } else {
            $cart[$sp->id]['qty'] = $qty;
        }

        session(['pos_cart' => $cart]);

        return $this->respondOk('Qty berkurang.');
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'sparepart_id' => ['required', 'integer', 'exists:spareparts,id'],
        ]);

        $cart = session('pos_cart', []);
        unset($cart[$data['sparepart_id']]);
        session(['pos_cart' => $cart]);

        return $this->respondOk('Item dihapus.');
    }

    public function clear()
    {
        session()->forget('pos_cart');
        return $this->respondOk('Keranjang dikosongkan.');
    }

    // ================= CHECKOUT =================

    public function finish(Request $request)
    {
        $request->validate([
            'buyer_name' => ['required', 'string', 'max:100'],
            'paid' => ['required', 'numeric', 'min:0'],
        ]);

        $cart = session('pos_cart', []);
        if (count($cart) === 0) {
            return back()->with('error', 'Keranjang masih kosong.');
        }

        try {
            $receipt = null;

            DB::transaction(function () use ($request, $cart, &$receipt) {

                $itemsPrepared = [];
                $total = 0;

                foreach ($cart as $sparepartId => $row) {
                    $qty = (int)($row['qty'] ?? 0);
                    if ($qty <= 0) continue;

                    $sp = Sparepart::lockForUpdate()->findOrFail($sparepartId);

                    if ((int)$sp->stok < $qty) {
                        throw new \Exception("Stok {$sp->nama_barang} tidak cukup. Sisa: {$sp->stok}");
                    }

                    $harga = (int)$sp->harga_jual;
                    $subtotal = $harga * $qty;
                    $total += $subtotal;

                    $itemsPrepared[] = [
                        'sp' => $sp,
                        'qty' => $qty,
                        'harga' => $harga,
                        'subtotal' => $subtotal,
                    ];
                }

                $paid = (int)$request->paid;
                $status = ($paid >= $total) ? 'OK' : 'LACK';
                $change = max(0, $paid - $total);
                $lack = max(0, $total - $paid);

                $trx = Transaction::create([
                    'invoice' => $this->makeInvoice(),
                    'buyer_name' => $request->buyer_name,
                    'total' => $total,
                    'paid' => $paid,
                    'status' => $status,
                    'change' => $change,
                    'lack' => $lack,
                    'user_id' => auth()->id(),
                ]);

                foreach ($itemsPrepared as $it) {
                    $sp = $it['sp'];

                    TransactionItem::create([
                        'transaction_id' => $trx->id,
                        'sparepart_id' => $sp->id,
                        'kode' => $sp->kode,
                        'nama' => $sp->nama_barang,
                        'harga' => $it['harga'],
                        'qty' => $it['qty'],
                        'subtotal' => $it['subtotal'],
                    ]);

                    $sp->decrement('stok', $it['qty']);

                    InventoryMovement::create([
                        'sparepart_id' => $sp->id,
                        'type' => 'OUT',
                        'qty' => $it['qty'],
                        'note' => 'Penjualan POS #' . $trx->id . ' - ' . auth()->user()->name,
                        'user_id' => auth()->id(),
                    ]);
                }

                $receiptItems = $trx->items()->get()->map(function ($x) {
                    return [
                        'kode' => $x->kode,
                        'nama' => $x->nama,
                        'qty' => (int)$x->qty,
                        'harga' => (int)$x->harga,
                        'subtotal' => (int)$x->subtotal,
                    ];
                })->toArray();

                $receipt = [
                    'status' => $status,
                    'buyer' => $trx->buyer_name,
                    'time' => now()->format('d/m/Y H:i'),
                    'total' => $total,
                    'paid' => $paid,
                    'change' => $change,
                    'lack' => $lack,
                    'items' => $receiptItems,
                    'trx_id' => $trx->id,
                ];
            });

            session()->forget('pos_cart');

            return redirect()
                ->route('pos.index')
                ->with('show_receipt', true)
                ->with('receipt', $receipt);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function resetReceipt()
    {
        return redirect()->route('pos.index');
    }

    // ================= HELPERS =================

    private function buildCartItems(): array
    {
        $cart = session('pos_cart', []);
        $ids = array_keys($cart);

        $spareparts = Sparepart::whereIn('id', $ids)->get()->keyBy('id');

        $cartItems = [];
        $subtotalAll = 0;

        foreach ($cart as $id => $row) {
            $sp = $spareparts->get($id);
            if (!$sp) continue;

            $qty = (int)($row['qty'] ?? 0);
            if ($qty <= 0) continue;

            if ($qty > (int)$sp->stok) $qty = (int)$sp->stok;

            $price = (int)$sp->harga_jual;
            $subtotal = $price * $qty;
            $subtotalAll += $subtotal;

            $cartItems[] = [
                'id' => $sp->id,
                'kode' => $sp->kode,
                'nama' => $sp->nama_barang,
                'price' => $price,
                'qty' => $qty,
                'stok' => (int)$sp->stok,
                'subtotal' => $subtotal,
            ];
        }

        $newCart = $cart;
        foreach ($cartItems as $it) {
            $newCart[$it['id']]['qty'] = $it['qty'];
        }
        session(['pos_cart' => $newCart]);

        return [$cartItems, $subtotalAll];
    }

    private function respondOk(string $message = 'OK')
    {
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }
        return back()->with('success', $message);
    }

    private function respondError(string $message = 'Error')
    {
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }
        return back()->with('error', $message);
    }

    private function makeInvoice(): string
    {
        $date = now()->format('Ymd');
        $prefix = "INV-{$date}-";

        $last = Transaction::where('invoice', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice');

        $next = 1;
        if ($last) {
            $next = ((int) substr($last, -4)) + 1;
        }

        return $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }
}