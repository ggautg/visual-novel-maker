<?php

namespace Database\Factories;

use App\Models\DialogueNode;
use App\Models\Scene;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DialogueNode>
 */
class DialogueNodeFactory extends Factory
{
    /**
     * Estado por defecto: nodo de texto sin personaje ni salto asignado.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scene_id' => Scene::factory(),
            'character_id' => null,
            'character_expression_id' => null,
            'text' => fake()->sentence(),
            'next_node_id' => null,
            'order' => 0,
        ];
    }
}
