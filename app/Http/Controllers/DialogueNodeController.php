<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\DialogueNode;
use App\Models\Playthrough;
use App\Models\Scene;
use App\Services\ValidadorDeNodos;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Administra los nodos de diálogo de una escena, sus personajes y sus saltos.
 * Valida que las referencias pertenezcan a la novela y evita ciclos en la trama.
 */
class DialogueNodeController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ValidadorDeNodos $validadorDeNodos)
    {
    }

    /**
     * Lista los nodos de una escena junto con sus personajes y opciones.
     */
    public function index(Scene $scene): Response
    {
        $this->authorize('update', $scene->novel);

        return Inertia::render('DialogueNodes/Index', [
            'scene' => $scene,
            'nodes' => $scene->dialogueNodes()
                ->with(['character', 'expression', 'choices'])
                ->get(),
            'characters' => $scene->novel->characters()->with('expressions')->get(),
        ]);
    }

    /**
     * Crea un nodo en la escena, con personaje, expresión y salto validados.
     */
    public function store(Request $request, Scene $scene): RedirectResponse
    {
        $this->authorize('update', $scene->novel);

        $validados = $request->validate($this->reglas($scene));
        $this->validadorDeNodos->validarDestino($scene->id, 0, $validados['next_node_id'] ?? null);

        $scene->dialogueNodes()->create([
            ...$validados,
            'order' => $scene->dialogueNodes()->max('order') + 1,
        ]);

        return back();
    }

    /**
     * Actualiza un nodo, impidiendo que apunte a sí mismo o cree un ciclo.
     */
    public function update(Request $request, DialogueNode $dialogueNode): RedirectResponse
    {
        $this->authorize('update', $dialogueNode->scene->novel);

        $validados = $request->validate($this->reglas($dialogueNode->scene, $dialogueNode->id));
        $this->validadorDeNodos->validarDestino(
            $dialogueNode->scene_id,
            $dialogueNode->id,
            $validados['next_node_id'] ?? null,
            $dialogueNode->id
        );

        $dialogueNode->update($validados);

        return back();
    }

    /**
     * Elimina un nodo solo si ninguna partida, salto o efecto lo referencia.
     */
    public function destroy(DialogueNode $dialogueNode): RedirectResponse
    {
        $this->authorize('update', $dialogueNode->scene->novel);

        $enUso = Playthrough::where('current_node_id', $dialogueNode->id)->exists()
            || DialogueNode::where('next_node_id', $dialogueNode->id)->exists()
            || Choice::where('next_node_id', $dialogueNode->id)->exists()
            || $dialogueNode->choices()->whereHas('variableEffects')->exists();

        if ($enUso) {
            return back()->with('error', 'No se puede eliminar el nodo porque hay partidas, saltos o efectos que lo referencian.');
        }

        $dialogueNode->delete();

        return back();
    }

    /**
     * Construye las reglas con alcance de novela/escena para los campos del nodo.
     *
     * @return array<string, array<int, mixed>>
     */
    private function reglas(Scene $scene, ?int $nodoId = null): array
    {
        $novelId = $scene->novel_id;

        return [
            'character_id' => [
                'nullable',
                Rule::exists('characters', 'id')->where('novel_id', $novelId),
            ],
            'character_expression_id' => [
                'nullable',
                Rule::exists('character_expressions', 'id')->where(function ($consulta) use ($novelId) {
                    $consulta->whereIn('character_id', function ($subconsulta) use ($novelId) {
                        $subconsulta->select('id')->from('characters')->where('novel_id', $novelId);
                    });
                }),
            ],
            'text' => ['required', 'string'],
            'next_node_id' => [
                'nullable',
                Rule::exists('dialogue_nodes', 'id')->where(function ($consulta) use ($scene, $nodoId) {
                    $consulta->where('scene_id', $scene->id);

                    if ($nodoId !== null) {
                        $consulta->where('id', '!=', $nodoId);
                    }
                }),
            ],
        ];
    }
}
