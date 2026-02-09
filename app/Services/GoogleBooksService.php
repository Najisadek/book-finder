<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\{Http, Cache, Log};
use Illuminate\Http\Client\RequestException;

class GoogleBooksService
{
    protected readonly string $baseUrl;
    protected readonly ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.google_books.url');
        $this->apiKey = config('services.google_books.key');
    }

    public function search(string $query, int $page = 1, int $perPage = 10): array
    {
        $cacheKey = 'google_books:search:' . md5($query . $page . $perPage);

        return Cache::remember($cacheKey, 3600, function () use ($query, $page, $perPage) {
            return $this->performSearch($query, $page, $perPage);       
        });
    }

    public function getBook(string $volumeId): ?array
    {
        $cacheKey = 'google_books:book:' . md5($volumeId);
        
        return Cache::remember($cacheKey, 3600, function () use ($volumeId) {
            return $this->performGetBook($volumeId);
        });
    }

    public function transformToSimplified(array $items): array
    {
        return array_map(function ($item) {
            $volumeInfo = $item['volumeInfo'] ?? [];
            
            return [
                'google_books_id' => $item['id'] ?? null,
                'title' => $volumeInfo['title'] ?? 'Unknown Title',
                'author' => $this->extractAuthors($volumeInfo),
                'isbn' => $this->extractIsbn($volumeInfo),
                'cover_url' => $this->extractCoverUrl($volumeInfo),
            ];

        }, $items);
    }

    protected function performSearch(string $query, int $page, int $perPage): array
    {
        try {
            $startIndex = ($page - 1) * $perPage;

            $maxResults = min($perPage, 40);
            
            $params = [
                'q' => $query,
                'startIndex' => $startIndex,
                'maxResults' => min($maxResults, 40),
                'printType' => 'books',
            ];

            if ($this->apiKey) {

                $params['key'] = $this->apiKey;
            }

            $url = $this->baseUrl . '/volumes';

            $response = Http::get($url, $params);

            if ($response->successful()) {

                return $response->json();
            }

            return ['items' => [], 'totalItems' => 0];

        } catch (RequestException $e) {
            Log::error('Google Books API request failed', [
                'message' => $e->getMessage(),
                'query' => $query,
            ]);

            return ['items' => [], 'totalItems' => 0];
        }
    }

    protected function extractAuthors(array $volumeInfo): string
    {
        return empty($volumeInfo['authors']) 
            ? 'Unknown Author' 
            : implode(', ', $volumeInfo['authors']);
    }

    protected function performGetBook(string $volumeId): ?array
    {
        try {
            $url = $this->baseUrl . '/volumes/' . $volumeId;
            $params = $this->apiKey ? ['key' => $this->apiKey] : [];

            $response = Http::get($url, $params);

            if ($response->successful()) {

                return $response->json();
            }

            if ($response->status() === 404) {
                return null;
            }

            return null;

        } catch (RequestException $e) {
            Log::error('Google Books API get book failed', [
                'message' => $e->getMessage(),
                'volumeId' => $volumeId,
            ]);

            return null;
        }
    }

    protected function extractIsbn(array $volumeInfo): ?string
    {
        $identifiers = $volumeInfo['industryIdentifiers'] ?? [];
        
        foreach ($identifiers as $identifier) {
            if (($identifier['type'] ?? '') === 'ISBN_13') {
                return $identifier['identifier'] ?? null;
            }
        }

        foreach ($identifiers as $identifier) {
            if (($identifier['type'] ?? '') === 'ISBN_10') {
                return $identifier['identifier'] ?? null;
            }
        }

        return null;
    }

    protected function extractCoverUrl(array $volumeInfo): ?string
    {
        $imageLinks = $volumeInfo['imageLinks'] ?? [];
        
        return $imageLinks['thumbnail'] 
            ?? $imageLinks['smallThumbnail'] 
            ?? null;
    }
}