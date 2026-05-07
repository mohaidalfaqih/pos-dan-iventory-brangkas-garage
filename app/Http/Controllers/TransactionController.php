<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $q        = $request->query('q');
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
            ->when($dateFrom, fn($q2) => $q2->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q2) => $q2->whereDate('created_at', '<=', $dateTo));

        $totalRevenue = (clone $query)->sum('total');

        $transactions = $query->latest()->paginate(12)->withQueryString();

        return view('transactions.index', compact(
            'transactions', 'q', 'dateFrom', 'dateTo', 'totalRevenue'
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

        // Format angka Rupiah sebagai teks agar tidak jadi scientific notation di Excel
        $rp = fn(int $n): string => number_format($n, 0, ',', '.');

        $filename = 'transaksi_' . now()->format('Ymd_His') . '.csv';

        $output = fopen('php://temp', 'r+');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

        // Baris header
        fputcsv($output, [
            'No', 'Tanggal', 'Invoice', 'Pembeli', 'Status',
            'Kode', 'Nama Barang', 'Qty', 'Harga Satuan (Rp)', 'Subtotal (Rp)',
            'Total Transaksi (Rp)', 'Dibayar (Rp)', 'Kembalian (Rp)',
        ], ';');

        $no          = 1;
        $grandTotal  = 0;
        $grandPaid   = 0;
        $grandChange = 0;

        foreach ($transactions as $trx) {
            $items   = $trx->items;
            $change  = max(0, ($trx->paid ?? 0) - ($trx->total ?? 0));
            $invoice = $trx->invoice ?? ('TRX-' . str_pad($trx->id, 4, '0', STR_PAD_LEFT));

            $grandTotal  += (int) ($trx->total ?? 0);
            $grandPaid   += (int) ($trx->paid  ?? 0);
            $grandChange += (int) $change;

            if ($items->isEmpty()) {
                fputcsv($output, [
                    $no++,
                    "\t" . $trx->created_at->format('d-m-Y H:i'),
                    $invoice,
                    $trx->buyer_name,
                    $trx->status ?? 'OK',
                    '-', '-', 0, 0, 0,
                    $rp((int) ($trx->total ?? 0)),
                    $rp((int) ($trx->paid  ?? 0)),
                    $rp((int) $change),
                ], ';');
            } else {
                foreach ($items as $i => $item) {
                    $kode     = ($item->sparepart && $item->sparepart->kode)
                                    ? "\t" . $item->sparepart->kode
                                    : ($item->kode ? "\t" . $item->kode : '-');
                    $nama     = $item->sparepart->nama_barang ?? $item->nama ?? '-';
                    $harga    = (int) $item->price;
                    $subtotal = (int) ($item->qty * $item->price);

                    fputcsv($output, [
                        $i === 0 ? $no++ : '',
                        $i === 0 ? "\t" . $trx->created_at->format('d-m-Y H:i') : '',
                        $i === 0 ? $invoice : '',
                        $i === 0 ? $trx->buyer_name : '',
                        $i === 0 ? ($trx->status ?? 'OK') : '',
                        $kode,
                        $nama,
                        (int) $item->qty,
                        $rp($harga),
                        $rp($subtotal),
                        $i === 0 ? $rp((int) ($trx->total ?? 0)) : '',
                        $i === 0 ? $rp((int) ($trx->paid  ?? 0)) : '',
                        $i === 0 ? $rp((int) $change) : '',
                    ], ';');
                }
            }
        }

        // Baris kosong pemisah
        fputcsv($output, [], ';');

        // Baris TOTAL — sejajar dengan kolom Total Transaksi (Rp)
        fputcsv($output, [
            '', '', '', '', '', '', '', '', '',
            'TOTAL KESELURUHAN',
            $rp($grandTotal),
            $rp($grandPaid),
            $rp($grandChange),
        ], ';');

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return response($csvContent, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }
}
