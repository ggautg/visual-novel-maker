<?php

namespace App\Http\Controllers;

use App\Models\Choice;
use App\Models\Novel;
use App\Models\Playthrough;
use App\Services\PlaythroughEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PlaythroughController extends Controller
{
  public function show(Novel $novel)
{
    Gate::authorize('view', $novel);

    $playthrough = Playthrough::firstOrCreateFor(Auth::id(), $novel->id);

    return $this->renderPlaythrough($playthrough);
}

    public function advance(Request $request, Playthrough $playthrough, PlaythroughEngine $engine)
    {
        abort_unless($playthrough->user_id === Auth::id(), 403);

        $choice = null;
        if ($request->filled('choice_id')) {
            $choice = Choice::findOrFail($request->input('choice_id'));
        }

        $playthrough = $engine->advance($playthrough, $choice);

        return $this->renderPlaythrough($playthrough);
    }

    protected function renderPlaythrough(Playthrough $playthrough)
    {
        $playthrough->load([
            'currentNode.character',
            'currentNode.expression',
            'currentNode.choices',
            'variableValues.variable',
        ]);

        $currentNode = $playthrough->currentNode;
        $scene = $currentNode?->scene;

        return Inertia::render('Play/Show', [
            'playthrough' => $playthrough,
            'novel' => $playthrough->novel,
            'scene' => $scene,
            'node' => $currentNode,
            'isFinished' => $currentNode === null,
        ]);
    }

    public function restart(Novel $novel)
{
    Gate::authorize('view', $novel);

    Playthrough::where('user_id', Auth::id())
        ->where('novel_id', $novel->id)
        ->delete();

    $playthrough = Playthrough::startFor(Auth::id(), $novel->id);

    return $this->renderPlaythrough($playthrough);
}
}