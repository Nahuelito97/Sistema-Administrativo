<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Ruta a la que se redirige tras el login.
     * Laravel 11 eliminó este provider; lo conservamos solo por la
     * constante HOME que usan los controllers de autenticación (laravel/ui).
     */
    public const HOME = '/home';
}
