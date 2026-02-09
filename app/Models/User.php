<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\{Factories\HasFactory, Casts\Attribute, Relations\BelongsToMany};
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;

final class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    protected function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->role === UserRole::Admin,
        );
    }

    public function favoriteBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'user_book')
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }

    public function hasFavorited(Book $book): bool
    {
        return $this->favoriteBooks()->where('book_id', $book->id)->exists();
    }

    public function addToFavorites(Book $book): void
    {
        $this->favoriteBooks()->syncWithoutDetaching($book->id);
    }

    public function removeFromFavorites(Book $book): void
    {
        $this->favoriteBooks()->detach($book->id);
    }
}
