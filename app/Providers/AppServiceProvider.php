<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

/**
 * Registra y arranca los servicios comunes de la aplicación.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registra los enlaces del contenedor de servicios.
     */
    public function register(): void
    {
        //
    }

    /**
     * Configura el prefetch de Vite al arrancar la aplicación.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
