<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = ['title', 'body', 'image', 'link', 'active'];
    protected $casts = ['active' => 'boolean'];
}
