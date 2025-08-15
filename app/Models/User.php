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
        ];
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
}
