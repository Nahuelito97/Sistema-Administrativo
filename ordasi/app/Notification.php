<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = ['user_id', 'type', 'title', 'body', 'link', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Crea una notificación para un usuario. */
    public static function notify(int $userId, string $type, string $title, ?string $body = null, ?string $link = null): void
    {
        static::create(['user_id' => $userId, 'type' => $type, 'title' => $title, 'body' => $body, 'link' => $link]);
    }

    /** Notifica a todos los administradores. */
    public static function notifyAdmins(string $type, string $title, ?string $body = null, ?string $link = null): void
    {
        User::role('Admin')->pluck('id')->each(fn ($uid) => static::notify($uid, $type, $title, $body, $link));
    }

    /** Notifica a todos los vendedores de una tienda. */
    public static function notifyCompany(?int $companyId, string $type, string $title, ?string $body = null, ?string $link = null): void
    {
        if (! $companyId) {
            return;
        }
        User::where('company_id', $companyId)
            ->where('status_seller_id', User::SELLER_ACTIVE)
            ->pluck('id')
            ->each(fn ($uid) => static::notify($uid, $type, $title, $body, $link));
    }
}
