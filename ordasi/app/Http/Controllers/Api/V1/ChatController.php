<?php

namespace App\Http\Controllers\Api\V1;

use App\Conversation;
use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Order;
use Illuminate\Http\Request;

/**
 * Chat comprador <-> vendedor, una conversación por orden. REST + polling.
 */
class ChatController extends Controller
{
    /** Mis conversaciones (como comprador y/o como vendedor de mi tienda). */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Conversation::with(['company', 'buyer', 'messages']);

        if ($user->hasRole('Admin')) {
            // ve todas
        } elseif ($user->hasRole('Vendedor') && $user->company_id) {
            $query->where(fn ($q) => $q->where('buyer_id', $user->id)->orWhere('company_id', $user->company_id));
        } else {
            $query->where('buyer_id', $user->id);
        }

        return ConversationResource::collection($query->orderByDesc('last_message_at')->orderByDesc('id')->paginate(30));
    }

    /** Obtiene (o crea) la conversación de una orden. */
    public function forOrder(Request $request, Order $order)
    {
        $user = $request->user();
        $isBuyer = $order->user_id === $user->id;
        $isSeller = $user->hasRole('Vendedor') && (int) $user->company_id === (int) $order->company_id;
        abort_unless($isBuyer || $isSeller || $user->hasRole('Admin'), 403);

        $conversation = Conversation::firstOrCreate(
            ['order_id' => $order->id],
            ['buyer_id' => $order->user_id, 'company_id' => $order->company_id]
        );

        return new ConversationResource($conversation->load(['company', 'buyer']));
    }

    /** Mensajes de una conversación (y marca como leídos los ajenos). */
    public function messages(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->isParticipant($request->user()), 403);

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $request->user()->id)
            ->update(['read_at' => now()]);

        return MessageResource::collection($conversation->messages()->with('sender')->orderBy('created_at')->get());
    }

    /** Enviar un mensaje. */
    public function send(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->isParticipant($request->user()), 403);
        $request->validate([
            'body'       => ['nullable', 'string', 'max:2000', 'required_without:attachment'],
            'attachment' => ['nullable', 'file', 'max:8192', 'required_without:body'],
        ]);

        $payload = ['sender_id' => $request->user()->id, 'body' => $request->input('body')];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $payload['attachment_path'] = $file->store('chat', 'public');
            $payload['attachment_name'] = $file->getClientOriginalName();
        }

        $message = $conversation->messages()->create($payload);
        $conversation->update(['last_message_at' => now()]);

        // Notifica al otro lado: si escribe el comprador → a la tienda; si la tienda → al comprador.
        if ($request->user()->id === $conversation->buyer_id) {
            \App\Notification::notifyCompany($conversation->company_id, 'message', 'Nuevo mensaje', "Pedido #{$conversation->order_id}", '/mensajes');
        } else {
            \App\Notification::notify($conversation->buyer_id, 'message', 'Nuevo mensaje del vendedor', "Pedido #{$conversation->order_id}", '/mi-cuenta');
        }

        return (new MessageResource($message->load('sender')))->response()->setStatusCode(201);
    }
}
