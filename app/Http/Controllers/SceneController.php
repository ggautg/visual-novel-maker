<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\DialogueNode;
use App\Models\Novel;
use App\Models\Playthrough;
use App\Models\Scene;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Administra las escenas de una novela y sus archivos de fondo y música.
 * Impide borrar escenas que todavía son usadas por partidas o enlaces.
 */
class SceneController extends Controller
{
    use AuthorizesRequests;

    /**
     * Lista las escenas de una novela propia con el número de nodos que tiene cada una.
     */
    public function index(Novel $novel): Response
    {
        $this->authorize('update', $novel);

        return Inertia::render('Scenes/Index', [
            'novel' => $novel,
            'scenes' => $novel->scenes()->withCount('dialogueNodes')->get(),
        ]);
    }

    /**
     * Crea una escena al final del orden de la novela, con fondos opcionales.
     */
    public function store(Request $request, Novel $novel): RedirectResponse
    {
        $this->authorize('update', $novel);

        $validados = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'background_image' => ['nullable', 'image', 'max:4096'],
            'background_music' => ['nullable', 'file', 'mimes:mp3,ogg,wav', 'max:10240'],
        ]);

        $datos = [
            'title' => $validados['title'] ?? null,
            'order' => $novel->scenes()->max('order') + 1,
        ];

        if ($request->hasFile('background_image')) {
            $datos['background_image'] = $request->file('background_image')->store('backgrounds', 'public');
        }

        if ($request->hasFile('background_music')) {
            $datos['background_music'] = $request->file('background_music')->store('music', 'public');
        }

        $novel->scenes()->create($datos);

        return back();
    }

    /**
     * Actualiza una escena propia y sustituye sus archivos solo cuando se validan.
     */
    public function update(Request $request, Scene $scene): RedirectResponse
    {
        $this->authorize('update', $scene->novel);

        $validados = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'background_image' => ['nullable', 'image', 'max:4096'],
            'background_music' => ['nullable', 'file', 'mimes:mp3,ogg,wav', 'max:10240'],
        ]);

        $datos = ['title' => $validados['title'] ?? null];
        $imagenAnterior = $scene->background_image;
        $musicaAnterior = $scene->background_music;

        if ($request->hasFile('background_image')) {
            $datos['background_image'] = $request->file('background_image')->store('backgrounds', 'public');
        }

        if ($request->hasFile('background_music')) {
            $datos['background_music'] = $request->file('background_music')->store('music', 'public');
        }

        $scene->update($datos);

        if ($request->hasFile('background_image') && $imagenAnterior) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        if ($request->hasFile('background_music') && $musicaAnterior) {
            Storage::disk('public')->delete($musicaAnterior);
        }

        return back();
    }

    /**
     * Elimina una escena si ninguna partida, salto o efecto depende de sus nodos.
     */
    public function destroy(Scene $scene): RedirectResponse
    {
        $this->authorize('update', $scene->novel);

        $idsNodos = $scene->dialogueNodes()->pluck('id');
        $enUso = Playthrough::whereIn('current_node_id', $idsNodos)->exists()
            || DialogueNode::whereIn('next_node_id', $idsNodos)->exists()
            || Choice::whereIn('next_node_id', $idsNodos)->exists()
            || Choice::whereIn('dialogue_node_id', $idsNodos)->whereHas('variableEffects')->exists();

        if ($enUso) {
            return back()->with('error', 'No se puede eliminar la escena porque hay partidas, saltos o efectos que la referencian.');
        }

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
