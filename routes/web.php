<?php

use App\Http\Controllers\XeroController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RfmController;
use App\Http\Controllers\InvoicesController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureXeroLinked;
use Illuminate\Http\Request;

Route::get('/', fn () => view('landing'))->name('landing');

// Compatibility callback if your Xero app still points to /xero/callback
Route::get('/xero/callback', function (Request $request) {
    return redirect()->route('xero.auth.callback', $request->query());
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // Xero OAuth flow (package handles authorize + callback)
    Route::get('/xero/connect',  [XeroController::class, 'connect'])->name('xero.connect');

    // User profile (Breeze expects these route names)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

// Require Xero link before accessing app features that need it
Route::middleware(['auth', EnsureXeroLinked::class])->group(function () {
    // Invoices from DB
    Route::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');
    Route::post('/invoices/sync', [InvoicesController::class, 'sync'])->name('invoices.sync');
    
    // RFM Analysis
    Route::get('/rfm', [RfmController::class, 'index'])->name('rfm.index');
    Route::post('/rfm/sync', [RfmController::class, 'sync'])->name('rfm.sync');
});

require __DIR__.'/auth.php';
