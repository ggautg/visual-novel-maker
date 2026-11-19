<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario de la aplicación: autor de novelas, jugador de partidas y lector favorito.
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Devuelve los tipos que deben convertirse al leer el modelo.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Novelas creadas por el usuario.
     */
    public function novels(): HasMany
    {
        return $this->hasMany(Novel::class);
    }

    /**
     * Novelas marcadas como favoritas por el usuario.
     */
    public function likedNovels(): BelongsToMany
    {
        return $this->belongsToMany(Novel::class, 'novel_likes')->withTimestamps();
    }
}
