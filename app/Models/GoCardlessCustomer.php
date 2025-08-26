<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoCardlessCustomer extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'gocardless_customers';

    protected $fillable = [
        'user_id',
        'gocardless_customer_id',
        'email',
        'given_name',
        'family_name',
        'company_name',
        'address_line1',
        'address_line2',
        'city',
        'region',
        'postal_code',
        'country_code',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the customer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full name of the customer
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->given_name . ' ' . $this->family_name);
    }

    /**
     * Get the display name (company name or full name)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company_name ?: $this->full_name;
    }
}
