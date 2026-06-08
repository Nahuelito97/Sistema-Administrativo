<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'description',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function caracteristics()
    {
        return $this->hasMany(Caracteristic::class);
    }

    /** Hijos cargados recursivamente (para el árbol). */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /** Ruta de ancestros como "Padre > Hijo > Actual". */
    public function getPathAttribute(): string
    {
        $names = [$this->name];
        $node = $this->parent;
        $guard = 0;
        while ($node && $guard++ < 10) {
            array_unshift($names, $node->name);
            $node = $node->parent;
        }
        return implode(' › ', $names);
    }
}
