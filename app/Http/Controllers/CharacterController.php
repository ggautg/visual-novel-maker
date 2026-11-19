<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Novel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Administra los personajes de una novela y sus expresiones.
 */
class CharacterController extends Controller
{
    use AuthorizesRequests;

    /**
     * Lista los personajes de una novela propia con sus expresiones.
     */
    public function index(Novel $novel): Response
    {
        $this->authorize('update', $novel);

        return Inertia::render('Characters/Index', [
            'novel' => $novel,
            'characters' => $novel->characters()->with('expressions')->get(),
        ]);
    }

    /**
     * Crea un personaje con nombre y color opcional.
     */
    public function store(Request $request, Novel $novel): RedirectResponse
    {
        $this->authorize('update', $novel);

        $validados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $novel->characters()->create($validados);

        return back();
    }

    /**
     * Actualiza los datos de un personaje propio.
     */
    public function update(Request $request, Character $character): RedirectResponse
    {
        $this->authorize('update', $character->novel);

        $validados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $character->update($validados);

        return back();
    }

    /**
     * Elimina un personaje propio.
     */
    public function destroy(Character $character): RedirectResponse
    {
        $this->authorize('update', $character->novel);

        $character->delete();

        return back();
    }
}
