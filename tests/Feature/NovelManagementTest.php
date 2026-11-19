<?php

namespace Tests\Feature;

use App\Models\Choice;
use App\Models\DialogueNode;
use App\Models\Novel;
use App\Models\Playthrough;
use App\Models\Scene;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre la gestión de novelas, etiquetas, likes y la validación de opciones de juego.
 */
class NovelManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Crear una novela desde el formulario debe guardarla y asociarla al autor.
     */
    public function test_store_crea_la_novela_del_usuario_autenticado(): void
    {
        $usuario = User::factory()->create();

        $respuesta = $this->actingAs($usuario)->post(route('novels.store'), [
            'title' => 'La ciudad sumergida',
            'description' => 'Una historia de mares secretos.',
        ]);

        $respuesta->assertRedirect(route('novels.index'));

        $this->assertDatabaseHas('novels', [
            'user_id' => $usuario->id,
            'title' => 'La ciudad sumergida',
            'description' => 'Una historia de mares secretos.',
        ]);
    }

    /**
     * Un usuario distinto del autor no puede abrir el formulario de edición.
     */
    public function test_otro_usuario_no_puede_editar_la_novela(): void
    {
        $autor = User::factory()->create();
        $novel = Novel::factory()->for($autor)->create();
        $intruso = User::factory()->create();

        $this->actingAs($intruso)
            ->get(route('novels.edit', $novel))
            ->assertForbidden();
    }

    /**
     * Actualizar sin enviar el campo tags no debe borrar las etiquetas actuales.
     */
    public function test_las_etiquetas_se_conservan_si_no_se_envian(): void
    {
        $usuario = User::factory()->create();
        $novel = Novel::factory()->for($usuario)->create();
        $etiqueta = Tag::firstOrCreate(['name' => 'fantasia']);
        $novel->tags()->attach($etiqueta);

        $this->actingAs($usuario)->put(route('novels.update', $novel), [
            'title' => 'Título cambiado',
            'description' => 'Descripción cambiada.',
            'status' => 'draft',
        ])->assertRedirect(route('novels.index'));

        $this->assertCount(1, $novel->fresh()->tags);
        $this->assertDatabaseHas('novel_tag', [
            'novel_id' => $novel->id,
            'tag_id' => $etiqueta->id,
        ]);
    }

    /**
     * El like alterna entre añadir y quitar sin duplicados ni errores.
     */
    public function test_like_es_idempotente_y_alterna_estado(): void
    {
        $usuario = User::factory()->create();
        $novel = Novel::factory()->for($usuario)->create(['status' => 'published']);

        $this->actingAs($usuario)->post(route('novels.like', $novel))->assertRedirect();
        $this->assertDatabaseCount('novel_likes', 1);

        $this->actingAs($usuario)->post(route('novels.like', $novel))->assertRedirect();
        $this->assertDatabaseCount('novel_likes', 0);

        $this->actingAs($usuario)->post(route('novels.like', $novel))->assertRedirect();
        $this->assertDatabaseCount('novel_likes', 1);
    }

    /**
     * Una opción que pertenece a otra novela o nodo debe rechazarse con 422.
     */
    public function test_opcion_ajena_es_rechazada(): void
    {
        $usuario = User::factory()->create();
        $novel = Novel::factory()->for($usuario)->create(['status' => 'published']);
        $escena = Scene::factory()->for($novel)->create();
        $nodo = DialogueNode::factory()->for($escena)->create();
        Choice::factory()->for($nodo)->create();

        $otraNovel = Novel::factory()->create(['status' => 'published']);
        $otraEscena = Scene::factory()->for($otraNovel)->create();
        $otroNodo = DialogueNode::factory()->for($otraEscena)->create();
        $opcionAjena = Choice::factory()->for($otroNodo)->create();

        $partida = Playthrough::factory()
            ->for($usuario)
            ->for($novel)
            ->create(['current_node_id' => $nodo->id]);

        $this->actingAs($usuario)
            ->post(route('play.advance', $partida), ['choice_id' => $opcionAjena->id])
            ->assertStatus(422);

        $this->assertDatabaseHas('playthroughs', [
            'id' => $partida->id,
            'current_node_id' => $nodo->id,
        ]);
    }
}
