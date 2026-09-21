<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Tag;

class PublicNovelController extends Controller
{
   public function index(Request $request)
{
    $query = Novel::where('status', 'published')
        ->with(['user:id,name', 'tags']);

    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    if ($request->filled('tag')) {
        $query->whereHas('tags', fn ($q) => $q->where('name', $request->input('tag')));
    }

    return Inertia::render('Novels/Public', [
        'novels' => $query->latest()->get(['id', 'title', 'description', 'cover_image', 'user_id']),
        'allTags' => Tag::orderBy('name')->pluck('name'),
        'filters' => $request->only('search', 'tag'),
    ]);
}
}