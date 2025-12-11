<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuAddon extends Model
{
    use HasFactory;

    protected $table = 'menu_addons';
    protected $fillable = [
        'menu_id',
        'addon_id',
        'max_quantity',   
        'custom_price',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }
}
