<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SellerResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Moderación de vendedores por parte del admin (aprobación de KYC).
 */
class SellerAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:sellers.index')->only(['index', 'show']);
        $this->middleware('can:sellers.approve')->only(['updateStatus']);
    }

    /** Solicitudes y vendedores (todo lo que no esté INACTIVE), filtrable por estado. */
    public function index(Request $request)
    {
        $query = User::with('company')
            ->where('status_seller_id', '!=', User::SELLER_INACTIVE);

        if ($status = $request->query('status')) {
            $map = array_flip(User::SELLER_LABELS);
            if (isset($map[$status])) {
                $query->where('status_seller_id', $map[$status]);
            }
        }
        if ($s = $request->query('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
        }

        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return SellerResource::collection($query->latest('updated_at')->paginate($perPage));
    }

    public function show(User $user)
    {
        return new SellerResource($user->load('company'));
    }

    /** Aprobar / rechazar / banear / reactivar un vendedor. */
    public function updateStatus(Request $request, User $user)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'banned', 'inactive'])],
        ]);
        $target = array_flip(User::SELLER_LABELS)[$data['status']];

        abort_unless(
            $user->canTransitionSellerTo($target),
            422,
            "No se puede pasar de '{$user->seller_status}' a '{$data['status']}'."
        );

        DB::transaction(function () use ($user, $target, $data) {
            $user->transitionSellerTo($target);

            // Asignar / quitar el rol Vendedor y sincronizar el estado de la tienda.
            if ($target === User::SELLER_ACTIVE) {
                $user->assignRole('Vendedor');
                $user->company?->update(['status' => 'active']);
            } else { // banned o inactive
                $user->removeRole('Vendedor');
                $user->company?->update(['status' => 'inactive']);
            }
        });

        return new SellerResource($user->fresh()->load('company'));
    }
}
