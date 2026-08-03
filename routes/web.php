<?php

use App\Http\Controllers\BackofficeController;
use App\Http\Controllers\CalibrationController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\StockController;
use App\Models\Calibration;
use App\Models\CustomerOrder;
use App\Models\Palox;
use App\Models\Reception;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    $ordersToday = CustomerOrder::query()
        ->whereDate('ordered_at', today())
        ->with('paloxes')
        ->get();

    return view('dashboard', [
        'stats' => [
            'receptions_today' => Reception::query()->whereDate('received_at', today())->count(),
            'pending_receptions' => Reception::query()->where('processing_status', 'pending')->count(),
            'calibrations_today' => Calibration::query()->whereDate('calibrated_at', today())->count(),
            'palox_in_stock' => Palox::query()->whereIn('availability_status', ['available', 'partial'])->count(),
            'stock_weight_kg' => (float) Palox::query()->whereIn('availability_status', ['available', 'partial'])->sum('remaining_net_weight_kg'),
            'orders_today' => $ordersToday->count(),
            'picked_today_kg' => (float) $ordersToday->sum(fn ($order) => $order->paloxes->sum(fn ($palox) => (float) $palox->pivot->picked_net_weight_kg)),
            'non_conforming_receptions' => Reception::query()->where('conformity_status', 'non_conforming')->count(),
        ],
    ]);
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/receptions', [ReceptionController::class, 'index'])->name('receptions.index');
    Route::get('/receptions/create', [ReceptionController::class, 'create'])->name('receptions.create');
    Route::post('/receptions', [ReceptionController::class, 'store'])->name('receptions.store');
    Route::get('/receptions/{reception}/label', [ReceptionController::class, 'label'])->name('receptions.label');

    Route::get('/calibrages', [CalibrationController::class, 'index'])->name('calibrages.index');
    Route::get('/calibrages/create', [CalibrationController::class, 'create'])->name('calibrages.create');
    Route::post('/calibrages', [CalibrationController::class, 'store'])->name('calibrages.store');
    Route::get('/paloxes/{palox}/label', [CalibrationController::class, 'label'])->name('paloxes.label');

    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/stock/{palox}', [StockController::class, 'show'])->name('stock.show');

    Route::get('/commandes', [CustomerOrderController::class, 'index'])->name('commandes.index');
    Route::get('/commandes/create', [CustomerOrderController::class, 'create'])->name('commandes.create');
    Route::post('/commandes', [CustomerOrderController::class, 'store'])->name('commandes.store');
    Route::get('/commandes/{commande}', [CustomerOrderController::class, 'show'])->name('commandes.show');
    Route::get('/commandes/{commande}/edit', [CustomerOrderController::class, 'edit'])->name('commandes.edit');
    Route::patch('/commandes/{commande}', [CustomerOrderController::class, 'update'])->name('commandes.update');

    Route::middleware('superadmin')->group(function () {
        Route::get('/backoffice', [BackofficeController::class, 'index'])->name('backoffice.index');
        Route::post('/backoffice/customers', [BackofficeController::class, 'storeCustomer'])->name('backoffice.customers.store');
        Route::post('/backoffice/fruits', [BackofficeController::class, 'storeFruit'])->name('backoffice.fruits.store');
        Route::post('/backoffice/varieties', [BackofficeController::class, 'storeVariety'])->name('backoffice.varieties.store');
        Route::post('/backoffice/suppliers', [BackofficeController::class, 'storeSupplier'])->name('backoffice.suppliers.store');
        Route::post('/backoffice/calibers', [BackofficeController::class, 'storeCaliber'])->name('backoffice.calibers.store');
        Route::post('/backoffice/users', [BackofficeController::class, 'storeUser'])->name('backoffice.users.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
