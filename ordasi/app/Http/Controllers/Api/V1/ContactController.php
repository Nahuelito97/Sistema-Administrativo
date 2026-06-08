<?php

namespace App\Http\Controllers\Api\V1;

use App\ContactMessage;
use App\Http\Controllers\Controller;
use App\Notification;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:settings.index')->only(['index', 'markHandled']);
        // store es público
    }

    /** Envío público del formulario de contacto. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        ContactMessage::create($data);
        Notification::notifyAdmins('contact', 'Nuevo mensaje de contacto', $data['subject'] ?? $data['name'], '/contactos');

        return response()->json(['message' => 'Mensaje enviado'], 201);
    }

    /** Bandeja del admin. */
    public function index(Request $request)
    {
        return ContactMessage::latest()->paginate(20);
    }

    public function markHandled(ContactMessage $contact)
    {
        $contact->update(['handled' => true]);
        return response()->json(['data' => $contact]);
    }
}
