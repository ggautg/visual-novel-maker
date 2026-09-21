<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use App\Models\Variable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VariableController extends Controller
{
    use AuthorizesRequests;

    public function index(Novel $novel)
    {
        $this->authorize('update', $novel);

        return Inertia::render('Variables/Index', [
            'novel' => $novel,
            'variables' => $novel->variables,
        ]);
    }

    public function store(Request $request, Novel $novel)
    {
        $this->authorize('update', $novel);

        $validated = $request->validate([
            'key' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('variables')->where('novel_id', $novel->id),
            ],
            'label' => ['nullable', 'string', 'max:255'],
            'default_value' => ['required', 'integer'],
        ]);

        $novel->variables()->create($validated);

        return back();
    }

    public function update(Request $request, Variable $variable)
    {
        $this->authorize('update', $variable->novel);

        $validated = $request->validate([
            'key' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('variables')->where('novel_id', $variable->novel_id)->ignore($variable->id),
            ],
            'label' => ['nullable', 'string', 'max:255'],
            'default_value' => ['required', 'integer'],
        ]);

        $variable->update($validated);

        return back();
    }

    public function destroy(Variable $variable)
    {
        $this->authorize('update', $variable->novel);

        $variable->delete();

        return back();
    }
}