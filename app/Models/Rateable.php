<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rateable extends Model
{
    use HasFactory;

    protected $table = 'rateables';
    protected $primaryKey = 'id';
    protected $fillable = [
        'ratable_id',
        'ratable_type',
        'rating',
        'review',
        'user_id',
    ];

    public function rateable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
