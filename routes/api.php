<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WasteApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/requests', [WasteApiController::class, 'getRequests']);
Route::get('/driver-locations', [WasteApiController::class, 'getDriverLocations']);
Route::get('/bin-status', [WasteApiController::class, 'getBinStatus']);
Route::post('/create-request', [WasteApiController::class, 'createRequest']);

/*
|--------------------------------------------------------------------------
| DevOps: Health & Metrics Endpoints (Read-Only)
|--------------------------------------------------------------------------
|
| These endpoints are used by Prometheus to scrape application metrics.
| They are completely read-only and do NOT modify any data.
|
| Prometheus scrapes: GET /api/devops/metrics every 30 seconds
| Health check:       GET /api/devops/health
|
*/
Route::prefix('devops')->group(function () {

    // Health check — returns application status
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'app' => config('app.name'),
            'environment' => config('app.env'),
            'timestamp' => now()->toISOString(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ]);
    });

    // Prometheus metrics endpoint — returns metrics in Prometheus text format
    Route::get('/metrics', function () {
        $metrics = [];

        // Application info
        $metrics[] = '# HELP laravel_app_info Application information';
        $metrics[] = '# TYPE laravel_app_info gauge';
        $metrics[] = 'laravel_app_info{app="waste-management",env="' . config('app.env') . '",php_version="' . PHP_VERSION . '"} 1';

        // Application uptime (seconds since boot)
        $metrics[] = '# HELP laravel_uptime_seconds Application uptime in seconds';
        $metrics[] = '# TYPE laravel_uptime_seconds gauge';
        $uptime = defined('LARAVEL_START') ? microtime(true) - LARAVEL_START : 0;
        $metrics[] = 'laravel_uptime_seconds ' . round($uptime, 2);

        // Database status
        $metrics[] = '# HELP laravel_database_up Database connection status';
        $metrics[] = '# TYPE laravel_database_up gauge';
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $metrics[] = 'laravel_database_up 1';
        } catch (\Exception $e) {
            $metrics[] = 'laravel_database_up 0';
        }

        // Cache status
        $metrics[] = '# HELP laravel_cache_up Cache connection status';
        $metrics[] = '# TYPE laravel_cache_up gauge';
        try {
            \Illuminate\Support\Facades\Cache::store()->put('devops_health_check', true, 10);
            $metrics[] = 'laravel_cache_up 1';
        } catch (\Exception $e) {
            $metrics[] = 'laravel_cache_up 0';
        }

        // Storage disk usage (bytes)
        $metrics[] = '# HELP laravel_storage_free_bytes Free disk space in storage directory';
        $metrics[] = '# TYPE laravel_storage_free_bytes gauge';
        $freeSpace = disk_free_space(storage_path());
        $metrics[] = 'laravel_storage_free_bytes ' . ($freeSpace ?: 0);

        // Log file size
        $metrics[] = '# HELP laravel_log_file_bytes Current log file size in bytes';
        $metrics[] = '# TYPE laravel_log_file_bytes gauge';
        $logPath = storage_path('logs/laravel.log');
        $logSize = file_exists($logPath) ? filesize($logPath) : 0;
        $metrics[] = 'laravel_log_file_bytes ' . $logSize;

        return response(implode("\n", $metrics) . "\n", 200)
            ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
    });
});

