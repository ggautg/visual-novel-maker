<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

/**
 * Personaje de una novela con nombre, color y expresiones asociadas.
 */
class Character extends Model
{
    use HasFactory;

    protected $fillable = ['novel_id', 'name', 'color'];

    /**
     * Novela a la que pertenece el personaje.
     */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    /**
     * Imágenes de expresión disponibles para el personaje.
     */
    public function expressions(): HasMany
    {
        return $this->hasMany(CharacterExpression::class);
    }
}
