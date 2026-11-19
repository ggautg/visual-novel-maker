<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Valor actual de una variable dentro de una partida concreta.
 */
class PlaythroughVariableValue extends Model
{
    use HasFactory;

    protected $fillable = ['playthrough_id', 'variable_id', 'value'];

    /**
     * Partida a la que pertenece el valor.
     */
    public function playthrough(): BelongsTo
    {
        return $this->belongsTo(Playthrough::class);
    }

    /**
     * Variable cuyo valor se guarda.
     */
    public function variable(): BelongsTo
    {
        return $this->belongsTo(Variable::class);
    }
}
