<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Caracteristic extends Model
{
    protected $fillable = ['category_id', 'name'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_caracteristic')->withPivot('value');
    }
}
