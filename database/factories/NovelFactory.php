<?php

namespace Database\Factories;

use App\Models\Novel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Novel>
 */
class NovelFactory extends Factory
{
    /**
     * Estado por defecto: novela en borrador perteneciente a un usuario nuevo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->text(200),
            'status' => 'draft',
            'cover_image' => null,
        ];
    }

    /**
     * Marca la novela como publicada para el catálogo público.
     */
    public function publicada(): static
    {
        return $this->state(fn (array $atributos) => ['status' => 'published']);
    }
}
