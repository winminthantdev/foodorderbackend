<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userinfo extends Model
{
    use HasFactory;

    protected $table = 'userinfos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'gender',
        'avatar',
        'date_of_birth',
        'loyalty_points',
        'notification_enabled',
        'last_active',
        'user_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

}
