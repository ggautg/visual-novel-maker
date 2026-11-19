<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Escena de una novela: agrupa nodos de diálogo con fondo y música propios.
 */
class Scene extends Model
{
    use HasFactory;

    protected $fillable = [
        'novel_id',
        'title',
        'background_image',
        'background_music',
        'order',
    ];

    /**
     * Novela a la que pertenece la escena.
     */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    /**
     * Nodos de diálogo ordenados de la escena.
     */
    public function dialogueNodes(): HasMany
    {
        return $this->hasMany(DialogueNode::class)->orderBy('order');
    }
}
