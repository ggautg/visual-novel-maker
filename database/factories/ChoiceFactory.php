<?php

namespace Database\Factories;

use App\Models\Choice;
use App\Models\DialogueNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Choice>
 */
class ChoiceFactory extends Factory
{
    /**
     * Estado por defecto: opción de texto sin nodo destino.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dialogue_node_id' => DialogueNode::factory(),
            'text' => fake()->sentence(4),
            'next_node_id' => null,
            'order' => 0,
        ];
    }
}
