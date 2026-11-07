<?php

namespace App\Providers;

use App\Models\ProductAlert;
use App\Models\ProductRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }
        // if (!function_exists('schemaHasTable')) {
        //     function schemaHasTable(string $table): bool
        //     {
        //         try {
        //             return Schema::hasTable($table);
        //         } catch (\Exception $e) {
        //             return false;
        //         }
        //     }
        // }
        $unreadAlertsCount = 0;
        $recentAlerts = collect();
        $unreadRequestCount = 0;
        $recentRequest = collect();

        if (Schema::hasTable('product_alerts')) {
            $unreadAlertsCount = ProductAlert::where('is_read', false)->where('user_id', auth()->id())->count();
            $recentAlerts = ProductAlert::with('product')
                ->where('is_read', false)
                ->where('user_id', auth()->id())
                ->latest()
                ->take(5)
                ->get();
        }

        if (Schema::hasTable('product_requests')) {
            $unreadRequestCount = ProductRequest::where('is_read', false)->count();
            $recentRequest = ProductRequest::with('product')
                ->where('is_read', false)
                ->latest()
                ->take(5)
                ->get();
        }

        View::share([
            'unreadAlertsCount' => $unreadAlertsCount,
            'recentAlerts' => $recentAlerts,
            'unreadRequestCount' => $unreadRequestCount,
            'recentRequest' => $recentRequest,
        ]);

        \App\Service\SettingsService::configureMailer();
    }
}
