<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\Novel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CharacterController extends Controller
{
    use AuthorizesRequests;

    public function index(Novel $novel)
    {
        $this->authorize('update', $novel);

        return Inertia::render('Characters/Index', [
            'novel' => $novel,
            'characters' => $novel->characters()->with('expressions')->get(),
        ]);
    }

    public function store(Request $request, Novel $novel)
    {
        $this->authorize('update', $novel);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $novel->characters()->create($validated);

        return back();
    }

    public function update(Request $request, Character $character)
    {
        $this->authorize('update', $character->novel);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $character->update($validated);

        return back();
    }

    public function destroy(Character $character)
    {
        $this->authorize('update', $character->novel);

        $character->delete();

        return back();
    }
}