<?php

use App\Http\Controllers\XeroController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RfmController;
use App\Http\Controllers\RfmReportsController;
use App\Http\Controllers\RfmAnalysisController;
use App\Http\Controllers\RfmConfigController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\TokenController;
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

    // Organisation management
    Route::get('/organisations', [OrganisationController::class, 'index'])->name('organisations.index');
    Route::post('/organisations/{connection}/switch', [OrganisationController::class, 'switch'])->name('organisations.switch');
    Route::delete('/organisations/{connection}/disconnect', [OrganisationController::class, 'disconnect'])->name('organisations.disconnect');

    // Token management (moved to auto-refresh group below)

    // User profile (Breeze expects these route names)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

// Token management (simplified for debugging)
Route::post('/token/refresh', [TokenController::class, 'refresh'])->name('token.refresh')->middleware('auth');
Route::get('/token/status', [TokenController::class, 'status'])->name('token.status')->middleware('auth');
Route::post('/token/reconnect', [TokenController::class, 'reconnect'])->name('token.reconnect')->middleware('auth');

// Require Xero link before accessing app features that need it
Route::middleware(['auth', 'auto.refresh.xero', EnsureXeroLinked::class])->group(function () {
    
    // Invoices from DB
    Route::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');
    Route::post('/invoices/sync', [InvoicesController::class, 'sync'])->name('invoices.sync');
    Route::post('/invoices/{invoice}/exclude', [InvoicesController::class, 'exclude'])->name('invoices.exclude');
    Route::delete('/invoices/{invoice}/exclude', [InvoicesController::class, 'unexclude'])->name('invoices.unexclude');
    
    // RFM Scores (renamed from RFM Analysis)
    Route::get('/rfm', [RfmController::class, 'index'])->name('rfm.index');
    Route::post('/rfm/sync', [RfmController::class, 'sync'])->name('rfm.sync');
    
    // RFM Reports
    Route::get('/rfm/reports', [RfmReportsController::class, 'index'])->name('rfm.reports.index');
    Route::get('/rfm/reports/generate', [RfmReportsController::class, 'generate'])->name('rfm.reports.generate');
    
    // RFM Configuration
    Route::prefix('rfm/config')->name('rfm.config.')->group(function () {
        Route::get('/', [RfmConfigController::class, 'index'])->name('index');
        Route::post('/', [RfmConfigController::class, 'store'])->name('store');
        Route::post('/reset', [RfmConfigController::class, 'reset'])->name('reset');
        Route::post('/recalculate', [RfmConfigController::class, 'recalculate'])->name('recalculate');
    });
    
    // RFM Analysis
    Route::get('/rfm/analysis', [RfmAnalysisController::class, 'index'])->name('rfm.analysis.index');
    Route::get('/rfm/analysis/trends', [RfmAnalysisController::class, 'trends'])->name('rfm.analysis.trends');
});



require __DIR__.'/auth.php';
