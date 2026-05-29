<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:users.index')->only(['index', 'show']);
        $this->middleware('can:users.create')->only(['store']);
        $this->middleware('can:users.edit')->only(['update']);
        $this->middleware('can:users.destroy')->only(['destroy']);
    }

    public function index()
    {
        return UserResource::collection(User::with('roles')->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:5'],
            'roles'    => ['array'],
            'roles.*'  => ['string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);
        $user->syncRoles($data['roles'] ?? []);

        return new UserResource($user->load('roles'));
    }

    public function show(User $user)
    {
        return new UserResource($user->load('roles'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->id === 1) {
            abort(403, 'El usuario administrador principal no puede editarse.');
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:5'],
            'roles'    => ['array'],
            'roles.*'  => ['string', 'exists:roles,name'],
        ]);

        $user->update(array_filter([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'] ?? null,
        ], fn ($v) => $v !== null));
        $user->syncRoles($data['roles'] ?? []);

        return new UserResource($user->load('roles'));
    }

    public function destroy(User $user)
    {
        if ($user->id === 1) {
            abort(403, 'El usuario administrador principal no puede eliminarse.');
        }
        $user->delete();
        return response()->json(null, 204);
    }
}
