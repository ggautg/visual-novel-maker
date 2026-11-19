<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestiona el perfil del usuario: visualización, actualización y borrado de cuenta.
 */
class ProfileController extends Controller
{
    /**
     * Muestra el formulario de perfil y el estado de verificación del correo.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Actualiza nombre y correo; si cambia el correo se invalida su verificación.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Valida la contraseña, borra los archivos del usuario y elimina su cuenta.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $usuario = $request->user();

        $novelas = $usuario->novels()->with(['scenes', 'characters.expressions'])->get();

        foreach ($novelas as $novel) {
            if ($novel->cover_image) {
                Storage::disk('public')->delete($novel->cover_image);
            }

            foreach ($novel->scenes as $scene) {
                if ($scene->background_image) {
                    Storage::disk('public')->delete($scene->background_image);
                }

                if ($scene->background_music) {
                    Storage::disk('public')->delete($scene->background_music);
                }
            }

            foreach ($novel->characters as $character) {
                foreach ($character->expressions as $expression) {
                    Storage::disk('public')->delete($expression->image_path);
                }
            }
        }

        Auth::logout();

        $usuario->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
