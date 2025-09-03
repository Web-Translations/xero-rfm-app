<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroConnection extends Model
{
    protected $fillable = ['user_id','tenant_id','org_name','access_token','refresh_token','expires_at','is_active','last_sync_at','last_sync_invoice_count'];
    protected $casts = [
        'expires_at' => 'datetime', // Store as standard UTC datetime
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];
    public function user(){ return $this->belongsTo(User::class); }
    
    /**
     * Get the active connection for a user
     */
    public static function getActiveForUser(int $userId): ?self
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
    }
    
    /**
     * Get all connections for a user
     */
    public static function getAllForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', $userId)
            ->orderBy('is_active', 'desc')
            ->orderBy('org_name')
            ->get();
    }
    
    /**
     * Set this connection as active and deactivate others
     */
    public function setActive(): void
    {
        // Use a transaction to ensure atomicity
        \DB::transaction(function () {
            // Deactivate all other connections for this user
            self::where('user_id', $this->user_id)
                ->where('id', '!=', $this->id)
                ->update(['is_active' => false]);
                
            // Activate this connection
            $this->update(['is_active' => true]);
        });
    }
    
    /**
     * Check if this connection is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
    
    /**
     * Ensure only one connection is active per user
     * This is a safety method to fix any data integrity issues
     */
    public static function ensureSingleActiveConnection(int $userId): void
    {
        $activeConnections = self::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
            
        if ($activeConnections->count() > 1) {
            // Keep the first one active, deactivate the rest
            $first = $activeConnections->first();
            $activeConnections->where('id', '!=', $first->id)
                ->each(function ($connection) {
                    $connection->update(['is_active' => false]);
                });
        }
    }
}
