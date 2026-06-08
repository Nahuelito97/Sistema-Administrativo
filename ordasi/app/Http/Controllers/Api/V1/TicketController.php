<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Notification;
use App\Ticket;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:tickets.index')->only(['index']);
        $this->middleware('can:tickets.manage')->only(['updateStatus']);
        // store / myTickets / show / reply: usuario autenticado (dueño) o admin
    }

    // ---------- Usuario ----------

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body'    => ['required', 'string', 'max:2000'],
        ]);

        $ticket = Ticket::create([
            'user_id'         => $request->user()->id,
            'subject'         => $data['subject'],
            'last_message_at' => now(),
        ]);
        $ticket->messages()->create(['sender_id' => $request->user()->id, 'body' => $data['body']]);

        Notification::notifyAdmins('ticket', 'Nuevo ticket de soporte', $data['subject'], '/soporte');

        return (new TicketResource($ticket->load('messages', 'user')))->response()->setStatusCode(201);
    }

    public function myTickets(Request $request)
    {
        return TicketResource::collection(
            Ticket::where('user_id', $request->user()->id)->latest('last_message_at')->paginate(20)
        );
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorizeAccess($request, $ticket);
        return new TicketResource($ticket->load('messages', 'user'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $this->authorizeAccess($request, $ticket);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $isAdmin = $request->user()->hasRole('Admin');

        $ticket->messages()->create([
            'sender_id'  => $request->user()->id,
            'from_admin' => $isAdmin,
            'body'       => $data['body'],
        ]);
        $ticket->update(['last_message_at' => now(), 'status' => $isAdmin ? 'answered' : 'open']);

        if ($isAdmin) {
            Notification::notify($ticket->user_id, 'ticket', 'Respuesta a tu ticket', $ticket->subject, '/soporte');
        } else {
            Notification::notifyAdmins('ticket', 'Respuesta en un ticket', $ticket->subject, '/soporte');
        }

        return new TicketResource($ticket->load('messages', 'user'));
    }

    // ---------- Admin ----------

    public function index(Request $request)
    {
        $query = Ticket::with('user');
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        return TicketResource::collection($query->latest('last_message_at')->paginate(20));
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['open', 'answered', 'closed'])]]);
        $ticket->update($data);
        return new TicketResource($ticket->load('user'));
    }

    private function authorizeAccess(Request $request, Ticket $ticket): void
    {
        abort_unless($ticket->user_id === $request->user()->id || $request->user()->hasRole('Admin'), 403);
    }
}
