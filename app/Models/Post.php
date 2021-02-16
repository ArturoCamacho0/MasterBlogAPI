<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    public function user(){
        $this->hasMany('users');
    }

    public function categories(){
        return $this->belongsTo('App\Category', 'category_id');
    }

    public function users(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
