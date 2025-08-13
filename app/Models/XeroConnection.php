<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroConnection extends Model
{
    protected $fillable = ['user_id','tenant_id','org_name','access_token','refresh_token','expires_at'];
    protected $casts = ['expires_at' => 'datetime'];
    public function user(){ return $this->belongsTo(User::class); }
}
