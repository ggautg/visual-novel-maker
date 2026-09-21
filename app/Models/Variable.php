<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Novel;
use App\Models\VariableEffect;

// app/Models/Variable.php
class Variable extends Model
{
    use HasFactory;

    protected $fillable = ['novel_id', 'key', 'label', 'default_value'];

    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    public function effects(): HasMany
    {
        return $this->hasMany(VariableEffect::class);
    }
}