<?php

namespace App\Services;

use App\Models\DialogueNode;
use Illuminate\Validation\ValidationException;

/**
 * Comprueba que un enlace entre nodos no apunte al propio nodo ni cree un ciclo,
 * siguiendo tanto los saltos lineales como los de las opciones de la escena.
 */
class ValidadorDeNodos
{
    /**
     * Valida el nodo destino antes de guardar: rechaza el propio nodo y los ciclos simples.
     * Los parámetros de exclusión evitan que el valor antiguo que se está cambiando
     * cuente como un ciclo que la edición va a eliminar.
     *
     * @throws ValidationException si el destino es el propio nodo o vuelve a él
     */
    public function validarDestino(
        int $escenaId,
        int $origenId,
        ?int $destinoId,
        ?int $excluirNodoId = null,
        ?int $excluirOpcionId = null
    ): void {
        if ($destinoId === null) {
            return;
        }

        if ($destinoId === $origenId) {
            throw ValidationException::withMessages([
                'next_node_id' => 'Un nodo no puede apuntarse a sí mismo.',
            ]);
        }

        $nodos = DialogueNode::where('scene_id', $escenaId)->with('choices')->get(['id', 'next_node_id']);
        $saltos = [];

        foreach ($nodos as $nodo) {
            $destinos = [$nodo->id === $excluirNodoId ? null : $nodo->next_node_id];

            foreach ($nodo->choices as $opcion) {
                $destinos[] = $opcion->id === $excluirOpcionId ? null : $opcion->next_node_id;
            }

            $saltos[$nodo->id] = array_values(array_filter(
                $destinos,
                fn ($id) => $id !== null
            ));
        }

        $pendientes = [$destinoId];
        $visitados = [];

        while ($pendientes !== []) {
            $actual = array_pop($pendientes);

            if ($actual === $origenId) {
                throw ValidationException::withMessages([
                    'next_node_id' => 'El destino elegido crea un ciclo y dejaría la historia atrapada.',
                ]);
            }

            if (isset($visitados[$actual])) {
                continue;
            }

            $visitados[$actual] = true;

            foreach ($saltos[$actual] ?? [] as $siguiente) {
                $pendientes[] = $siguiente;
            }
        }
    }
}
