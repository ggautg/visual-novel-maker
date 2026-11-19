<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Nodo de diálogo dentro de una escena: texto, personaje, expresión y salto siguiente.
 */
class DialogueNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'scene_id',
        'character_id',
        'character_expression_id',
        'text',
        'next_node_id',
        'order',
    ];

    /**
     * Escena a la que pertenece el nodo.
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    /**
     * Personaje que habla en el nodo, si lo hay.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Expresión que muestra el personaje en el nodo.
     */
    public function expression(): BelongsTo
    {
        return $this->belongsTo(CharacterExpression::class, 'character_expression_id');
    }

    /**
     * Nodo siguiente cuando no hay opciones que decidir.
     */
    public function nextNode(): BelongsTo
    {
        return $this->belongsTo(DialogueNode::class, 'next_node_id');
    }

    /**
     * Opciones ordenadas que se ofrecen en el nodo.
     */
    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class)->orderBy('order');
    }
}
