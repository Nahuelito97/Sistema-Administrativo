<?php

namespace App\Http\Controllers\Api\V1;

use App\Claim;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClaimResource;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClaimController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:claims.index')->only(['index']);
        $this->middleware('can:claims.resolve')->only(['resolve']);
        // store / myClaims: el comprador autenticado
    }

    // ---------- Comprador ----------

    /** Abrir un reclamo sobre una orden propia. */
    public function store(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->claims()->where('status', 'pending')->exists()) {
            throw ValidationException::withMessages(['claim' => ['Ya tenés un reclamo abierto para este pedido.']]);
        }

        $data = $request->validate([
            'type'   => ['required', Rule::in(['cancellation', 'refund'])],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $claim = $order->claims()->create([
            'user_id'    => $request->user()->id,
            'company_id' => $order->company_id,
            'type'       => $data['type'],
            'reason'     => $data['reason'],
        ]);

        return (new ClaimResource($claim->load('order', 'company')))->response()->setStatusCode(201);
    }

    /** Mis reclamos (como comprador). */
    public function myClaims(Request $request)
    {
        return ClaimResource::collection(
            Claim::with('company', 'order')->where('user_id', $request->user()->id)->latest()->paginate(20)
        );
    }

    // ---------- Vendedor / Admin ----------

    /** Reclamos de mi tienda (scoped) o todos (admin). */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Claim::with(['company', 'user', 'order']);

        if (! $user->hasRole('Admin')) {
            $query->where('company_id', $user->company_id);
        }
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return ClaimResource::collection($query->latest()->paginate(20));
    }

    /** Resolver: aprobar (con efectos) o rechazar. */
    public function resolve(Request $request, Claim $claim)
    {
        $user = $request->user();
        abort_unless($user->hasRole('Admin') || (int) $user->company_id === (int) $claim->company_id, 403);
        abort_if($claim->status !== 'pending', 422, 'El reclamo ya fue resuelto.');

        $data = $request->validate([
            'status'     => ['required', Rule::in(['approved', 'rejected'])],
            'resolution' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($claim, $data) {
            $claim->update([
                'status'      => $data['status'],
                'resolution'  => $data['resolution'] ?? null,
                'resolved_at' => now(),
            ]);

            if ($data['status'] === 'approved') {
                $this->applyApproval($claim);
            }
        });

        return new ClaimResource($claim->fresh()->load('company', 'user', 'order'));
    }

    /** Aprobar cancelación/devolución: reembolsa, cancela envío y restaura stock. */
    private function applyApproval(Claim $claim): void
    {
        $order = $claim->order;
        $order->update(['payment_status' => 'REFUNDED', 'shipping_status' => 'CANCELED']);

        foreach ($order->details()->with('variant', 'product')->get() as $detail) {
            if ($detail->variant) {
                $detail->variant->increment('stock', $detail->quantity);
            } elseif ($detail->product) {
                $detail->product->increment('stock', $detail->quantity);
            }
        }
    }
}
