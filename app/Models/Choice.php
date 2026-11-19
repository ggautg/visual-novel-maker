<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Opción presentada al jugador en un nodo, con su texto, destino y efectos.
 */
class Choice extends Model
{
    use HasFactory;

    protected $fillable = [
        'dialogue_node_id',
        'text',
        'next_node_id',
        'order',
    ];

    /**
     * Nodo en el que se muestra la opción.
     */
    public function dialogueNode(): BelongsTo
    {
        return $this->belongsTo(DialogueNode::class);
    }

    /**
     * Nodo al que lleva la opción.
     */
    public function nextNode(): BelongsTo
    {
        return $this->belongsTo(DialogueNode::class, 'next_node_id');
    }

    /**
     * Efectos sobre variables que se aplican al elegir la opción.
     */
    public function variableEffects(): HasMany
    {
        return $this->hasMany(VariableEffect::class);
    }
}
