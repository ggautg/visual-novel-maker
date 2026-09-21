<?php

namespace App\Http\Controllers;

use App\Models\DialogueNode;
use App\Models\Scene;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DialogueNodeController extends Controller
{
    use AuthorizesRequests;

    public function index(Scene $scene)
    {
        $this->authorize('update', $scene->novel);

        return Inertia::render('DialogueNodes/Index', [
            'scene' => $scene,
            'nodes' => $scene->dialogueNodes()
                ->with(['character', 'expression', 'choices'])
                ->get(),
            'characters' => $scene->novel->characters()->with('expressions')->get(),
        ]);
    }

    public function store(Request $request, Scene $scene)
    {
        $this->authorize('update', $scene->novel);

        $validated = $request->validate([
            'character_id' => ['nullable', 'exists:characters,id'],
            'character_expression_id' => ['nullable', 'exists:character_expressions,id'],
            'text' => ['required', 'string'],
            'next_node_id' => ['nullable', 'exists:dialogue_nodes,id'],
        ]);

        $scene->dialogueNodes()->create([
            ...$validated,
            'order' => $scene->dialogueNodes()->max('order') + 1,
        ]);

        return back();
    }

    public function update(Request $request, DialogueNode $dialogueNode)
    {
        $this->authorize('update', $dialogueNode->scene->novel);

        $validated = $request->validate([
            'character_id' => ['nullable', 'exists:characters,id'],
            'character_expression_id' => ['nullable', 'exists:character_expressions,id'],
            'text' => ['required', 'string'],
            'next_node_id' => ['nullable', 'exists:dialogue_nodes,id'],
        ]);

        $dialogueNode->update($validated);

        return back();
    }

    public function destroy(DialogueNode $dialogueNode)
    {
        $this->authorize('update', $dialogueNode->scene->novel);

        $dialogueNode->delete();

        return back();
    }
}