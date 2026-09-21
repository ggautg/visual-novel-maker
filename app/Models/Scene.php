<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Novel;
use App\Models\DialogueNode;

// app/Models/Scene.php
class Scene extends Model
{
    use HasFactory;

    protected $fillable = [
        'novel_id',
        'title',
        'background_image',
        'background_music',
        'order',
    ];

    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    public function dialogueNodes(): HasMany
{
    return $this->hasMany(DialogueNode::class)->orderBy('order');
}
}