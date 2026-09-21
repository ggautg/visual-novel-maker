<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Choice;
use App\Models\Variable;

// app/Models/VariableEffect.php
class VariableEffect extends Model
{
    use HasFactory;

    protected $fillable = ['choice_id', 'variable_id', 'operation', 'value'];

    public function choice(): BelongsTo
    {
        return $this->belongsTo(Choice::class);
    }

    public function variable(): BelongsTo
    {
        return $this->belongsTo(Variable::class);
    }
}