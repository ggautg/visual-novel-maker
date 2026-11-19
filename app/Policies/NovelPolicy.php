<?php

namespace App\Policies;

use App\Models\Novel;
use App\Models\User;

/**
 * Define quién puede ver, editar o borrar cada novela.
 */
class NovelPolicy
{
    /**
     * Solo el autor puede editar la novela.
     */
    public function update(User $user, Novel $novel): bool
    {
        return $user->id === $novel->user_id;
    }

    /**
     * Solo el autor puede borrar la novela.
     */
    public function delete(User $user, Novel $novel): bool
    {
        return $user->id === $novel->user_id;
    }

    /**
     * Puede verla cualquier usuario autenticado si está publicada, o su autor.
     */
    public function view(User $user, Novel $novel): bool
    {
        return $novel->status === 'published' || $user->id === $novel->user_id;
    }
}
