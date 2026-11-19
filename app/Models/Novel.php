<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Obra creada por un usuario: agrupa escenas, personajes, variables y etiquetas.
 * El estado de "me gusta" se calcula mediante withExists en los listados para
 * evitar una consulta por novela.
 */
class Novel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'cover_image',
        'status',
    ];

    protected $appends = ['cover_image_url'];

    protected $withCount = ['likedByUsers as likes_count'];

    /**
     * Autor de la novela.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Otras novelas relacionadas con esta (convención del modelo).
     */
    public function novels(): HasMany
    {
        return $this->hasMany(Novel::class);
    }

    /**
     * Personajes que participan en la novela.
     */
    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    /**
     * Escenas ordenadas de la novela.
     */
    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class)->orderBy('order');
    }

    /**
     * Variables disponibles para los efectos de la novela.
     */
    public function variables(): HasMany
    {
        return $this->hasMany(Variable::class);
    }

    /**
     * URL pública de la portada, o null si no hay imagen.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    /**
     * Usuarios que han marcado esta novela como favorita.
     */
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'novel_likes')->withTimestamps();
    }

    /**
     * Etiquetas asociadas a la novela.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
