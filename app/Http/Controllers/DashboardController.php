<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\XeroInvoice;
use App\Models\XeroConnection;
use App\Models\RfmReport;
use App\Models\RfmConfiguration;
use App\Models\ExcludedInvoice;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get platform status data
        $platformStatus = $this->getPlatformStatus();
        
        // Setup/checklist state
        $activeConnection = $user?->getActiveXeroConnection();
        $tenantId = $activeConnection?->tenant_id;

        $hasConnection = (bool) $activeConnection;
        $invoiceCount = $hasConnection
            ? XeroInvoice::where('user_id', $user->id)->where('tenant_id', $tenantId)->count()
            : 0;
        $hasInvoices = $invoiceCount > 0;

        $lastSyncAt = $activeConnection?->last_sync_at;
        $daysSinceSync = $lastSyncAt ? now()->diffInDays($lastSyncAt) : null;

        // Latest RFM snapshot date (acts as last compute time)
        $latestSnapshotDate = $hasConnection
            ? RfmReport::where('user_id', $user->id)
                ->whereHas('client', function ($q) use ($tenantId) { $q->where('tenant_id', $tenantId); })
                ->max('snapshot_date')
            : null;
        $hasRfm = !empty($latestSnapshotDate);
        $lastRfmComputedAt = $latestSnapshotDate ? Carbon::parse($latestSnapshotDate) : null;

        // Config/exclusions recalc checks
        $config = $hasConnection ? RfmConfiguration::getOrCreateDefault($user->id, $tenantId) : null;
        $configUpdatedAt = $config?->updated_at;
        $exclusionsUpdatedAt = $hasConnection
            ? ExcludedInvoice::where('user_id', $user->id)->where('tenant_id', $tenantId)->max('updated_at')
            : null;

        $needsRecalc = false;
        if ($lastRfmComputedAt) {
            if ($configUpdatedAt && Carbon::parse($configUpdatedAt)->gt($lastRfmComputedAt)) {
                $needsRecalc = true;
            }
            if ($exclusionsUpdatedAt && Carbon::parse($exclusionsUpdatedAt)->gt($lastRfmComputedAt)) {
                $needsRecalc = true;
            }
        }

        return view('dashboard', [
            'platformStatus'   => $platformStatus,
            'activeConnection' => $activeConnection,
            'hasConnection'    => $hasConnection,
            'hasInvoices'      => $hasInvoices,
            'invoiceCount'     => $invoiceCount,
            'lastSyncAt'       => $lastSyncAt,
            'daysSinceSync'    => $daysSinceSync,
            'hasRfm'           => $hasRfm,
            'lastRfmComputedAt'=> $lastRfmComputedAt,
            'needsRecalc'      => $needsRecalc,
        ]);
    }
    

    
    private function getPlatformStatus()
    {
        return [
            'sync_status' => 'Connected',
            'platform_health' => 'Excellent',
            'next_sync' => now()->addMinutes(45)->format('g:i A'),
            'system_uptime' => '99.9%',
            'data_security' => 'Enterprise',
            'support_response' => '< 2 hours'
        ];
    }
}
