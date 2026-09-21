<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NovelController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        return Inertia::render('Novels/Index', [
            'novels' => Auth::user()->novels()->with('tags')->latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Novels/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $data = collect($validated)->except('cover_image')->toArray();

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $novel = Auth::user()->novels()->create($data);

        return redirect()->route('novels.index');
    }
    public function edit(Novel $novel)
    {
        $this->authorize('update', $novel);

        return Inertia::render('Novels/Edit', [
            'novel' => $novel,
        ]);
    }

    public function update(Request $request, Novel $novel)
    {
        $this->authorize('update', $novel);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ]);

        $data = collect($validated)->except(['cover_image', 'tags'])->toArray();

        if ($request->hasFile('cover_image')) {
            if ($novel->cover_image) {
                Storage::disk('public')->delete($novel->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $novel->update($data);

        if ($request->has('tags')) {
            $tagIds = collect($validated['tags'] ?? [])
                ->map(fn($name) => Tag::firstOrCreate(['name' => strtolower(trim($name))])->id);

            $novel->tags()->sync($tagIds);
        }

        return redirect()->route('novels.index');
    }

    public function destroy(Novel $novel)
    {
        $this->authorize('delete', $novel);

        $novel->delete();

        return redirect()->route('novels.index');
    }
}