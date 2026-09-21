<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\VariableEffect;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariableEffectController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Choice $choice)
    {
        $novel = $choice->dialogueNode->scene->novel;
        $this->authorize('update', $novel);

        $validated = $request->validate([
            'variable_id' => [
                'required',
                Rule::exists('variables', 'id')->where('novel_id', $novel->id),
            ],
            'operation' => ['required', 'in:set,increment,decrement'],
            'value' => ['required', 'integer'],
        ]);

        $choice->variableEffects()->create($validated);

        return back();
    }

    public function update(Request $request, VariableEffect $variableEffect)
    {
        $novel = $variableEffect->choice->dialogueNode->scene->novel;
        $this->authorize('update', $novel);

        $validated = $request->validate([
            'variable_id' => [
                'required',
                Rule::exists('variables', 'id')->where('novel_id', $novel->id),
            ],
            'operation' => ['required', 'in:set,increment,decrement'],
            'value' => ['required', 'integer'],
        ]);

        $variableEffect->update($validated);

        return back();
    }

    public function destroy(VariableEffect $variableEffect)
    {
        $novel = $variableEffect->choice->dialogueNode->scene->novel;
        $this->authorize('update', $novel);

        $variableEffect->delete();

        return back();
    }
}