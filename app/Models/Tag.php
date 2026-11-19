<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Etiqueta reutilizable para clasificar novelas en el catálogo público.
 */
class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Novelas asociadas a la etiqueta.
     */
    public function novels(): BelongsToMany
    {
        return $this->belongsToMany(Novel::class);
    }
}
