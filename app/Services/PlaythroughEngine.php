<?php

namespace App\Services;

use App\Models\Choice;
use App\Models\Playthrough;

class PlaythroughEngine
{
    /**
     * Avanza el playthrough. Si se pasa una $choice, aplica sus efectos
     * y salta a su next_node_id. Si no, sigue el next_node_id lineal
     * del nodo actual.
     */
    public function advance(Playthrough $playthrough, ?Choice $choice = null): Playthrough
    {
        $currentNode = $playthrough->currentNode;

        if ($choice) {
            $this->applyChoiceEffects($playthrough, $choice);
            $nextNodeId = $choice->next_node_id;
        } else {
            $nextNodeId = $currentNode?->next_node_id;
        }

        $playthrough->update(['current_node_id' => $nextNodeId]);

        return $playthrough->fresh();
    }

    protected function applyChoiceEffects(Playthrough $playthrough, Choice $choice): void
    {
        foreach ($choice->variableEffects as $effect) {
            $variableValue = $playthrough->variableValues()
                ->where('variable_id', $effect->variable_id)
                ->first();

            $newValue = match ($effect->operation) {
                'set' => $effect->value,
                'increment' => $variableValue->value + $effect->value,
                'decrement' => $variableValue->value - $effect->value,
            };

            $variableValue->update(['value' => $newValue]);
        }
    }
}