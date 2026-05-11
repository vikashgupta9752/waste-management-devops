<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\CitizenController;
use App\Http\Controllers\SmartFeatureController;
use App\Http\Controllers\GamificationController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\SmartCityController;
use App\Http\Controllers\BinController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } elseif ($user->role === 'driver') {
        return redirect()->route('driver.dashboard');
    } else {
        return redirect()->route('citizen.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/smart-dashboard', [SmartCityController::class, 'dashboard'])->name('smart-dashboard');
        Route::get('/requests', [AdminController::class, 'requests'])->name('requests');
        Route::post('/assign', [AdminController::class, 'assign'])->name('assign');
        Route::get('/export-reports', [AdminController::class, 'exportReports'])->name('export-reports');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.update-role');
        Route::get('/complaints', [ComplaintController::class, 'adminIndex'])->name('complaints');
        Route::patch('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
    });

    // Driver Routes
    Route::prefix('driver')->name('driver.')->group(function () {
        Route::get('/dashboard', [DriverController::class, 'index'])->name('dashboard');
        Route::post('/update-status', [DriverController::class, 'updateStatus'])->name('update-status');
        Route::post('/update-location', [DriverController::class, 'updateLocation'])->name('update-location');
        Route::post('/verify-qr', [DriverController::class, 'verifyQr'])->name('verify-qr');
    });

    // Citizen Routes
    Route::prefix('citizen')->name('citizen.')->group(function () {
        Route::get('/dashboard', [CitizenController::class, 'index'])->name('dashboard');
        Route::post('/request', [CitizenController::class, 'storeRequest'])->name('request.store');
        Route::get('/track/{driver_id}', [CitizenController::class, 'trackDriver'])->name('track');
        Route::get('/segregation-guide', [SmartFeatureController::class, 'segregationGuide'])->name('segregation-guide');
        Route::post('/segregation-guide', [SmartFeatureController::class, 'processSegregation'])->name('segregation-guide.process');
        Route::get('/leaderboard', [GamificationController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints');
        Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
        Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
        Route::post('/marketplace', [MarketplaceController::class, 'store'])->name('marketplace.store');
        Route::get('/bins', [BinController::class, 'index'])->name('bins');
        Route::post('/bins/{bin}/update', [BinController::class, 'updateFillLevel'])->name('bins.update');
    });

    // Smart Features API (Global for auth users)
    Route::get('/api/heatmap-data', [SmartCityController::class, 'getHeatmapData'])->name('api.heatmap-data');
    Route::get('/api/analytics-data', [SmartCityController::class, 'getAnalyticsData'])->name('api.analytics-data');
    Route::get('/api/prediction-data', [SmartCityController::class, 'getPredictionData'])->name('api.prediction-data');
    Route::post('/api/simulate', [SmartCityController::class, 'simulate'])->name('api.simulate');
    Route::post('/api/chatbot', [SmartCityController::class, 'chatbot'])->name('api.chatbot');
    Route::get('/api/driver-location/{driver_id}', [TrackingController::class, 'getDriverLocation'])->name('api.driver-location');
    Route::get('/api/drivers', [TrackingController::class, 'getAllDrivers'])->name('api.drivers');
    Route::post('/api/update-location', [TrackingController::class, 'updateLocation'])->name('api.update-location');
});

require __DIR__.'/auth.php';
