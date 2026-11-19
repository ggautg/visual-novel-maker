<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Variable definida por el autor de una novela y modificable mediante efectos.
 */
class Variable extends Model
{
    use HasFactory;

    protected $fillable = ['novel_id', 'key', 'label', 'default_value'];

    /**
     * Novela a la que pertenece la variable.
     */
    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    /**
     * Efectos que modifican esta variable.
     */
    public function effects(): HasMany
    {
        return $this->hasMany(VariableEffect::class);
    }
}
