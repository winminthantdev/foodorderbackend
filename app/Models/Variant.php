<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    //

    use HasFactory;

    protected $table = 'variants';

    protected $primaryKey = 'id';

    protected $fillable = [
        'menu_id',
        'name',
        'extra_price',
        'status_id'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
}
