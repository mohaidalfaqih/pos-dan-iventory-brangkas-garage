<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = (int) $request->query('year', now()->year);

        // Semua tahun yang tersedia
        $availableYears = Transaction::selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->orderByDesc('year')
            ->pluck('year');

        // Rekap per tahun (tabel ringkasan)
        $yearlyStats = collect();
        foreach ($availableYears as $year) {
            $yearlyStats->push([
                'year'         => $year,
                'revenue'      => (int) Transaction::whereYear('created_at', $year)->sum('total'),
                'transactions' => (int) Transaction::whereYear('created_at', $year)->count(),
            ]);
        }

        // Breakdown per bulan untuk tahun yang dipilih
        $monthlyBreakdown = Transaction::selectRaw(
                'MONTH(created_at) as month,
                 COUNT(*) as transactions,
                 SUM(total) as revenue,
                 SUM(paid) as paid'
            )
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Isi bulan yang kosong
        $months = collect();
        for ($m = 1; $m <= 12; $m++) {
            $data = $monthlyBreakdown->get($m);
            $months->push([
                'month'        => $m,
                'transactions' => $data ? (int) $data->transactions : 0,
                'revenue'      => $data ? (int) $data->revenue      : 0,
                'paid'         => $data ? (int) $data->paid         : 0,
            ]);
        }

        // Data grafik bulanan tahun terpilih
        $chartLabels  = $months->pluck('month');
        $chartRevenue = $months->pluck('revenue');

        return view('rekap.index', compact(
            'availableYears',
            'selectedYear',
            'yearlyStats',
            'months',
            'chartLabels',
            'chartRevenue'
        ));
    }

    public function exportCsv(Request $request)
    {
        $selectedYear = (int) $request->query('year', now()->year);

        $monthNames = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
            5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
            9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];

        $monthlyBreakdown = Transaction::selectRaw(
                'MONTH(created_at) as month,
                 COUNT(*) as transactions,
                 SUM(total) as revenue,
                 SUM(paid) as paid'
            )
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $rp = fn(int $n): string => number_format($n, 0, ',', '.');

        $filename = 'rekap_' . $selectedYear . '.csv';
        $output   = fopen('php://temp', 'r+');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['REKAP OMZET TAHUN ' . $selectedYear], ';');
        fputcsv($output, [], ';');
        fputcsv($output, ['Bulan', 'Jumlah Transaksi', 'Total Omzet (Rp)', 'Total Dibayar (Rp)'], ';');

        $totalTrx     = 0;
        $totalRevenue = 0;
        $totalPaid    = 0;

        for ($m = 1; $m <= 12; $m++) {
            $data = $monthlyBreakdown->get($m);
            $trx  = $data ? (int) $data->transactions : 0;
            $rev  = $data ? (int) $data->revenue      : 0;
            $paid = $data ? (int) $data->paid         : 0;

            $totalTrx     += $trx;
            $totalRevenue += $rev;
            $totalPaid    += $paid;

            fputcsv($output, [
                $monthNames[$m],
                $trx,
                $rp($rev),
                $rp($paid),
            ], ';');
        }

        fputcsv($output, [], ';');
        fputcsv($output, ['TOTAL', $totalTrx, $rp($totalRevenue), $rp($totalPaid)], ';');

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }
}
