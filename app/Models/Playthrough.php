<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Progreso de un usuario en una novela: nodo actual y valores de sus variables.
 * Garantiza que solo exista una partida por usuario y novela.
 */
class Playthrough extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'novel_id', 'current_node_id'];

    /**
     * Usuario que juega la partida.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Novela a la que pertenece la partida.
     */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    /**
     * Nodo de diálogo que se está mostrando, si la historia sigue en curso.
     */
    public function currentNode(): BelongsTo
    {
        return $this->belongsTo(DialogueNode::class, 'current_node_id');
    }

    /**
     * Valores de las variables de la novela en esta partida.
     */
    public function variableValues(): HasMany
    {
        return $this->hasMany(PlaythroughVariableValue::class);
    }

    /**
     * Crea una partida nueva en el primer nodo de la novela con los valores
     * iniciales de todas sus variables. Bloquea al usuario para evitar duplicados.
     */
    public static function startFor(int $userId, int $novelId): self
    {
        return DB::transaction(function () use ($userId, $novelId) {
            User::whereKey($userId)->lockForUpdate()->firstOrFail();

            $novel = Novel::with('variables', 'scenes.dialogueNodes')->findOrFail($novelId);
            $primerNodo = $novel->scenes->first()?->dialogueNodes->first();

            $partida = self::create([
                'user_id' => $userId,
                'novel_id' => $novelId,
                'current_node_id' => $primerNodo?->id,
            ]);

            foreach ($novel->variables as $variable) {
                $partida->variableValues()->create([
                    'variable_id' => $variable->id,
                    'value' => $variable->default_value,
                ]);
            }

            return $partida;
        });
    }

    /**
     * Devuelve la partida existente o crea una de forma segura ante dobles clics.
     */
    public static function firstOrCreateFor(int $userId, int $novelId): self
    {
        return DB::transaction(function () use ($userId, $novelId) {
            User::whereKey($userId)->lockForUpdate()->firstOrFail();

            $existente = self::where([
                'user_id' => $userId,
                'novel_id' => $novelId,
            ])->lockForUpdate()->first();

            return $existente ?? self::startFor($userId, $novelId);
        });
    }
}
