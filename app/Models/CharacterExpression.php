<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterExpression extends Model
{
    use HasFactory;

    protected $fillable = ['character_id', 'name', 'image_path'];

    protected $appends = ['image_url'];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}