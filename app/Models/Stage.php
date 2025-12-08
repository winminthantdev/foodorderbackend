<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $table = 'stages';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'status_id'
    ];

    public function status(){
        return $this->belongsTo(Status::class);
    }


}
