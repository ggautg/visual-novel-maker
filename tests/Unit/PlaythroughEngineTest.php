<?php

namespace Tests\Unit;

use App\Models\Choice;
use App\Models\DialogueNode;
use App\Models\Novel;
use App\Models\Playthrough;
use App\Models\Scene;
use App\Models\User;
use App\Models\Variable;
use App\Models\VariableEffect;
use App\Services\PlaythroughEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Comprueba el cálculo de efectos y la creación segura de valores de variable.
 */
class PlaythroughEngineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crea una partida con un nodo, una opción y una variable sin valor previo.
     *
     * @return array{partida: Playthrough, opcion: Choice, variable: Variable}
     */
    private function prepararPartida(int $valorInicial): array
    {
        $usuario = User::factory()->create();
        $novel = Novel::factory()->for($usuario)->create();
        $escena = Scene::factory()->for($novel)->create();
        $nodo = DialogueNode::factory()->for($escena)->create();
        $opcion = Choice::factory()->for($nodo)->create();
        $variable = Variable::factory()->for($novel)->create([
            'key' => 'afecto',
            'default_value' => $valorInicial,
        ]);
        $partida = Playthrough::factory()
            ->for($usuario)
            ->for($novel)
            ->create(['current_node_id' => $nodo->id]);

        return [
            'partida' => $partida,
            'opcion' => $opcion,
            'variable' => $variable,
        ];
    }

    /**
     * Comprueba que set, increment y decrement actualizan el valor guardado.
     */
    public function test_aplica_set_increment_y_decrement(): void
    {
        $datos = $this->prepararPartida(10);
        $engine = new PlaythroughEngine();

        VariableEffect::factory()->for($datos['opcion'])->for($datos['variable'])->create([
            'operation' => 'set',
            'value' => 4,
        ]);
        $engine->advance($datos['partida']->fresh(), $datos['opcion']->fresh());

        $this->assertDatabaseHas('playthrough_variable_values', [
            'playthrough_id' => $datos['partida']->id,
            'variable_id' => $datos['variable']->id,
            'value' => 4,
        ]);

        VariableEffect::factory()->for($datos['opcion'])->for($datos['variable'])->create([
            'operation' => 'increment',
            'value' => 3,
        ]);
        // La opción no apunta a otro nodo: devolvemos la partida al nodo de origen
        // para poder comprobar cada operación de forma aislada.
        $partida = $datos['partida']->fresh();
        $partida->update(['current_node_id' => $datos['opcion']->dialogue_node_id]);
        $engine->advance($partida, $datos['opcion']->fresh());

        $this->assertDatabaseHas('playthrough_variable_values', [
            'playthrough_id' => $datos['partida']->id,
            'variable_id' => $datos['variable']->id,
            'value' => 7,
        ]);

        VariableEffect::factory()->for($datos['opcion'])->for($datos['variable'])->create([
            'operation' => 'decrement',
            'value' => 2,
        ]);
        $partida = $datos['partida']->fresh();
        $partida->update(['current_node_id' => $datos['opcion']->dialogue_node_id]);
        $engine->advance($partida, $datos['opcion']->fresh());

        $this->assertDatabaseHas('playthrough_variable_values', [
            'playthrough_id' => $datos['partida']->id,
            'variable_id' => $datos['variable']->id,
            'value' => 5,
        ]);
    }

    /**
     * Una variable sin fila en la partida se crea con su valor por defecto.
     */
    public function test_crea_valor_faltante_con_el_valor_por_defecto(): void
    {
        $datos = $this->prepararPartida(5);

        $this->assertDatabaseCount('playthrough_variable_values', 0);

        VariableEffect::factory()->for($datos['opcion'])->for($datos['variable'])->create([
            'operation' => 'increment',
            'value' => 2,
        ]);

        (new PlaythroughEngine())->advance($datos['partida']->fresh(), $datos['opcion']->fresh());

        $this->assertDatabaseCount('playthrough_variable_values', 1);
        $this->assertDatabaseHas('playthrough_variable_values', [
            'playthrough_id' => $datos['partida']->id,
            'variable_id' => $datos['variable']->id,
            'value' => 7,
        ]);
    }

    /**
     * Una operación desconocida lanza una excepción clara en español.
     */
    public function test_rechaza_operacion_desconocida(): void
    {
        $datos = $this->prepararPartida(0);

        // La tabla solo permite operaciones válidas, así que creamos una efecto
        // correcto y simulamos en memoria un valor corrupto para probar el motor.
        $efecto = VariableEffect::factory()->for($datos['opcion'])->for($datos['variable'])->create([
            'operation' => 'set',
            'value' => 2,
        ]);
        $efecto->operation = 'multiplicar';

        $opcion = $datos['opcion']->fresh();
        $opcion->setRelation('variableEffects', collect([$efecto]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Operación desconocida');

        (new PlaythroughEngine())->advance($datos['partida']->fresh(), $opcion);
    }
}
