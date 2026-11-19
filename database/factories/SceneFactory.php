<?php

namespace Database\Factories;

use App\Models\Novel;
use App\Models\Scene;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Scene>
 */
class SceneFactory extends Factory
{
    /**
     * Estado por defecto: escena sencilla sin archivos de fondo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'novel_id' => Novel::factory(),
            'title' => fake()->words(2, true),
            'background_image' => null,
            'background_music' => null,
            'order' => 0,
        ];
    }
}
