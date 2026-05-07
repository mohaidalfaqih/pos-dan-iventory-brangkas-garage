<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Sparepart;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ======================
        // OMZET HARI INI & KEMARIN
        // ======================
        $todayRevenue = (int) Transaction::whereDate('created_at', now())->sum('total');
        $yesterdayRevenue = (int) Transaction::whereDate('created_at', now()->subDay())->sum('total');

        $todayTransactions = (int) Transaction::whereDate('created_at', now())->count();

        // % perubahan (handle pembagi 0)
        if ($yesterdayRevenue > 0) {
            $revenueChangePercent = (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100;
        } else {
            // kalau kemarin 0, kita anggap:
            // - jika hari ini juga 0 => 0%
            // - jika hari ini >0 => 100%
            $revenueChangePercent = ($todayRevenue > 0) ? 100 : 0;
        }

        $revenueChangePercent = round($revenueChangePercent, 1);
        $revenueChangeDirection = $todayRevenue >= $yesterdayRevenue ? 'up' : 'down';

        // ======================
        // STAT BULAN INI
        // ======================
        $monthlyRevenue = (int) Transaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        // ======================
        // TOTAL PRODUK
        // ======================
        $totalProducts = (int) Sparepart::count();

        // ======================
        // STOK MENIPIS + HABIS
        // ======================
        $lowStockLimit = 3;

        $lowStockItems = Sparepart::where('stok', '<=', $lowStockLimit)
            ->where('stok', '>', 0)
            ->orderBy('stok')
            ->limit(5)
            ->get();

        $outOfStock = (int) Sparepart::where('stok', 0)->count();

        $outOfStockItems = Sparepart::where('stok', 0)
            ->orderBy('nama_barang')
            ->limit(5)
            ->get();

        // ======================
        // TOP PRODUK
        // ======================
        $topProducts = TransactionItem::select('nama', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('nama')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // ======================
        // REKAP TAHUNAN
        // ======================
        $yearlyStats = collect();
        $currentYear = now()->year;
        // Ambil tahun-tahun yang ada di database
        $availableYears = Transaction::selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year');

        foreach ($availableYears as $year) {
            $yearRevenue      = (int) Transaction::whereYear('created_at', $year)->sum('total');
            $yearTransactions = (int) Transaction::whereYear('created_at', $year)->count();
            $yearlyStats->push([
                'year'         => $year,
                'revenue'      => $yearRevenue,
                'transactions' => $yearTransactions,
            ]);
        }

        // Grafik perbandingan omzet per tahun (bar)
        $yearlyChart = $yearlyStats->sortBy('year')->values();
        $salesChart = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as total')
            )
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ======================
        // GRAFIK BULANAN (12 bulan - tahun ini)
        // ======================
        $monthlyChart = Transaction::select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total')
            )
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('dashboard', compact(
            'todayRevenue',
            'yesterdayRevenue',
            'revenueChangePercent',
            'revenueChangeDirection',
            'todayTransactions',
            'monthlyRevenue',
            'totalProducts',
            'lowStockLimit',
            'lowStockItems',
            'outOfStock',
            'outOfStockItems',
            'topProducts',
            'salesChart',
            'monthlyChart',
            'yearlyStats',
            'yearlyChart'
        ));
    }
}