<?php

namespace App\Http\Controllers\Api\V1;

use App\Company;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
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

    /** Mi tienda (la company del vendedor autenticado). */
    public function shop(Request $request)
    {
        $company = $this->ownCompany($request);
        return new CompanyResource($company->loadCount(['products', 'sellers']));
    }

    /** Actualizar datos de mi tienda. */
    public function updateShop(Request $request)
    {
        $company = $this->ownCompany($request);
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'cuit'           => ['nullable', 'string', 'max:20'],
            'cond_iva'       => ['nullable', 'string', 'max:60'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:40'],
            'address'        => ['nullable', 'string', 'max:255'],
            'social_network' => ['nullable', 'string', 'max:255'],
        ]);
        $company->update($data);
        return new CompanyResource($company);
    }

    public function uploadShopLogo(Request $request)
    {
        $company = $this->ownCompany($request);
        $request->validate(['logo' => ['required', 'image', 'max:2048']]);
        $company->update(['logo' => $request->file('logo')->store('companies/logos', 'public')]);
        return new CompanyResource($company);
    }

    public function uploadShopBanner(Request $request)
    {
        $company = $this->ownCompany($request);
        $request->validate(['banner' => ['required', 'image', 'max:4096']]);
        $company->update(['banner' => $request->file('banner')->store('companies/banners', 'public')]);
        return new CompanyResource($company);
    }

    /** Devuelve la tienda del vendedor o 403/404 si no corresponde. */
    private function ownCompany(Request $request): Company
    {
        $user = $request->user();
        abort_unless($user->company_id, 404, 'No tenés una tienda.');
        return Company::findOrFail($user->company_id);
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
