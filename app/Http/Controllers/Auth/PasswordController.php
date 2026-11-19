<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Permite al usuario autenticado cambiar su contraseña.
 */
class PasswordController extends Controller
{
    /**
     * Comprueba la contraseña actual y guarda la nueva.
     */
    public function update(Request $request): RedirectResponse
    {
        $validados = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validados['password']),
        ]);

        return back();
    }
}
