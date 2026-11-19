<?php

namespace Database\Factories;

use App\Models\Novel;
use App\Models\Variable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Variable>
 */
class VariableFactory extends Factory
{
    /**
     * Estado por defecto: variable entera con valor inicial cero.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'novel_id' => Novel::factory(),
            'key' => fake()->unique()->slug(2),
            'label' => fake()->words(2, true),
            'default_value' => 0,
        ];
    }
}
