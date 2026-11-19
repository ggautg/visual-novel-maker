<?php

namespace App\Services;

use App\Models\Choice;
use App\Models\DialogueNode;
use App\Models\Playthrough;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Ejecuta la lógica de avance de una partida: valida la opción elegida,
 * aplica sus efectos sobre las variables y mueve el nodo actual.
 */
class PlaythroughEngine
{
    /**
     * Avanza la partida con la opción indicada o, si no hay ninguna, con el
     * siguiente nodo lineal. Valida que la opción pertenezca al nodo actual
     * y a la novela de la partida. Devuelve la partida ya actualizada.
     *
     * @throws ValidationException cuando la opción no es válida para el nodo actual
     */
    public function advance(Playthrough $playthrough, ?Choice $choice = null): Playthrough
    {
        $nodoActual = $playthrough->currentNode;
        $tieneOpciones = $nodoActual !== null && $nodoActual->choices()->exists();

        if ($choice !== null) {
            $opcionValida = $nodoActual !== null
                && $choice->dialogue_node_id === $nodoActual->id
                && $nodoActual->choices()->whereKey($choice->id)->exists()
                && $this->nodoPerteneceANovela($nodoActual->id, $playthrough->novel_id)
                && ($choice->next_node_id === null
                    || $this->nodoPerteneceANovela($choice->next_node_id, $playthrough->novel_id));

            if (! $opcionValida) {
                throw ValidationException::withMessages([
                    'choice_id' => 'La opción elegida no pertenece al nodo actual de la partida.',
                ]);
            }
        } elseif ($tieneOpciones) {
            throw ValidationException::withMessages([
                'choice_id' => 'El nodo actual tiene opciones: debes elegir una para continuar.',
            ]);
        }

        if ($choice !== null) {
            $this->applyChoiceEffects($playthrough, $choice);
            $siguienteNodoId = $choice->next_node_id;
        } else {
            $siguienteNodoId = $nodoActual?->next_node_id;
        }

        $playthrough->update(['current_node_id' => $siguienteNodoId]);

        return $playthrough->fresh();
    }

    /**
     * Aplica los efectos de una opción, creando la fila de variable con su
     * valor por defecto cuando todavía no existe en la partida.
     *
     * @throws InvalidArgumentException cuando la operación del efecto no es conocida
     */
    protected function applyChoiceEffects(Playthrough $playthrough, Choice $choice): void
    {
        foreach ($choice->variableEffects as $effect) {
            $valorVariable = $playthrough->variableValues()->firstOrCreate(
                ['variable_id' => $effect->variable_id],
                ['value' => $effect->variable?->default_value ?? 0]
            );

            $nuevoValor = match ($effect->operation) {
                'set' => (int) $effect->value,
                'increment' => (int) $valorVariable->value + (int) $effect->value,
                'decrement' => (int) $valorVariable->value - (int) $effect->value,
                default => throw new InvalidArgumentException(
                    "Operación desconocida «{$effect->operation}» en el efecto de variable {$effect->id}."
                ),
            };

            $valorVariable->update(['value' => $nuevoValor]);
        }
    }

    /**
     * Comprueba que un nodo pertenezca a la novela indicada a través de su escena.
     */
    protected function nodoPerteneceANovela(int $nodoId, int $novelId): bool
    {
        return DialogueNode::whereKey($nodoId)
            ->whereHas('scene', fn ($consulta) => $consulta->where('novel_id', $novelId))
            ->exists();
    }
}
