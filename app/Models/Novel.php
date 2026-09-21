<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Novel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'cover_image',
        'status',
    ];

    protected $appends = ['cover_image_url', 'is_liked_by_me'];

    protected $withCount = ['likedByUsers as likes_count'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function novels(): HasMany
    {
        return $this->hasMany(Novel::class);
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }
    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class)->orderBy('order');
    }

    public function variables(): HasMany
    {
        return $this->hasMany(Variable::class);
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'novel_likes')->withTimestamps();
    }
    public function getIsLikedByMeAttribute(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->likedByUsers()->where('user_id', Auth::id())->exists();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}