<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'subscription_plan',
        'gocardless_subscription_id',
        'gocardless_mandate_id',
        'subscription_status',
        'subscription_ends_at',
        'gc_last_completed_flow_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_ends_at' => 'datetime',
            'admin' => 'boolean',
        ];
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_status === 'active' && 
               ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }

    /**
     * Check if subscription is eligible to use premium features (active or pending).
     */
    public function hasPremiumEligibleSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'pending'], true)
            && ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }

    /**
     * Check if user has specific plan
     */
    public function hasPlan(string $plan): bool
    {
        return $this->subscription_plan === $plan && $this->hasActiveSubscription();
    }

    /**
     * Check if user can access premium features
     */
    public function canAccessPremium(): bool
    {
        return in_array($this->subscription_plan, ['pro', 'pro_plus'], true)
            && $this->hasPremiumEligibleSubscription();
    }

    /**
     * Check if user can access AI features
     */
    public function canAccessAI(): bool
    {
        return $this->subscription_plan === 'pro_plus' && $this->hasPremiumEligibleSubscription();
    }

    /**
     * Relation: all XeroConnections for this user
     */
    public function xeroConnections()
    {
        return $this->hasMany(\App\Models\XeroConnection::class);
    }
    
    /**
     * Relation: the active XeroConnection for this user
     */
    public function activeXeroConnection()
    {
        return $this->hasOne(\App\Models\XeroConnection::class)->where('is_active', true);
    }
    
    /**
     * Get the active Xero connection
     */
    public function getActiveXeroConnection(): ?XeroConnection
    {
        return XeroConnection::getActiveForUser($this->id);
    }
    
    /**
     * Get all Xero connections
     */
    public function getAllXeroConnections()
    {
        return XeroConnection::getAllForUser($this->id);
    }

    /**
     * Get all excluded invoices for this user
     */
    public function excludedInvoices()
    {
        return $this->hasMany(ExcludedInvoice::class);
    }

    /**
     * Get the GoCardless customer for this user
     */
    public function gocardlessCustomer()
    {
        return $this->hasOne(GoCardlessCustomer::class);
    }
}

