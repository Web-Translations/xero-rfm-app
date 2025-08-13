<?php

use App\Http\Controllers\XeroController;
use App\Http\Controllers\ProfileController;
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
    Route::get('/demo/invoices', [XeroController::class, 'demoInvoices'])->name('demo.invoices');
    // Remove debug route if not needed anymore

    // Placeholder RFM Analysis page
    Route::view('/rfm', 'rfm.index')->name('rfm.index');
});

require __DIR__.'/auth.php';
