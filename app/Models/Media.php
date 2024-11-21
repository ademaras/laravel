<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';
    protected $fillable = ['user_id', 'file_path', 'media_folder_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    
    public function folder()
    {
        return $this->hasMany(MediaFolder::class);
    }
}
