<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_books_id',
        'author',
        'title',
        'isbn',
        'cover_url',
    ];

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_book')->withTimestamps();
    }

    public function scopeFavoritedByUser($query, User $user)
    {
        return $query->whereHas('favoritedByUsers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->favoritedByUsers()->where('user_id', $user->id)->exists();
    }
}
