<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['product_id', 'user_id', 'question', 'answer', 'answered_at'];

    protected $casts = ['answered_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAnswered($query)
    {
        return $query->whereNotNull('answered_at');
    }
}
