<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\DialogueNode;
use App\Models\Novel;
use App\Models\Playthrough;
use App\Models\User;
use App\Services\PlaythroughEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controla la lectura y el avance de las partidas de novelas.
 * Verifica la propiedad de la partida y la coherencia de las opciones elegidas.
 */
class PlaythroughController extends Controller
{
    /**
     * Obtiene o crea la partida del usuario para una novela publicada o propia.
     */
    public function show(Novel $novel): Response
    {
        Gate::authorize('view', $novel);

        $partida = DB::transaction(fn () => Playthrough::firstOrCreateFor(Auth::id(), $novel->id));

        return $this->renderPlaythrough($partida);
    }

    /**
     * Avanza una partida propia dentro de una transacción.
     * Exige una opción cuando el nodo la tiene y la rechaza cuando no la tiene;
     * comprueba además que la opción y su destino pertenezcan a la misma novela.
     */
    public function advance(Request $request, Playthrough $playthrough, PlaythroughEngine $engine): Response
    {
        $partida = DB::transaction(function () use ($request, $playthrough, $engine) {
            $bloqueada = Playthrough::whereKey($playthrough->id)->lockForUpdate()->firstOrFail();

            abort_unless($bloqueada->user_id === Auth::id(), 403, 'No tienes permiso para avanzar esta partida.');

            $nodoActual = $bloqueada->currentNode;
            $tieneOpciones = $nodoActual !== null && $nodoActual->choices()->exists();
            $choiceId = $request->input('choice_id');

            if ($tieneOpciones && blank($choiceId)) {
                abort(422, 'El nodo actual tiene opciones: debes elegir una para continuar.');
            }

            if (! $tieneOpciones && filled($choiceId)) {
                abort(422, 'El nodo actual no admite opciones.');
            }

            $opcion = null;
            if (filled($choiceId)) {
                $opcion = Choice::whereKey($choiceId)
                    ->where('dialogue_node_id', $bloqueada->current_node_id)
                    ->whereHas('dialogueNode', fn ($consulta) => $consulta->whereHas(
                        'scene',
                        fn ($escena) => $escena->where('novel_id', $bloqueada->novel_id)
                    ))
                    ->first();

                if ($opcion !== null && $opcion->next_node_id !== null) {
                    $destinoValido = DialogueNode::whereKey($opcion->next_node_id)
                        ->whereHas('scene', fn ($escena) => $escena->where('novel_id', $bloqueada->novel_id))
                        ->exists();

                    if (! $destinoValido) {
                        $opcion = null;
                    }
                }

                if ($opcion === null) {
                    abort(422, 'La opción elegida no pertenece al nodo actual ni a esta novela.');
                }
            }

            return $engine->advance($bloqueada, $opcion);
        });

        return $this->renderPlaythrough($partida);
    }

    /**
     * Prepara los datos que consume la vista de juego.
     */
    protected function renderPlaythrough(Playthrough $playthrough): Response
    {
        $playthrough->load([
            'novel' => fn ($consulta) => $consulta->withExists([
                'likedByUsers as is_liked_by_me' => fn ($q) => $q->where('user_id', Auth::id()),
            ]),
            'currentNode.character',
            'currentNode.expression',
            'currentNode.choices',
            'variableValues.variable',
        ]);

        $nodoActual = $playthrough->currentNode;

        return Inertia::render('Play/Show', [
            'playthrough' => $playthrough,
            'novel' => $playthrough->novel,
            'scene' => $nodoActual?->scene,
            'node' => $nodoActual,
            'isFinished' => $nodoActual === null,
        ]);
    }

    /**
     * Reinicia la partida desde el primer nodo, evitando duplicados por doble clic.
     */
    public function restart(Novel $novel): Response
    {
        Gate::authorize('view', $novel);

        $partida = DB::transaction(function () use ($novel) {
            User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();

            Playthrough::where('user_id', Auth::id())
                ->where('novel_id', $novel->id)
                ->lockForUpdate()
                ->delete();

            return Playthrough::startFor(Auth::id(), $novel->id);
        });

        return $this->renderPlaythrough($partida);
    }
}
