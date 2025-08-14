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
     * Get the latest RFM report for each client
     */
    public static function getLatestForUser(int $userId)
    {
        return self::select('rfm_reports.*', 'clients.name as client_name')
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $userId)
            ->whereIn('rfm_reports.id', function ($query) use ($userId) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('rfm_reports')
                    ->where('user_id', $userId)
                    ->groupBy('client_id');
            })
            ->orderBy('rfm_reports.rfm_score', 'desc');
    }

    /**
     * Get RFM reports for a specific snapshot date
     */
    public static function getForSnapshotDate(int $userId, string $snapshotDate)
    {
        return self::select('rfm_reports.*', 'clients.name as client_name')
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $userId)
            ->where('rfm_reports.snapshot_date', $snapshotDate)
            ->orderBy('rfm_reports.rfm_score', 'desc');
    }

    /**
     * Get available snapshot dates for a user
     */
    public static function getAvailableSnapshotDates(int $userId)
    {
        return self::where('user_id', $userId)
            ->distinct()
            ->pluck('snapshot_date')
            ->sort()
            ->reverse()
            ->values();
    }
} 