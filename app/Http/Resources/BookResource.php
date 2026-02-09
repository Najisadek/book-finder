<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

final class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Check if this is an array (Google Books) or Eloquent model (local DB)
        if (is_array($this->resource)) {
            return [
                'google_books_id' => $this->resource['google_books_id'] ?? null,
                'title' => $this->resource['title'] ?? 'Unknown Title',
                'author' => $this->resource['author'] ?? 'Unknown Author',
                'isbn' => $this->resource['isbn'] ?? null,
                'cover_url' => $this->resource['cover_url'] ?? null,
            ];
        }

        // Local database Eloquent model
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'cover_url' => $this->cover_url,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}