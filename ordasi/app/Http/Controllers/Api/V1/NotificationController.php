<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Mis notificaciones + cuántas sin leer. */
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'data'   => $notifications,
            'unread' => $notifications->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->update(['read_at' => now()]);
        return response()->json(null, 204);
    }

    public function markAllRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(null, 204);
    }
}
