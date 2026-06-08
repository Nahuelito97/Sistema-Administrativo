<?php

namespace Database\Seeders;

use App\Company;
use App\Product;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CompaniesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Rol Vendedor: gestiona SU tienda, SUS productos y ve SUS ventas/órdenes.
        // El scope "solo lo suyo" se aplica en los controllers (F-MP3); acá quedan los permisos.
        $vendedor = Role::firstOrCreate(['name' => 'Vendedor', 'guard_name' => 'web']);
        $sellerPermissions = [
            'products.index', 'products.show', 'products.create', 'products.edit', 'products.destroy',
            'change.status.products',
            'companies.show', 'companies.edit',
            'orders.index', 'orders.show', 'orders.edit',
            'questions.index', 'questions.answer',
            'reports.day', 'reports.date',
        ];
        $vendedor->syncPermissions(
            Permission::whereIn('name', $sellerPermissions)->get()
        );

        // Tienda por defecto: aloja todo el catálogo single-store existente.
        $default = Company::firstOrCreate(
            ['slug' => 'ordasi'],
            [
                'name'        => 'Ordasi',
                'description' => 'Tienda oficial Ordasi.',
                'status'      => 'active',
            ]
        );

        // Backfill: productos y usuarios sin tienda quedan bajo la tienda por defecto.
        Product::whereNull('company_id')->update(['company_id' => $default->id]);
        User::whereNull('company_id')->update(['company_id' => $default->id]);
    }
}
