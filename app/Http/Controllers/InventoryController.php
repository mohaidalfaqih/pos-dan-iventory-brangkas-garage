<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = InventoryMovement::query()
            ->with(['sparepart', 'user'])
            ->when($q, function ($query) use ($q) {
                $query->whereHas('sparepart', function ($sp) use ($q) {
                    $sp->where('kode', 'like', "%{$q}%")
                        ->orWhere('nama_barang', 'like', "%{$q}%");
                });
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            });

        $totalIn = (clone $query)->where('type', 'IN')->sum('qty');
        $totalOut = (clone $query)->where('type', 'OUT')->sum('qty');

        $movements = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('inventory.index', compact(
            'movements',
            'q',
            'dateFrom',
            'dateTo',
            'totalIn',
            'totalOut'
        ));
    }

    public function exportCsv(Request $request)
    {
        $q        = $request->query('q');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $movements = InventoryMovement::query()
            ->with(['sparepart', 'user'])
            ->when($q, function ($query) use ($q) {
                $query->whereHas('sparepart', function ($sp) use ($q) {
                    $sp->where('kode', 'like', "%{$q}%")
                        ->orWhere('nama_barang', 'like', "%{$q}%");
                });
            })
            ->when($dateFrom, fn($q2) => $q2->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q2) => $q2->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->get();

        $filename = 'inventory_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($movements) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($handle, [
                'No', 'Tanggal', 'Kode', 'Nama Barang', 'Type', 'Qty', 'Catatan', 'User'
            ], ';');

            $no = 1;
            foreach ($movements as $m) {
                fputcsv($handle, [
                    $no++,
                    "\t" . optional($m->created_at)->format('d-m-Y H:i'),
                    $m->sparepart->kode ? "\t" . $m->sparepart->kode : '-',
                    $m->sparepart->nama_barang ?? '-',
                    $m->type,
                    (int) $m->qty,
                    $m->note ?? '-',
                    $m->user->name ?? '-',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}