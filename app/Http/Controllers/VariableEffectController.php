<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\VariableEffect;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Administra los efectos que una opción aplica sobre las variables de su novela.
 * Solo se pueden seleccionar variables pertenecientes a la misma novela.
 */
class VariableEffectController extends Controller
{
    use AuthorizesRequests;

    /**
     * Añade un efecto a una opción, validando la variable y la operación.
     */
    public function store(Request $request, Choice $choice): RedirectResponse
    {
        $novel = $choice->dialogueNode->scene->novel;
        $this->authorize('update', $novel);

        $validados = $request->validate([
            'variable_id' => [
                'required',
                Rule::exists('variables', 'id')->where('novel_id', $novel->id),
            ],
            'operation' => ['required', 'in:set,increment,decrement'],
            'value' => ['required', 'integer'],
        ]);

        $choice->variableEffects()->create($validados);

        return back();
    }

    /**
     * Actualiza un efecto manteniendo la variable dentro de la misma novela.
     */
    public function update(Request $request, VariableEffect $variableEffect): RedirectResponse
    {
        $novel = $variableEffect->choice->dialogueNode->scene->novel;
        $this->authorize('update', $novel);

        $validados = $request->validate([
            'variable_id' => [
                'required',
                Rule::exists('variables', 'id')->where('novel_id', $novel->id),
            ],
            'operation' => ['required', 'in:set,increment,decrement'],
            'value' => ['required', 'integer'],
        ]);

        $variableEffect->update($validados);

        return back();
    }

    /**
     * Elimina un efecto de una opción propia.
     */
    public function destroy(VariableEffect $variableEffect): RedirectResponse
    {
        $novel = $variableEffect->choice->dialogueNode->scene->novel;
        $this->authorize('update', $novel);

        $variableEffect->delete();

        return back();
    }
}
