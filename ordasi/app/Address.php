<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = ['user_id', 'label', 'recipient', 'phone', 'street', 'city', 'province', 'postal_code', 'notes', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Una línea con la dirección completa (para snapshot en la orden). */
    public function getOneLineAttribute(): string
    {
        return collect([$this->street, $this->city, $this->province, $this->postal_code])
            ->filter()->implode(', ');
    }
}
