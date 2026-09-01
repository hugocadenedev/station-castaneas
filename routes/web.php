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

    $availablePaloxes = Palox::query()
        ->whereIn('availability_status', ['available', 'partial'])
        ->with(['reception.fruit', 'calibration.caliber'])
        ->get();

    $soldPaloxes = Palox::query()
        ->whereHas('orders')
        ->with(['reception.fruit', 'calibration.caliber', 'orders'])
        ->get();

    $buildFruitBreakdown = function ($paloxes, $weightResolver) {
        return $paloxes
            ->groupBy(fn ($palox) => $palox->reception->fruit->name)
            ->map(function ($group) use ($weightResolver) {
                $byCaliber = $group
                    ->groupBy(fn ($palox) => $palox->calibration?->caliber?->name ?? 'Sans calibre')
                    ->map(fn ($caliberGroup) => $caliberGroup->sum($weightResolver))
                    ->sortKeys();

                return [
                    'total' => $byCaliber->sum(),
                    'calibers' => $byCaliber,
                ];
            })
            ->sortByDesc(fn ($fruit) => $fruit['total']);
    };

    $stockByFruit = $buildFruitBreakdown($availablePaloxes, fn ($palox) => (float) $palox->remaining_net_weight_kg);
    $soldByFruit = $buildFruitBreakdown($soldPaloxes, fn ($palox) => (float) $palox->orders->sum('pivot.picked_net_weight_kg'));

    return view('dashboard', [
        'stockByFruit' => $stockByFruit,
        'soldByFruit' => $soldByFruit,
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
    Route::get('/receptions/{reception}/edit', [ReceptionController::class, 'edit'])->name('receptions.edit');
    Route::patch('/receptions/{reception}', [ReceptionController::class, 'update'])->name('receptions.update');
    Route::delete('/receptions/{reception}', [ReceptionController::class, 'destroy'])->name('receptions.destroy');
    Route::get('/receptions/{reception}/label', [ReceptionController::class, 'label'])->name('receptions.label');

    Route::get('/calibrages', [CalibrationController::class, 'index'])->name('calibrages.index');
    Route::get('/calibrages/create', [CalibrationController::class, 'create'])->name('calibrages.create');
    Route::get('/calibrages/{reception}', [CalibrationController::class, 'show'])->name('calibrages.show');
    Route::post('/calibrages', [CalibrationController::class, 'store'])->name('calibrages.store');
    Route::delete('/receptions/{reception}/calibrage/retirer-dernier', [CalibrationController::class, 'destroyLastPalox'])->name('calibrages.destroy-last-palox');
    Route::post('/receptions/{reception}/calibrage/finaliser', [CalibrationController::class, 'finalize'])->name('calibrages.finalize');
    Route::get('/calibrages/paloxes/{palox}/edit', [CalibrationController::class, 'editPalox'])->name('calibrages.paloxes.edit');
    Route::patch('/calibrages/paloxes/{palox}', [CalibrationController::class, 'updatePalox'])->name('calibrages.paloxes.update');
    Route::delete('/calibrages/paloxes/{palox}', [CalibrationController::class, 'destroyPalox'])->name('calibrages.paloxes.destroy');
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
        Route::patch('/backoffice/customers/{customer}', [BackofficeController::class, 'updateCustomer'])->name('backoffice.customers.update');
        Route::delete('/backoffice/customers/{customer}', [BackofficeController::class, 'destroyCustomer'])->name('backoffice.customers.destroy');
        Route::post('/backoffice/fruits', [BackofficeController::class, 'storeFruit'])->name('backoffice.fruits.store');
        Route::patch('/backoffice/fruits/{fruit}', [BackofficeController::class, 'updateFruit'])->name('backoffice.fruits.update');
        Route::delete('/backoffice/fruits/{fruit}', [BackofficeController::class, 'destroyFruit'])->name('backoffice.fruits.destroy');
        Route::post('/backoffice/varieties', [BackofficeController::class, 'storeVariety'])->name('backoffice.varieties.store');
        Route::patch('/backoffice/varieties/{variety}', [BackofficeController::class, 'updateVariety'])->name('backoffice.varieties.update');
        Route::delete('/backoffice/varieties/{variety}', [BackofficeController::class, 'destroyVariety'])->name('backoffice.varieties.destroy');
        Route::post('/backoffice/suppliers', [BackofficeController::class, 'storeSupplier'])->name('backoffice.suppliers.store');
        Route::patch('/backoffice/suppliers/{supplier}', [BackofficeController::class, 'updateSupplier'])->name('backoffice.suppliers.update');
        Route::delete('/backoffice/suppliers/{supplier}', [BackofficeController::class, 'destroySupplier'])->name('backoffice.suppliers.destroy');
        Route::post('/backoffice/calibers', [BackofficeController::class, 'storeCaliber'])->name('backoffice.calibers.store');
        Route::patch('/backoffice/calibers/{caliber}', [BackofficeController::class, 'updateCaliber'])->name('backoffice.calibers.update');
        Route::delete('/backoffice/calibers/{caliber}', [BackofficeController::class, 'destroyCaliber'])->name('backoffice.calibers.destroy');
        Route::post('/backoffice/tare-types', [BackofficeController::class, 'storeTareType'])->name('backoffice.tare-types.store');
        Route::patch('/backoffice/tare-types/{tareType}', [BackofficeController::class, 'updateTareType'])->name('backoffice.tare-types.update');
        Route::delete('/backoffice/tare-types/{tareType}', [BackofficeController::class, 'destroyTareType'])->name('backoffice.tare-types.destroy');
        Route::post('/backoffice/users', [BackofficeController::class, 'storeUser'])->name('backoffice.users.store');
        Route::patch('/backoffice/users/{user}', [BackofficeController::class, 'updateUser'])->name('backoffice.users.update');
        Route::delete('/backoffice/users/{user}', [BackofficeController::class, 'destroyUser'])->name('backoffice.users.destroy');
        Route::patch('/stock/{palox}/reservation', [StockController::class, 'updateReservation'])->name('stock.reservation.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
