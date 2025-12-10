<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paymenttype extends Model
{
     use HasFactory;

    protected $table = 'paymenttypes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'status_id'
    ];

    public function status(){
        return $this->belongsTo(Status::class);
    }
}
