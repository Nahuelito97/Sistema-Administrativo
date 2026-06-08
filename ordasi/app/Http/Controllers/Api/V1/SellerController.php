<?php

namespace App\Http\Controllers\Api\V1;

use App\Company;
use App\Http\Controllers\Controller;
use App\Http\Resources\SellerResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Flujo "quiero vender" del usuario autenticado (comprador → vendedor).
 * Requiere solo auth:sanctum (cualquier usuario logueado puede postularse).
 */
class SellerController extends Controller
{
    /** Estado actual del vendedor + su tienda (para que el front sepa qué mostrar). */
    public function status(Request $request)
    {
        $user = $request->user()->load('company');
        return (new SellerResource($user))->additional(['can_sell' => $user->canSell()]);
    }

    /** Postulación a vendedor: crea la tienda (pending) + sube KYC + pasa a PENDING. */
    public function apply(Request $request)
    {
        $user = $request->user();

        abort_if(
            (int) $user->status_seller_id !== User::SELLER_INACTIVE,
            422,
            'Ya tenés una solicitud de vendedor en curso o sos vendedor.'
        );

        $data = $request->validate([
            'shop_name'      => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'cuit'           => ['nullable', 'string', 'max:20'],
            'cond_iva'       => ['nullable', 'string', 'max:60'],
            'social_network' => ['nullable', 'string', 'max:255'],
            'dni'            => ['nullable', 'string', 'max:20'],
            'cbu'            => ['nullable', 'string', 'max:30'],
            'dni_front'      => ['required', 'image', 'max:4096'],
            'dni_back'       => ['required', 'image', 'max:4096'],
            'selfie'         => ['required', 'image', 'max:4096'],
        ]);

        $seller = DB::transaction(function () use ($request, $user, $data) {
            $company = Company::create([
                'name'           => $data['shop_name'],
                'slug'           => $this->uniqueSlug($data['shop_name']),
                'description'    => $data['description'] ?? null,
                'cuit'           => $data['cuit'] ?? null,
                'cond_iva'       => $data['cond_iva'] ?? null,
                'social_network' => $data['social_network'] ?? null,
                'status'         => 'pending',
            ]);

            $user->company_id = $company->id;
            $user->dni        = $data['dni'] ?? null;
            $user->cbu        = $data['cbu'] ?? null;
            $user->dni_front  = $request->file('dni_front')->store('sellers/kyc', 'public');
            $user->dni_back   = $request->file('dni_back')->store('sellers/kyc', 'public');
            $user->selfie     = $request->file('selfie')->store('sellers/kyc', 'public');
            $user->status_seller_id = User::SELLER_PENDING;
            $user->save();

            return $user->load('company');
        });

        return (new SellerResource($seller))->additional(['can_sell' => $seller->canSell()]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'tienda';
        $slug = $base;
        $i = 1;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
