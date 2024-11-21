<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkCategories extends Model
{
    use HasFactory;

    protected $table = 'link_categories';
    protected $guarded = ['id'];

    public function links(){
        return $this->hasMany(Links::class, 'link_category_id', 'id')->with('inputs');
    }
}
