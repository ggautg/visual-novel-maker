<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterExpression;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Gestiona las imágenes de expresión de los personajes de una novela.
 */
class CharacterExpressionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Guarda una expresión con su imagen en el disco público.
     */
    public function store(Request $request, Character $character): RedirectResponse
    {
        $this->authorize('update', $character->novel);

        $validados = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $ruta = $request->file('image')->store('expressions', 'public');

        $character->expressions()->create([
            'name' => $validados['name'],
            'image_path' => $ruta,
        ]);

        return back();
    }

    /**
     * Elimina una expresión y su fichero de imagen.
     */
    public function destroy(CharacterExpression $expression): RedirectResponse
    {
        $this->authorize('update', $expression->character->novel);

        $ruta = $expression->image_path;
        $expression->delete();
        Storage::disk('public')->delete($ruta);

        return back();
    }
}
