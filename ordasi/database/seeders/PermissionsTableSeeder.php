<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Listado de permisos del sistema.
     * El "name" de spatie = el antiguo "slug" de shinobi, para que
     * los middleware can:xxx de las rutas/controllers sigan funcionando.
     */
    public function run(): void
    {
        $permissions = [
            // Usuarios
            'users.index', 'users.create', 'users.show', 'users.edit', 'users.destroy',
            // Roles
            'roles.index', 'roles.show', 'roles.create', 'roles.edit', 'roles.destroy',
            // Categorías
            'categories.index', 'categories.show', 'categories.edit', 'categories.create', 'categories.destroy',
            // Subcategorías
            'subcategories.index', 'subcategories.show', 'subcategories.edit', 'subcategories.create', 'subcategories.destroy',
            // Marcas
            'brands.index', 'brands.show', 'brands.edit', 'brands.create', 'brands.destroy',
            // Promociones
            'promotions.index', 'promotions.show', 'promotions.edit', 'promotions.create', 'promotions.destroy',
            // Clientes
            'clients.index', 'clients.show', 'clients.edit', 'clients.create', 'clients.destroy',
            // Productos
            'products.index', 'products.show', 'products.edit', 'products.create', 'products.destroy',
            // Proveedores
            'providers.index', 'providers.show', 'providers.edit', 'providers.create', 'providers.destroy',
            // Compras
            'purchases.index', 'purchases.show', 'purchases.create',
            // Ventas
            'sales.index', 'sales.show', 'sales.create', 'sales.print',
            // Órdenes (e-commerce)
            'orders.index', 'orders.show', 'orders.edit',
            // PDFs
            'purchases.pdf', 'sales.pdf',
            // Empresa
            'business.index', 'business.edit',
            // Acciones varias
            'upload.purchases',
            'change.status.products', 'change.status.purchases', 'change.status.sales',
            // Reportes
            'reports.day', 'reports.date',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }
}
