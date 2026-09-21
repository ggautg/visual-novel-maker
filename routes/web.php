<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PlaythroughController;
use App\Http\Controllers\PublicNovelController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\NovelLikeController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\CharacterExpressionController;
use App\Http\Controllers\VariableController;
use App\Http\Controllers\SceneController;
use App\Http\Controllers\DialogueNodeController;
use App\Http\Controllers\ChoiceController;
use App\Http\Controllers\VariableEffectController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('/dashboard', '/novels')->middleware(['auth', 'verified'])->name('dashboard');
    Route::resource('novels', NovelController::class);
    Route::post('novels/{novel}/restart', [PlaythroughController::class, 'restart'])->name('play.restart');
    Route::resource('novels.scenes', SceneController::class)->shallow();
    Route::resource('novels.characters', CharacterController::class)->shallow();
    Route::resource('scenes.dialogue-nodes', DialogueNodeController::class)->shallow();
    Route::resource('dialogue-nodes.choices', ChoiceController::class)->shallow();
    Route::resource('novels.variables', VariableController::class)->shallow();
    Route::resource('choices.variable-effects', VariableEffectController::class)
        ->shallow()
        ->except(['index', 'create', 'edit']);
    Route::resource('characters.expressions', CharacterExpressionController::class)
        ->shallow()
        ->except(['index', 'create', 'edit', 'update']);
    Route::get('novels/{novel}/play', [PlaythroughController::class, 'show'])->name('play.show');
    Route::post('playthroughs/{playthrough}/advance', [PlaythroughController::class, 'advance'])->name('play.advance');
    Route::get('explore', [PublicNovelController::class, 'index'])->name('novels.public');
    Route::post('novels/{novel}/like', [NovelLikeController::class, 'toggle'])->name('novels.like');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';