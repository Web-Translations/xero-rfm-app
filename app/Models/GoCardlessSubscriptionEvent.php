<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoCardlessSubscriptionEvent extends Model
{
    use HasFactory;

    protected $table = 'gocardless_subscription_events';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'event_id',
        'action',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}


