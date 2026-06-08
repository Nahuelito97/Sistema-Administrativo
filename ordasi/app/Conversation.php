<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['order_id', 'buyer_id', 'company_id', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /** ¿El usuario participa? (comprador, vendedor de la tienda, o admin). */
    public function isParticipant(User $user): bool
    {
        return $user->id === $this->buyer_id
            || ($user->hasRole('Vendedor') && (int) $user->company_id === (int) $this->company_id)
            || $user->hasRole('Admin');
    }
}
