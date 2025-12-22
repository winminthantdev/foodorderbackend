<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'ordertype_id',
        'paymenttype_id',
        'driver_id',
        'stage_id',
        'address_id',
        'subtotal',
        'discount',
        'delivery_fee',
        'service_fee',
        'total',
        'transaction_id',
        'is_paid',
        'order_note',
        'scheduled_at',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stage(){
        return $this->belongsTo(Stage::class);
    }

    public function ordertype()
    {
        return $this->belongsTo(OrderType::class);
    }

    public function paymenttype()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }

}
