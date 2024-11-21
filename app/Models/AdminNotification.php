<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasFactory;
    protected $fillable =
        [
            'user_id',
            'target_users',
            'title',
            'desc',
            'image',
        ];


    public function notifyType()
    {
        return $this->hasMany(Notification::class, 'admin_notify_id', 'id');
    }
}
