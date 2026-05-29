<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('can:users.create')->only(['create', 'store']);
        $this->middleware('can:users.index')->only(['index']);
        $this->middleware('can:users.edit')->only(['edit', 'update']);
        $this->middleware('can:users.show')->only(['show']);
        $this->middleware('can:users.destroy')->only(['destroy']);
    }

    public function index()
    {
        $users = User::get();
        return view('admin.user.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::get();
        return view('admin.user.create', compact('roles'));
    }

    public function store(Request $request)
    {
        // El cast 'hashed' del modelo User hashea el password automáticamente.
        $user = User::create($request->only('name', 'email', 'password'));
        $user->syncRoles($request->get('roles') ?? []);

        return redirect()->route('users.index');
    }

    public function show(User $user)
    {
        $total_purchases = 0;
        foreach ($user->sales as $sale) {
            $total_purchases += $sale->total;
        }
        $total_amount_sold = 0;
        foreach ($user->purchases as $purchase) {
            $total_amount_sold += $purchase->total;
        }
        return view('admin.user.show', compact('user', 'total_purchases', 'total_amount_sold'));
    }

    public function edit(User $user)
    {
        $roles = Role::get();
        return view('admin.user.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->id == 1) {
            return redirect()->route('users.index');
        }

        $data = $request->only('name', 'email');
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }
        $user->update($data);
        $user->syncRoles($request->get('roles') ?? []);

        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        if ($user->id == 1) {
            return back();
        }
        $user->delete();
        return back();
    }
}
