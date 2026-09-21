<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\DialogueNode;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChoiceController extends Controller
{
    use AuthorizesRequests;

    public function index(DialogueNode $dialogueNode)
    {
        $novel = $dialogueNode->scene->novel;
        $this->authorize('update', $novel);

        return Inertia::render('Choices/Index', [
            'node' => $dialogueNode,
            'choices' => $dialogueNode->choices()->with('variableEffects')->get(),
            'allNodes' => $dialogueNode->scene->dialogueNodes()->get(['id', 'text']),
            'variables' => $novel->variables,
        ]);
    }

    public function store(Request $request, DialogueNode $dialogueNode)
    {
        $this->authorize('update', $dialogueNode->scene->novel);

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:255'],
            'next_node_id' => ['nullable', 'exists:dialogue_nodes,id'],
        ]);

        $dialogueNode->choices()->create([
            ...$validated,
            'order' => $dialogueNode->choices()->max('order') + 1,
        ]);

        return back();
    }

    public function update(Request $request, Choice $choice)
    {
        $this->authorize('update', $choice->dialogueNode->scene->novel);

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:255'],
            'next_node_id' => ['nullable', 'exists:dialogue_nodes,id'],
        ]);

        $choice->update($validated);

        return back();
    }

    public function destroy(Choice $choice)
    {
        $this->authorize('update', $choice->dialogueNode->scene->novel);

        $choice->delete();

        return back();
    }
}