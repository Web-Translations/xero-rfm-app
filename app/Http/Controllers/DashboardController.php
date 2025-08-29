<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get platform status data
        $platformStatus = $this->getPlatformStatus();
        
        return view('dashboard', compact('platformStatus'));
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
