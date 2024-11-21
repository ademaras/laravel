<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkUsingLimit extends Model
{
    use HasFactory;

    protected $table = 'link_using_limits';
    protected $guarded = ['id'];

    public function link()
    {
        return $this->belongsTo(Links::class, 'link_id');
    }
}
