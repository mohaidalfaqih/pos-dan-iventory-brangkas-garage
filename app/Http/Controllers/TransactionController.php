<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $query = Transaction::query()
            ->withCount('items')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('buyer_name', 'like', "%{$q}%")
                        ->orWhere('invoice', 'like', "%{$q}%")
                        ->orWhere('id', $q);
                });
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            });

        $totalRevenue = (clone $query)->sum('total');

        $transactions = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('transactions.index', compact(
            'transactions',
            'q',
            'dateFrom',
            'dateTo',
            'totalRevenue'
        ));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('items');

        return view('transactions.show', compact('transaction'));
    }

    public function exportCsv(Request $request)
    {
        $q        = $request->query('q');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $transactions = Transaction::query()
            ->with('items.sparepart')
            ->when($q, fn($q2) => $q2->where(function ($sub) use ($q) {
                $sub->where('buyer_name', 'like', "%{$q}%")
                    ->orWhere('invoice', 'like', "%{$q}%");
            }))
            ->when($dateFrom, fn($q2) => $q2->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q2) => $q2->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->get();

        $filename = 'transaksi_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            // Header
            fputcsv($handle, [
                'No', 'Tanggal', 'Invoice', 'Pembeli', 'Status',
                'Kode', 'Nama Barang', 'Qty', 'Harga Satuan (Rp)', 'Subtotal (Rp)',
                'Total Transaksi (Rp)', 'Dibayar (Rp)', 'Kembalian (Rp)'
            ], ';');

            $no = 1;
            foreach ($transactions as $trx) {
                $items = $trx->items;
                $change = max(0, ($trx->paid ?? 0) - ($trx->total ?? 0));

                if ($items->isEmpty()) {
                    fputcsv($handle, [
                        $no++,
                        "\t" . $trx->created_at->format('d-m-Y H:i'),
                        $trx->invoice,
                        $trx->buyer_name,
                        $trx->status ?? 'OK',
                        '-', '-', 0, 0, 0,
                        $trx->total ?? 0,
                        $trx->paid ?? 0,
                        $change,
                    ], ';');
                } else {
                    foreach ($items as $i => $item) {
                        fputcsv($handle, [
                            $i === 0 ? $no++ : '',
                            $i === 0 ? "\t" . $trx->created_at->format('d-m-Y H:i') : '',
                            $i === 0 ? $trx->invoice : '',
                            $i === 0 ? $trx->buyer_name : '',
                            $i === 0 ? ($trx->status ?? 'OK') : '',
                    $item->sparepart->kode ? "\t" . $item->sparepart->kode : '-',
                            $item->sparepart->nama_barang ?? '-',
                            (int) $item->qty,
                            (int) $item->price,
                            (int) ($item->qty * $item->price),
                            $i === 0 ? (int) ($trx->total ?? 0) : '',
                            $i === 0 ? (int) ($trx->paid ?? 0) : '',
                            $i === 0 ? (int) $change : '',
                        ], ';');
                    }
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
