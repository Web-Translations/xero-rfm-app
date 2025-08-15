<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['user_id','tenant_id','contact_id','name'];
    public function user(){ return $this->belongsTo(User::class); }
}
