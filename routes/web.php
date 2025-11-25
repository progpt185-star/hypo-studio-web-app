<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ClusteringController;
use App\Http\Controllers\OwnerReportController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Customers
    // Import customers (define before resource to avoid route parameter conflict)
    Route::get('customers/import', [App\Http\Controllers\CustomerImportController::class, 'show'])->name('customers.import.form');
    Route::post('customers/import', [App\Http\Controllers\CustomerImportController::class, 'import'])->name('customers.import');
    Route::resource('customers', CustomerController::class);

    // Orders
    // Import orders (define before resource to avoid route parameter conflicts)
    Route::get('orders/import', function(){ return view('orders.import'); })->name('orders.import.form');
    Route::post('orders/import', [OrderController::class, 'import'])->name('orders.import');
    Route::resource('orders', OrderController::class);

    // Clustering
    Route::get('clustering', [ClusteringController::class, 'index'])->name('clustering.index');
    Route::post('clustering/analyze', [ClusteringController::class, 'analyze'])->name('clustering.analyze');
    Route::get('clustering/results/{cluster}', [ClusteringController::class, 'results'])->name('clustering.results');
    Route::get('clustering/export/{cluster}', [ClusteringController::class, 'export'])->name('clustering.export');
    Route::get('clustering/rerun/{cluster}', [ClusteringController::class, 'rerun'])->name('clustering.rerun');
    Route::get('clustering/pdf/{cluster}', [ClusteringController::class, 'exportPdf'])->name('clustering.pdf');
    Route::get('clustering/history', [ClusteringController::class, 'history'])->name('clustering.history');
    Route::post('clustering/{cluster}/update-label', [ClusteringController::class, 'updateLabel'])->name('clustering.updateLabel');
    
    // Owner report (view only, protected by gate)
    Route::get('/owner/report', [OwnerReportController::class, 'index'])->name('owner.report');
});

// Gudang routes
Route::middleware(['web','auth'])->group(function(){
    Route::get('/gudang', [\App\Http\Controllers\GudangController::class, 'index'])->name('gudang.index');
    Route::get('/gudang/create', [\App\Http\Controllers\GudangController::class, 'create'])->name('gudang.create');
    Route::post('/gudang', [\App\Http\Controllers\GudangController::class, 'store'])->name('gudang.store');
    Route::get('/gudang/{gudang}/edit', [\App\Http\Controllers\GudangController::class, 'edit'])->name('gudang.edit');
    Route::put('/gudang/{gudang}', [\App\Http\Controllers\GudangController::class, 'update'])->name('gudang.update');
    Route::delete('/gudang/{gudang}', [\App\Http\Controllers\GudangController::class, 'destroy'])->name('gudang.destroy');
});

// Owner dashboard route (protected by Gate in controller)
Route::middleware(['web','auth'])->group(function(){
    Route::get('/owner/dashboard', [\App\Http\Controllers\OwnerController::class, 'dashboard'])->name('owner.dashboard');
});
// Provide a short '/owner' URL that redirects to the dashboard
Route::get('/owner', function(){
    return redirect('/owner/dashboard');
})->middleware(['web','auth']);
// Fallback
// Route::fallback(function () {
//     return redirect()->route('login');
// });
