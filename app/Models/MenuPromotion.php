<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuPromotion extends Model
{
    use HasFactory;

    protected $table = 'menu_promotions';
    protected $fillable = [
        'menu_id',
        'promotion_id',
        'custom_discount_value',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
