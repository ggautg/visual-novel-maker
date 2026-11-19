<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Alterna el "me gusta" de una novela para el usuario autenticado.
 * Serializa los cambios por usuario para que dobles clics no rompan el índice único.
 */
class NovelLikeController extends Controller
{
    /**
     * Añade o quita el like y devuelve el estado resultante.
     * Devuelve JSON para peticiones API y una redirección con datos flash para Inertia.
     */
    public function toggle(Request $request, Novel $novel): JsonResponse|RedirectResponse
    {
        Gate::authorize('view', $novel);

        $meGusta = DB::transaction(function () use ($novel) {
            $usuarioId = Auth::id();

            User::whereKey($usuarioId)->lockForUpdate()->firstOrFail();

            $like = DB::table('novel_likes')
                ->where('user_id', $usuarioId)
                ->where('novel_id', $novel->id);

            if ($like->lockForUpdate()->exists()) {
                $like->delete();

                return false;
            }

            DB::table('novel_likes')->insert([
                'user_id' => $usuarioId,
                'novel_id' => $novel->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        });

        $estado = [
            'is_liked_by_me' => $meGusta,
            'likes_count' => DB::table('novel_likes')->where('novel_id', $novel->id)->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json($estado);
        }

        return back()->with($estado);
    }
}
