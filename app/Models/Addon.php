<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;

    protected $table = 'addons';

    protected $fillable = [
        'name',
        'price',
        'status_id',
    ];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_addons')
            ->withPivot(['max_quantity', 'custom_price'])
            ->withTimestamps();
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
