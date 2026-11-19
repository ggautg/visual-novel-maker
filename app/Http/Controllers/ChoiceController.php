<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\DialogueNode;
use App\Models\Scene;
use App\Services\ValidadorDeNodos;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Administra las opciones de los nodos de diálogo y comprueba sus saltos.
 * Los destinos se limitan a la escena de la opción y no pueden formar ciclos.
 */
class ChoiceController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ValidadorDeNodos $validadorDeNodos)
    {
    }

    /**
     * Lista las opciones de un nodo con sus efectos y los nodos destino posibles.
     */
    public function index(DialogueNode $dialogueNode): Response
    {
        $novel = $dialogueNode->scene->novel;
        $this->authorize('update', $novel);

        return Inertia::render('Choices/Index', [
            'node' => $dialogueNode,
            'choices' => $dialogueNode->choices()->with('variableEffects')->get(),
            'allNodes' => $dialogueNode->scene->dialogueNodes()->get(['id', 'text']),
            'variables' => $novel->variables,
        ]);
    }

    /**
     * Crea una opción cuyo destino pertenece a la escena y no forma ciclo.
     */
    public function store(Request $request, DialogueNode $dialogueNode): RedirectResponse
    {
        $this->authorize('update', $dialogueNode->scene->novel);

        $validados = $request->validate($this->reglas($dialogueNode->scene));
        $this->validadorDeNodos->validarDestino(
            $dialogueNode->scene_id,
            $dialogueNode->id,
            $validados['next_node_id'] ?? null
        );

        $dialogueNode->choices()->create([
            ...$validados,
            'order' => $dialogueNode->choices()->max('order') + 1,
        ]);

        return back();
    }

    /**
     * Actualiza una opción, validando de nuevo su destino.
     */
    public function update(Request $request, Choice $choice): RedirectResponse
    {
        $this->authorize('update', $choice->dialogueNode->scene->novel);

        $nodo = $choice->dialogueNode;
        $validados = $request->validate($this->reglas($nodo->scene));
        $this->validadorDeNodos->validarDestino(
            $nodo->scene_id,
            $nodo->id,
            $validados['next_node_id'] ?? null,
            null,
            $choice->id
        );

        $choice->update($validados);

        return back();
    }

    /**
     * Elimina una opción de un nodo propio.
     */
    public function destroy(Choice $choice): RedirectResponse
    {
        $this->authorize('update', $choice->dialogueNode->scene->novel);

        $choice->delete();

        return back();
    }

    /**
     * Reglas para el texto y el nodo destino dentro de la misma escena.
     *
     * @return array<string, array<int, mixed>>
     */
    private function reglas(Scene $scene): array
    {
        return [
            'text' => ['required', 'string', 'max:255'],
            'next_node_id' => [
                'nullable',
                Rule::exists('dialogue_nodes', 'id')->where('scene_id', $scene->id),
            ],
        ];
    }
}
