<?php

namespace App\Policies;

use App\Models\Novel;
use App\Models\User;

class NovelPolicy
{
    public function update(User $user, Novel $novel): bool
    {
        return $user->id === $novel->user_id;
    }

    public function delete(User $user, Novel $novel): bool
    {
        return $user->id === $novel->user_id;
    }

    // app/Policies/NovelPolicy.php

    public function view(User $user, Novel $novel): bool
    {
        return $novel->status === 'published' || $user->id === $novel->user_id;
    }
}