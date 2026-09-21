<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class NovelLikeController extends Controller
{
    public function toggle(Novel $novel)
    {
        Gate::authorize('view', $novel);

        $user = Auth::user();

        if ($user->likedNovels()->where('novel_id', $novel->id)->exists()) {
            $user->likedNovels()->detach($novel->id);
        } else {
            $user->likedNovels()->attach($novel->id);
        }

        return back();
    }
}