<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Rol Admin con todos los permisos (equivalente al "all-access" de shinobi)
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $user = User::firstOrCreate(
            ['email' => 'nahuelito@gmail.com'],
            [
                'name'     => 'Nahuel',
                'password' => '12345', // se hashea solo por el cast 'hashed'
            ]
        );

        $user->assignRole($admin);
    }
}
