<?php

namespace App\Providers;

use App\Models\Sparepart;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Badge sidebar — hanya jalankan jika user sudah login
        View::composer('layouts.sidebar', function ($view) {
            if (!auth()->check()) {
                $view->with(['outOfStockCount' => 0, 'lowStockCount' => 0]);
                return;
            }

            $outOfStockCount = Sparepart::where('stok', '<=', 0)->count();
            $lowStockCount   = Sparepart::whereBetween('stok', [1, 3])->count();

            $view->with([
                'outOfStockCount' => $outOfStockCount,
                'lowStockCount'   => $lowStockCount,
            ]);
        });

        // Paksa URL app pakai APP_URL saat akses via tunnel / HTTPS
        if (config('app.url')) {
            URL::forceRootUrl(config('app.url'));
        }

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }
    }
}