<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
     use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'id';

    protected $fillable = [
        'amount',
        'order_id',
        'user_id',
        'stage_id',
        'paymenttype_id',
        'transition_id',
    ];

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function paymentType() {
        return $this->belongsTo(PaymentType::class);
    }

    public function status() {
        return $this->belongsTo(Status::class);
    }

}
