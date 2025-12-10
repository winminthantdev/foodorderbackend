<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ordertype extends Model
{
    use HasFactory;

    protected $table = 'ordertypes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'description',
        'status_id',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
