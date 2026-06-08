<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Acota recursos a la tienda del usuario cuando es Vendedor.
 * El Admin (all-access) ve todo; el Vendedor sólo lo de su company_id.
 */
trait ScopesToSeller
{
    /** company_id al que limitar, o null si es Admin (ve todo). */
    protected function sellerCompanyId(Request $request): ?int
    {
        $user = $request->user();
        return $user->hasRole('Admin') ? null : $user->company_id;
    }

    protected function scopeOwned(Builder $query, Request $request, string $column = 'company_id'): Builder
    {
        $companyId = $this->sellerCompanyId($request);
        return $companyId ? $query->where($column, $companyId) : $query;
    }

    protected function assertOwned(Model $model, Request $request, string $column = 'company_id'): void
    {
        $companyId = $this->sellerCompanyId($request);
        abort_if($companyId !== null && (int) $model->{$column} !== $companyId, 403, 'No tenés acceso a este recurso.');
    }
}
