<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Efecto aplicado a una variable cuando se elige una opción.
 */
class VariableEffect extends Model
{
    use HasFactory;

    protected $fillable = ['choice_id', 'variable_id', 'operation', 'value'];

    /**
     * Opción que dispara el efecto.
     */
    public function choice(): BelongsTo
    {
        return $this->belongsTo(Choice::class);
    }

    /**
     * Variable modificada por el efecto.
     */
    public function variable(): BelongsTo
    {
        return $this->belongsTo(Variable::class);
    }
}
