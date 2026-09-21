<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CharacterExpression;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CharacterExpressionController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Character $character)
    {
        $this->authorize('update', $character->novel);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('image')->store('expressions', 'public');

        $character->expressions()->create([
            'name' => $validated['name'],
            'image_path' => $path,
        ]);

        return back();
    }

public function destroy(CharacterExpression $expression)
{
    $this->authorize('update', $expression->character->novel);

    Storage::disk('public')->delete($expression->image_path);
    $expression->delete();

    return back();
}
}