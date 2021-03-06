<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name', 'slug',
    ];

    public function post() {
        return $this->hasMany(Post::class);
    }

    public function getRouteKeyName() {
        return 'slug';
    }
}
