<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroInvoice extends Model
{
    protected $fillable = [
<<<<<<< Updated upstream
        'user_id','tenant_id','invoice_id','contact_id','status','invoice_number',
        'date','due_date','subtotal','total','currency','updated_date_utc','fully_paid_at'
=======
        'user_id','tenant_id','invoice_id','contact_id','status','type','invoice_number',
        'date','due_date','subtotal','total','currency','updated_date_utc','fully_paid_at',
        'r_score', 'f_score', 'm_score', 'rfm_score', 'rfm_calculated_at'
>>>>>>> Stashed changes
    ];
    
    protected $casts = [
        'date'=>'date','due_date'=>'date',
        'updated_date_utc'=>'datetime','fully_paid_at'=>'datetime',
        'rfm_calculated_at'=>'datetime',
        'r_score'=>'decimal:2',
        'f_score'=>'decimal:2', 
        'm_score'=>'decimal:2',
        'rfm_score'=>'decimal:2'
    ];
    
    public function user(){ return $this->belongsTo(User::class); }
    
    public function client(){ return $this->belongsTo(Client::class, 'contact_id', 'contact_id'); }
}
