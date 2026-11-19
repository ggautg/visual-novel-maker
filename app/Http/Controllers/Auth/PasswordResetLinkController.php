<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Envía el enlace para restablecer la contraseña por correo electrónico.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Muestra el formulario de recuperación de contraseña.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Solicita el enlace de restablecimiento y devuelve el estado traducido.
     *
     * @throws ValidationException si el correo no es válido
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $estado = Password::sendResetLink(
            $request->only('email')
        );

        if ($estado === Password::RESET_LINK_SENT) {
            return back()->with('status', __($estado));
        }

        throw ValidationException::withMessages([
            'email' => [trans($estado)],
        ]);
    }
}
