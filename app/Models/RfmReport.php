<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RfmReport extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'snapshot_date',
        'txn_count',
        'monetary_sum',
        'last_txn_date',
        'months_since_last',
        'r_score',
        'f_score',
        'm_score',
        'rfm_score',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'last_txn_date' => 'date',
        'rfm_score' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the latest RFM report for each client (by most recent ID)
     */
    public static function getLatestForUser(int $userId, string $tenantId = null)
    {
        $query = self::select('rfm_reports.*', 'clients.name as client_name')
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $userId);
            
        if ($tenantId) {
            $query->where('clients.tenant_id', $tenantId);
        }
        
        return $query->whereIn('rfm_reports.id', function ($subQuery) use ($userId, $tenantId) {
                $subQuery->select(DB::raw('MAX(rfm_reports.id)'))
                    ->from('rfm_reports')
                    ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
                    ->where('rfm_reports.user_id', $userId);
                    
                if ($tenantId) {
                    $subQuery->where('clients.tenant_id', $tenantId);
                }
                
                $subQuery->groupBy('rfm_reports.client_id');
            })
            ->orderBy('rfm_reports.rfm_score', 'desc');
    }

    /**
     * Get current RFM scores (today's date)
     */
    public static function getCurrentScoresForUser(int $userId, string $tenantId = null)
    {
        $today = now()->toDateString();
        
        $query = self::select('rfm_reports.*', 'clients.name as client_name')
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $userId)
            ->where('rfm_reports.snapshot_date', $today);
            
        if ($tenantId) {
            $query->where('clients.tenant_id', $tenantId);
        }
        
        return $query->orderBy('rfm_reports.rfm_score', 'desc');
    }

    /**
     * Get RFM reports for a specific snapshot date
     */
    public static function getForSnapshotDate(int $userId, string $snapshotDate, string $tenantId = null)
    {
        // Extract just the date part from the datetime string and ensure proper format
        $dateOnly = date('Y-m-d', strtotime($snapshotDate));
        
        $query = self::select('rfm_reports.*', 'clients.name as client_name')
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $userId)
            ->whereRaw('DATE(rfm_reports.snapshot_date) = ?', [$dateOnly]); // Use raw SQL for exact date matching
            
        if ($tenantId) {
            $query->where('clients.tenant_id', $tenantId);
        }
        
        return $query->orderBy('rfm_reports.rfm_score', 'desc');
    }

    /**
     * Get available snapshot dates for a user
     */
    public static function getAvailableSnapshotDates(int $userId, string $tenantId = null)
    {
        $query = self::where('rfm_reports.user_id', $userId)
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id');
            
        if ($tenantId) {
            $query->where('clients.tenant_id', $tenantId);
        }
        
        // Use raw SQL to get distinct dates and avoid any timezone/format issues
        $dates = $query->selectRaw('DISTINCT DATE(rfm_reports.snapshot_date) as date_only')
            ->pluck('date_only')
            ->map(function($date) {
                return date('Y-m-d', strtotime($date));
            })
            ->unique()
            ->sort()
            ->reverse()
            ->values();
            
        return $dates;
    }


} 