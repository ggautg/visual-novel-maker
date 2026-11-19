<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Imagen de una expresión concreta de un personaje.
 */
class CharacterExpression extends Model
{
    use HasFactory;

    protected $fillable = ['character_id', 'name', 'image_path'];

    protected $appends = ['image_url'];

    /**
     * Personaje al que pertenece la expresión.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * URL pública de la imagen de la expresión.
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
