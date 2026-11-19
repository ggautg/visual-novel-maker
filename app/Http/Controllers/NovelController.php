<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

/**
 * Gestiona las novelas del usuario autenticado: listado, creación, edición y borrado.
 * Solo el autor puede modificar o eliminar cada novela.
 */
class NovelController extends Controller
{
    use AuthorizesRequests;

    /**
     * Lista las novelas propias con sus etiquetas y el estado de "me gusta" del usuario.
     */
    public function index(): Response
    {
        return Inertia::render('Novels/Index', [
            'novels' => Auth::user()->novels()
                ->with('tags')
                ->withExists([
                    'likedByUsers as is_liked_by_me' => fn ($consulta) => $consulta->where('user_id', Auth::id()),
                ])
                ->latest()
                ->get(),
        ]);
    }

    /**
     * Muestra el formulario de creación de una novela nueva.
     */
    public function create(): Response
    {
        return Inertia::render('Novels/Create');
    }

    /**
     * Valida los datos y crea la novela asociada al usuario autenticado.
     * Devuelve una redirección al listado de novelas.
     */
    public function store(Request $request): RedirectResponse
    {
        $validados = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
        ]);

        $datos = collect($validados)->except('cover_image')->toArray();

        if ($request->hasFile('cover_image')) {
            $datos['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $request->user()->novels()->create($datos);

        return redirect()->route('novels.index');
    }

    /**
     * Muestra el formulario de edición de una novela propia.
     * Verifica que el usuario sea el autor.
     */
    public function edit(Novel $novel): Response
    {
        $this->authorize('update', $novel);

        return Inertia::render('Novels/Edit', [
            'novel' => $novel->load('tags'),
        ]);
    }

    /**
     * Actualiza una novela propia, sus etiquetas y, si llega, su portada.
     * Si no se envía la clave tags se conservan las etiquetas actuales.
     */
    public function update(Request $request, Novel $novel): RedirectResponse
    {
        $this->authorize('update', $novel);

        $validados = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:draft,published'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
        ]);

        $datos = collect($validados)->except(['cover_image', 'tags'])->toArray();
        $portadaAnterior = $novel->cover_image;

        if ($request->hasFile('cover_image')) {
            $datos['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $novel->update($datos);

        if ($request->hasFile('cover_image') && $portadaAnterior) {
            Storage::disk('public')->delete($portadaAnterior);
        }

        if (array_key_exists('tags', $validados)) {
            $idsEtiquetas = collect($validados['tags'] ?? [])
                ->map(fn ($nombre) => Tag::firstOrCreate(['name' => strtolower(trim($nombre))])->id);

            $novel->tags()->sync($idsEtiquetas);
        }

        return redirect()->route('novels.index');
    }

    /**
     * Elimina una novela propia y borra también su fichero de portada.
     */
    public function destroy(Novel $novel): RedirectResponse
    {
        $this->authorize('delete', $novel);

        $portada = $novel->cover_image;
        $novel->delete();

        if ($portada) {
            Storage::disk('public')->delete($portada);
        }

        return redirect()->route('novels.index');
    }
}
