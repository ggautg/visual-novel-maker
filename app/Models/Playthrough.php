<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Novel;
use App\Models\DialogueNode;
use App\Models\PlaythroughVariableValue;

// app/Models/Playthrough.php
class Playthrough extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'novel_id', 'current_node_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    public function currentNode(): BelongsTo
    {
        return $this->belongsTo(DialogueNode::class, 'current_node_id');
    }

    public function variableValues(): HasMany
    {
        return $this->hasMany(PlaythroughVariableValue::class);
    }
    public static function startFor(int $userId, int $novelId): self
    {
        $novel = Novel::with('variables', 'scenes.dialogueNodes')->findOrFail($novelId);
        $firstNode = $novel->scenes->first()?->dialogueNodes->first();

        $playthrough = self::create([
            'user_id' => $userId,
            'novel_id' => $novelId,
            'current_node_id' => $firstNode?->id,
        ]);

        foreach ($novel->variables as $variable) {
            $playthrough->variableValues()->create([
                'variable_id' => $variable->id,
                'value' => $variable->default_value,
            ]);
        }

        return $playthrough;
    }

    public static function firstOrCreateFor(int $userId, int $novelId): self
    {
        return self::firstWhere(['user_id' => $userId, 'novel_id' => $novelId])
            ?? self::startFor($userId, $novelId);
    }
}