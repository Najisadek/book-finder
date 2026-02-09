<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Favorite;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\BookCollection;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Illuminate\Http\Request;

#[OA\Get(
    path: "/v1/favorites",
    summary: "Get user's favorite books",
    description: "Retrieve a paginated list of the authenticated user's favorite books",
    security: [["bearerAuth" => []]],
    tags: ["Favorites"]
)]
#[OA\Parameter(
    name: "page",
    description: "Page number for pagination",
    in: "query",
    required: false,
    schema: new OA\Schema(type: "integer", default: 1, example: 1)
)]
#[OA\Parameter(
    name: "per_page",
    description: "Number of items per page (max 50)",
    in: "query",
    required: false,
    schema: new OA\Schema(type: "integer", default: 10, minimum: 1, maximum: 50, example: 10)
)]
#[OA\Response(
    response: 200,
    description: "Favorite books retrieved successfully",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Favorite books retrieved successfully"),
            new OA\Property(
                property: "data",
                properties: [
                    new OA\Property(
                        property: "books",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "google_books_id", type: "string", example: "zyTCAlFPjgYC"),
                                new OA\Property(property: "title", type: "string", example: "The Google Story"),
                                new OA\Property(property: "author", type: "string", example: "David A. Vise"),
                                new OA\Property(property: "isbn", type: "string", example: "9780553804577"),
                                new OA\Property(property: "cover_url", type: "string", example: "http://books.google.com/books/content?id=zyTCAlFPjgYC&printsec=frontcover&img=1&zoom=1")
                            ]
                        )
                    ),
                    new OA\Property(property: "total_items", type: "integer", example: 25),
                    new OA\Property(property: "current_page", type: "integer", example: 1),
                    new OA\Property(property: "per_page", type: "integer", example: 10)
                ],
                type: "object"
            )
        ]
    )
)]
#[OA\Response(
    response: 401,
    description: "Unauthenticated",
    content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
)]
final class IndexController extends ApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorized();
        }

        $page = (int) $request->query('page', 1);
        
        $perPage = (int) $request->query('per_page', 10);
        
        $perPage = min($perPage, 50);

        $paginator = $user->favoriteBooks()->latest()->paginate(perPage: $perPage, page: $page);

        return $this->success(
            data: new BookCollection(
                resource: $paginator->items(),
                totalItems: $paginator->total(),
                currentPage: $paginator->currentPage(),
                perPage: $paginator->perPage()
            ),
            message: 'Favorite books retrieved successfully'
        );
    }
}