<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Playthrough;
use App\Models\Variable;

// app/Models/PlaythroughVariableValue.php
class PlaythroughVariableValue extends Model
{
    use HasFactory;

    protected $fillable = ['playthrough_id', 'variable_id', 'value'];

    public function playthrough(): BelongsTo
    {
        return $this->belongsTo(Playthrough::class);
    }

    public function variable(): BelongsTo
    {
        return $this->belongsTo(Variable::class);
    }
}