<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Scene;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SceneController extends Controller
{
    use AuthorizesRequests;

    public function index(Novel $novel)
    {
        $this->authorize('update', $novel);

        return Inertia::render('Scenes/Index', [
            'novel' => $novel,
            'scenes' => $novel->scenes()->withCount('dialogueNodes')->get(),
        ]);
    }

    public function store(Request $request, Novel $novel)
    {
        $this->authorize('update', $novel);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'background_image' => ['nullable', 'image', 'max:4096'],
            'background_music' => ['nullable', 'file', 'mimes:mp3,ogg,wav', 'max:10240'],
        ]);

        $data = [
            'title' => $validated['title'] ?? null,
            'order' => $novel->scenes()->max('order') + 1,
        ];

        if ($request->hasFile('background_image')) {
            $data['background_image'] = $request->file('background_image')->store('backgrounds', 'public');
        }

        if ($request->hasFile('background_music')) {
            $data['background_music'] = $request->file('background_music')->store('music', 'public');
        }

        $novel->scenes()->create($data);

        return back();
    }

    public function update(Request $request, Scene $scene)
    {
        $this->authorize('update', $scene->novel);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'background_image' => ['nullable', 'image', 'max:4096'],
            'background_music' => ['nullable', 'file', 'mimes:mp3,ogg,wav', 'max:10240'],
        ]);

        $data = ['title' => $validated['title'] ?? null];

        if ($request->hasFile('background_image')) {
            if ($scene->background_image) {
                Storage::disk('public')->delete($scene->background_image);
            }
            $data['background_image'] = $request->file('background_image')->store('backgrounds', 'public');
        }

        if ($request->hasFile('background_music')) {
            if ($scene->background_music) {
                Storage::disk('public')->delete($scene->background_music);
            }
            $data['background_music'] = $request->file('background_music')->store('music', 'public');
        }

        $scene->update($data);

        return back();
    }

    public function destroy(Scene $scene)
    {
        $this->authorize('update', $scene->novel);

        if ($scene->background_image) {
            Storage::disk('public')->delete($scene->background_image);
        }
        if ($scene->background_music) {
            Storage::disk('public')->delete($scene->background_music);
        }

        $scene->delete();

        return back();
    }
}