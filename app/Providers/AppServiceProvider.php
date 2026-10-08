<?php

namespace App\Providers;

use App\Models\Transaksi;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share pending validation count with all views for Sidebar badge
        View::composer('*', function ($view) {
            if (!app()->runningInConsole() && \Illuminate\Support\Facades\Schema::hasTable('transaksis')) {
                try {
                    $pendingValidationCount = Transaksi::where('status', 'menunggu')->count();
                    $view->with('pendingValidationCount', $pendingValidationCount);
                } catch (\Throwable $e) {
                    $view->with('pendingValidationCount', 0);
                }
            } else {
                $view->with('pendingValidationCount', 0);
            }
        });
    }
}

