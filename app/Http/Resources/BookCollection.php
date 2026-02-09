<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Request;

final class BookCollection extends ResourceCollection
{
    protected int $totalItems;
    protected int $currentPage;
    protected int $perPage;
    protected ?string $query;

    public function __construct(
        $resource,
        int $totalItems,
        int $currentPage,
        int $perPage,
        ?string $query = null
    ) {
        parent::__construct($resource);

        $this->totalItems = $totalItems;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->query = $query;
    }

    public function toArray(Request $request): array
    {
        return [
            'book' => BookResource::collection($this->collection),
            'meta' => $this->meta(),
            'links' => $this->links(),
        ];
    }

    protected function meta(): array
    {
        $lastPage = (int) ceil($this->totalItems / $this->perPage);

        return [
            'total_items' => $this->totalItems,
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page' => $lastPage,
            'from'  => $this->totalItems
                ? (($this->currentPage - 1) * $this->perPage) + 1
                : 0,
            'to' => min($this->currentPage * $this->perPage, $this->totalItems),
        ];
    }

    protected function links(): array
    {
        $lastPage = (int) ceil($this->totalItems / $this->perPage);

        return [
            'first' => $this->buildUrl(1),
            'last' => $this->buildUrl($lastPage),
            'prev' => $this->currentPage > 1
                ? $this->buildUrl($this->currentPage - 1)
                : null,
            'next' => $this->currentPage < $lastPage
                ? $this->buildUrl($this->currentPage + 1)
                : null,
        ];
    }

    protected function buildUrl(int $page): string
    {
        return url()->current() . '?' . http_build_query(array_filter([
            'query' => $this->query,
            'page' => $page,
            'per_page' => $this->perPage,
        ]));
    }
}
