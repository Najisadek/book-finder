<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

final class Book extends Model
{
    protected $fillable = [
        'author',
        'title',
        'isbn',
        'cover_url',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_book');
    }
}
