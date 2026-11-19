<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\PlaythroughVariableValue;
use App\Models\Variable;
use App\Models\VariableEffect;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Administra las variables de una novela, que alimentan los efectos de las opciones.
 * No permite borrar variables usadas por efectos o por partidas en curso.
 */
class VariableController extends Controller
{
    use AuthorizesRequests;

    /**
     * Lista las variables de una novela propia.
     */
    public function index(Novel $novel): Response
    {
        $this->authorize('update', $novel);

        return Inertia::render('Variables/Index', [
            'novel' => $novel,
            'variables' => $novel->variables,
        ]);
    }

    /**
     * Crea una variable con clave única dentro de la novela.
     */
    public function store(Request $request, Novel $novel): RedirectResponse
    {
        $this->authorize('update', $novel);

        $validados = $request->validate([
            'key' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('variables')->where('novel_id', $novel->id),
            ],
            'label' => ['nullable', 'string', 'max:255'],
            'default_value' => ['required', 'integer'],
        ]);

        $novel->variables()->create($validados);

        return back();
    }

    /**
     * Actualiza una variable manteniendo la unicidad de clave en la novela.
     */
    public function update(Request $request, Variable $variable): RedirectResponse
    {
        $this->authorize('update', $variable->novel);

        $validados = $request->validate([
            'key' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('variables')->where('novel_id', $variable->novel_id)->ignore($variable->id),
            ],
            'label' => ['nullable', 'string', 'max:255'],
            'default_value' => ['required', 'integer'],
        ]);

        $variable->update($validados);

        return back();
    }

    /**
     * Elimina una variable solo si no la usan efectos ni partidas guardadas.
     */
    public function destroy(Variable $variable): RedirectResponse
    {
        $this->authorize('update', $variable->novel);

        if (VariableEffect::where('variable_id', $variable->id)->exists()
            || PlaythroughVariableValue::where('variable_id', $variable->id)->exists()) {
            return back()->with('error', 'No se puede eliminar la variable porque hay efectos o partidas que la referencian.');
        }

        $variable->delete();

        return back();
    }
}
