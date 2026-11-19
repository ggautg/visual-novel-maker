<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Comparte con todas las páginas Inertia los datos del usuario autenticado.
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * Plantilla raíz que se carga en la primera petición.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Devuelve la versión del frontend para invalidar la caché al desplegar.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Añade los datos de autenticación a las propiedades comunes de Inertia.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
        ];
    }
}
