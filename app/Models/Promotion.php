<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'code',
        'description',
        'type',
        'value',
        'max_discount',
        'min_order_amount',
        'start_date',
        'end_date',
        'status_id',
    ];

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_promotions')
            ->withPivot('custom_discount_value')
            ->withTimestamps();
    }

    // The "scope" prefix is a Laravel magic word.
    // scopeActive in the model, Laravel allows you to call it as active() (removing the "scope" prefix).
    public function scopeActive($query)
    {
        return $query->where('status_id', 1)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }
}
