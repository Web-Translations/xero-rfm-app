<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroInvoice extends Model
{
    protected $fillable = [
        'user_id','tenant_id','invoice_id','contact_id','status','type','invoice_number',
        'date','due_date','subtotal','total','currency','updated_date_utc','fully_paid_at'
    ];
    protected $casts = [
        'date'=>'date','due_date'=>'date',
        'updated_date_utc'=>'datetime','fully_paid_at'=>'datetime'
    ];
    public function user(){ return $this->belongsTo(User::class); }
}
