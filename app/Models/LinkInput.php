<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkInput extends Model
{
    use HasFactory;

    protected $table = 'link_inputs';
    protected $guarded = ['id'];

    public function link()
    {
        return $this->belongsTo(Links::class, 'link_id');
    }

    public function corporateInfo()
    {
        return $this->belongsTo(CorporateLinkInfo::class, 'id');
    }

    public function corporateInfos()
    {
        return $this->hasMany(CorporateLinkInfo::class, 'id');
    }
}
