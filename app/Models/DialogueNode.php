<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// app/Models/DialogueNode.php
class DialogueNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'scene_id',
        'character_id',
        'character_expression_id',
        'text',
        'next_node_id',
        'order',
    ];

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function expression(): BelongsTo
    {
        return $this->belongsTo(CharacterExpression::class, 'character_expression_id');
    }

    public function nextNode(): BelongsTo
    {
        return $this->belongsTo(DialogueNode::class, 'next_node_id');
    }

   public function choices(): HasMany
{
    return $this->hasMany(Choice::class)->orderBy('order');
}

    
}