<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoCardlessPaymentEvent extends Model
{
    use HasFactory;

    protected $table = 'gocardless_payment_events';

    protected $fillable = [
        'user_id',
        'payment_id',
        'event_id',
        'status',
        'charge_date',
        'amount',
        'currency',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'charge_date' => 'date',
    ];
}


