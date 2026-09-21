<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Novel;
use App\Models\CharacterExpression;
use Illuminate\Database\Eloquent\Model;

// app/Models/Character.php
// app/Models/Character.php
class Character extends Model
{
    use HasFactory;

    protected $fillable = ['novel_id', 'name', 'color'];

    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    public function expressions(): HasMany
    {
        return $this->hasMany(CharacterExpression::class);
    }
}