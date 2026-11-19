<?php

namespace Database\Factories;

use App\Models\Novel;
use App\Models\Playthrough;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Playthrough>
 */
class PlaythroughFactory extends Factory
{
    /**
     * Estado por defecto: partida sin nodo actual todavía asignado.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'novel_id' => Novel::factory(),
            'current_node_id' => null,
        ];
    }
}
