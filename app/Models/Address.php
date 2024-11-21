<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'province',
        'district',
        'state',
        'open_address',
        'title',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
