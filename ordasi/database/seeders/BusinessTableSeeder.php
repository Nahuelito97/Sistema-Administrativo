<?php

namespace Database\Seeders;

use App\Business;
use Illuminate\Database\Seeder;

class BusinessTableSeeder extends Seeder
{
    public function run(): void
    {
        Business::firstOrCreate(['id' => 1], [
            'name'        => 'Nombre de la empresa.',
            'description' => 'Descripción corta de la empresa.',
            'logo'        => 'logo.png',
            'mail'        => 'Ejemplo@gmail.com',
            'address'     => '8888 Cummings Vista Apt. 101, Susanbury, NY 95473',
            'ruc'         => '15247895632',
        ]);
    }
}
