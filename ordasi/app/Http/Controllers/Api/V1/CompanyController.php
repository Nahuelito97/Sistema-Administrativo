<?php

namespace App\Http\Controllers\Api\V1;

use App\Company;
use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:companies.index')->only(['index', 'show']);
        $this->middleware('can:companies.create')->only(['store']);
        $this->middleware('can:companies.edit')->only(['update', 'uploadLogo', 'uploadBanner']);
        $this->middleware('can:companies.destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Company::query()
            ->search($request->query('search'))
            ->withCount(['products', 'sellers']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return CompanyResource::collection($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        return new CompanyResource(Company::create($data));
    }

    public function show(Company $company)
    {
        return new CompanyResource($company->loadCount(['products', 'sellers']));
    }

    public function update(Request $request, Company $company)
    {
        $data = $this->validateData($request, $company->id);
        if ($data['name'] !== $company->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $company->id);
        }
        $company->update($data);
        return new CompanyResource($company);
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return response()->json(null, 204);
    }

    public function uploadLogo(Request $request, Company $company)
    {
        $request->validate(['logo' => ['required', 'image', 'max:2048']]);
        $path = $request->file('logo')->store('companies/logos', 'public');
        $company->update(['logo' => $path]);
        return new CompanyResource($company);
    }

    public function uploadBanner(Request $request, Company $company)
    {
        $request->validate(['banner' => ['required', 'image', 'max:4096']]);
        $path = $request->file('banner')->store('companies/banners', 'public');
        $company->update(['banner' => $path]);
        return new CompanyResource($company);
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'cuit'           => ['nullable', 'string', 'max:20'],
            'cond_iva'       => ['nullable', 'string', 'max:60'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:40'],
            'address'        => ['nullable', 'string', 'max:255'],
            'social_network' => ['nullable', 'string', 'max:255'],
            'status'         => ['nullable', Rule::in(['active', 'pending', 'inactive'])],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Company::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
