<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcludedInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'invoice_id',
    ];

    /**
     * Get the user that owns the excluded invoice.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the actual invoice that is excluded.
     */
    public function invoice()
    {
        return $this->belongsTo(XeroInvoice::class, 'invoice_id', 'invoice_id')
            ->where('user_id', $this->user_id)
            ->where('tenant_id', $this->tenant_id);
    }

    /**
     * Check if an invoice is excluded for a specific user and tenant.
     */
    public static function isExcluded(int $userId, string $tenantId, string $invoiceId): bool
    {
        return self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->where('invoice_id', $invoiceId)
            ->exists();
    }

    /**
     * Get all excluded invoice IDs for a user and tenant.
     */
    public static function getExcludedInvoiceIds(int $userId, string $tenantId): array
    {
        return self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->pluck('invoice_id')
            ->toArray();
    }
}
