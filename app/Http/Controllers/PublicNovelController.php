<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Catálogo público de novelas publicadas, con búsqueda y filtro por etiqueta.
 */
class PublicNovelController extends Controller
{
    /**
     * Devuelve novelas publicadas con sus etiquetas y el estado de "me gusta"
     * del visitante (falso para usuarios no autenticados).
     */
    public function index(Request $request): Response
    {
        $consulta = Novel::where('status', 'published')
            ->with(['user:id,name', 'tags'])
            ->withExists([
                'likedByUsers as is_liked_by_me' => fn ($q) => $q->where('user_id', Auth::id()),
            ]);

        if ($request->filled('search')) {
            $busqueda = $request->input('search');
            $consulta->where(function ($q) use ($busqueda) {
                $q->where('title', 'like', "%{$busqueda}%")
                    ->orWhere('description', 'like', "%{$busqueda}%");
            });
        }

        if ($request->filled('tag')) {
            $consulta->whereHas('tags', fn ($q) => $q->where('name', $request->input('tag')));
        }

        return Inertia::render('Novels/Public', [
            'novels' => $consulta->latest()->get(['id', 'title', 'description', 'cover_image', 'user_id']),
            'allTags' => Tag::orderBy('name')->pluck('name'),
            'filters' => $request->only('search', 'tag'),
        ]);
    }
}
