<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
    protected $table = 'directories';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
