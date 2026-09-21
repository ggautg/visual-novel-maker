<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Novel;

// app/Models/Tag.php
class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function novels(): BelongsToMany
    {
        return $this->belongsToMany(Novel::class);
    }
}