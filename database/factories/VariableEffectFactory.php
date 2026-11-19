<?php

namespace Database\Factories;

use App\Models\Choice;
use App\Models\Variable;
use App\Models\VariableEffect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VariableEffect>
 */
class VariableEffectFactory extends Factory
{
    /**
     * Estado por defecto: efecto de asignación sobre una variable.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'choice_id' => Choice::factory(),
            'variable_id' => Variable::factory(),
            'operation' => 'set',
            'value' => 1,
        ];
    }
}
