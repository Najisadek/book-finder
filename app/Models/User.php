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

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'user_book');
    }
}
