<?php

use App\Http\Controllers\XeroController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RfmController;
use App\Http\Controllers\RfmReportsController;
use App\Http\Controllers\RfmPdfController;
use App\Http\Controllers\RfmAnalysisController;
use App\Http\Controllers\RfmConfigController;
use App\Http\Controllers\RfmInsightsController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\MembershipsController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureXeroLinked;
use Illuminate\Http\Request;


Route::get('/', fn () => view('landing'))->name('landing');

// Legal pages
Route::get('/terms', fn () => view('legal.terms'))->name('terms');
Route::get('/privacy', fn () => view('legal.privacy'))->name('privacy');

// Compatibility callback if your Xero app still points to /xero/callback
Route::get('/xero/callback', function (Request $request) {
    return redirect()->route('xero.auth.callback', $request->query());
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Admin (restricted to admin users)
    Route::get('/admin', [AdminController::class, 'index'])->middleware('admin')->name('admin.index');

    // Xero OAuth flow (package handles authorize + callback)
    Route::get('/xero/connect',  [XeroController::class, 'connect'])->name('xero.connect');

    // Organisation management
    Route::get('/organisations', [OrganisationController::class, 'index'])->name('organisations.index');
    Route::post('/organisations/{connection}/switch', [OrganisationController::class, 'switch'])->name('organisations.switch');
    Route::delete('/organisations/{connection}/disconnect', [OrganisationController::class, 'disconnect'])->name('organisations.disconnect');

    // Memberships
    Route::get('/memberships', [MembershipsController::class, 'index'])->name('memberships.index');
    Route::get('/memberships/manage', [MembershipsController::class, 'manage'])->name('memberships.manage');
    Route::post('/memberships/subscribe', [MembershipsController::class, 'subscribe'])->name('memberships.subscribe');
    Route::post('/memberships/process-payment', [MembershipsController::class, 'processPayment'])->name('memberships.process-payment');
    Route::post('/memberships/cancel', [MembershipsController::class, 'cancel'])->name('memberships.cancel');
    Route::get('/memberships/payment', [MembershipsController::class, 'payment'])->name('memberships.payment');

    // User profile (Breeze expects these route names)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

// GoCardless success callback (no auth middleware - handled in controller)
Route::get('/memberships/success', [MembershipsController::class, 'success'])->name('memberships.success');

// Token management (simplified for debugging)
Route::post('/token/refresh', [TokenController::class, 'refresh'])->name('token.refresh')->middleware('auth');
Route::get('/token/status', [TokenController::class, 'status'])->name('token.status')->middleware('auth');
Route::post('/token/reconnect', [TokenController::class, 'reconnect'])->name('token.reconnect')->middleware('auth');

// Require Xero link before accessing app features that need it
Route::middleware(['auth', 'auto.refresh.xero', EnsureXeroLinked::class])->group(function () {
    
    // Invoices from DB
    Route::get('/invoices', [InvoicesController::class, 'index'])->name('invoices.index');
    Route::post('/invoices/sync', [InvoicesController::class, 'sync'])->name('invoices.sync');
    Route::get('/invoices/rfm-timeline', [InvoicesController::class, 'getRfmTimeline'])->name('invoices.rfm-timeline');
    Route::get('/invoices/rfm-timeline-view', function() { return view('invoices.rfm-timeline'); })->name('invoices.rfm-timeline-view');
    Route::get('/invoices/rfm-data', [InvoicesController::class, 'getRfmData'])->name('invoices.rfm-data');
    Route::get('/invoices/rfm-data-test', function() { return view('invoices.rfm-data-test'); })->name('invoices.rfm-data-test');

    Route::post('/invoices/{invoice}/exclude', [InvoicesController::class, 'exclude'])->name('invoices.exclude');
    Route::delete('/invoices/{invoice}/exclude', [InvoicesController::class, 'unexclude'])->name('invoices.unexclude');

    // Bulk exclude/include based on current filters
    Route::post('/invoices/bulk-exclude', [InvoicesController::class, 'bulkExclude'])->name('invoices.bulk-exclude');
    Route::post('/invoices/bulk-unexclude', [InvoicesController::class, 'bulkUnexclude'])->name('invoices.bulk-unexclude');
    
    // RFM Scores (renamed from RFM Analysis)
    Route::get('/rfm', [RfmController::class, 'index'])->name('rfm.index');
    Route::post('/rfm/sync', [RfmController::class, 'sync'])->name('rfm.sync');
    
    // RFM Reports
    Route::get('/rfm/reports', [RfmReportsController::class, 'index'])->name('rfm.reports.index');
    Route::get('/rfm/reports/generate', [RfmReportsController::class, 'generate'])->name('rfm.reports.generate');
    Route::get('/rfm/reports/pdf', [RfmPdfController::class, 'download'])->name('rfm.reports.pdf');
    Route::post('/rfm/reports/pdf', [RfmPdfController::class, 'generateFromBuilder'])->name('rfm.reports.pdf.generate');
    
    // AI Insights
    Route::post('/rfm/insights/generate', [RfmInsightsController::class, 'generate'])->name('rfm.insights.generate');
    
    // RFM Configuration
    Route::prefix('rfm/config')->name('rfm.config.')->group(function () {
        Route::get('/', [RfmConfigController::class, 'index'])->name('index');
        Route::post('/', [RfmConfigController::class, 'store'])->name('store');
        Route::post('/save-recalculate', [RfmConfigController::class, 'saveAndRecalculate'])->name('save-recalculate');
        Route::post('/reset', [RfmConfigController::class, 'reset'])->name('reset');
        Route::post('/recalculate', [RfmConfigController::class, 'recalculate'])->name('recalculate');
        Route::get('/benchmark-preview', [RfmConfigController::class, 'benchmarkPreview'])->name('benchmark-preview');
    });
    
    // RFM Analysis
    Route::get('/rfm/analysis', [RfmAnalysisController::class, 'index'])->name('rfm.analysis.index');
    Route::get('/rfm/analysis/trends', [RfmAnalysisController::class, 'trends'])->name('rfm.analysis.trends');

    Route::get('/rfm/analysis/components', [RfmAnalysisController::class, 'components'])->name('rfm.analysis.components');
    Route::get('/rfm/analysis/distributions', [RfmAnalysisController::class, 'distributions'])->name('rfm.analysis.distributions');
    Route::get('/rfm/analysis/segments', [RfmAnalysisController::class, 'segments'])->name('rfm.analysis.segments');
    Route::get('/rfm/analysis/predictive', [RfmAnalysisController::class, 'predictive'])->name('rfm.analysis.predictive');
    Route::get('/rfm/analysis/cohort', [RfmAnalysisController::class, 'cohort'])->name('rfm.analysis.cohort');
    Route::get('/rfm/analysis/comparative', [RfmAnalysisController::class, 'comparative'])->name('rfm.analysis.comparative');
    
    // RFM Analysis API endpoints
    Route::get('/rfm/analysis/component-trends', [RfmAnalysisController::class, 'rfmComponentTrends'])->name('rfm.analysis.component-trends');
    Route::get('/rfm/analysis/top-companies', [RfmAnalysisController::class, 'topCompaniesByComponent'])->name('rfm.analysis.top-companies');
});

// GoCardless webhook (no auth required, CSRF disabled)
Route::post('/webhooks/gocardless', [WebhookController::class, 'gocardless'])
    ->name('webhooks.gocardless')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Debug API route removed

require __DIR__.'/auth.php';
