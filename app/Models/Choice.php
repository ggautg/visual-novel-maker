<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DialogueNode;
use App\Models\VariableEffect;

// app/Models/Choice.php
class Choice extends Model
{
    use HasFactory;

    protected $fillable = [
        'dialogue_node_id',
        'text',
        'next_node_id',
        'order',
    ];

    public function dialogueNode(): BelongsTo
    {
        return $this->belongsTo(DialogueNode::class);
    }

    public function nextNode(): BelongsTo
    {
        return $this->belongsTo(DialogueNode::class, 'next_node_id');
    }

    public function variableEffects(): HasMany
{
    return $this->hasMany(VariableEffect::class);
}
}