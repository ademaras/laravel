<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasOne(User::class,'id', 'user_id');
    }

    public function delivery()
    {
        return $this->hasOne(User::class, 'id','delivery_user');
    }
}
