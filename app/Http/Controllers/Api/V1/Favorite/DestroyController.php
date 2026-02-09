<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Favorite;

use Illuminate\Http\{Request, JsonResponse};
use App\Http\Controllers\Api\ApiController;
use OpenApi\Attributes as OA;
use App\Models\Book;

#[OA\Delete(
    path: "/v1/favorites/destroy/{book}",
    summary: "Remove book from favorites",
    description: "Remove a book from the authenticated user's favorites list",
    security: [["bearerAuth" => []]],
    tags: ["Favorites"]
)]
#[OA\Parameter(
    name: "book",
    description: "Book ID",
    in: "path",
    required: true,
    schema: new OA\Schema(type: "integer", example: 1)
)]
#[OA\Response(
    response: 200,
    description: "Book removed from favorites successfully",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Book removed from favorites")
        ]
    )
)]
#[OA\Response(
    response: 401,
    description: "Unauthenticated",
    content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
)]
#[OA\Response(
    response: 404,
    description: "Book not found",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: false),
            new OA\Property(property: "message", type: "string", example: "Book not found")
        ]
    )
)]
final class DestroyController extends ApiController
{
    public function __invoke(Request $request, Book $book): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasFavorited($book)) {

            return $this->notFound('Book not found');
        }

        $user->removeFromFavorites($book);

        return $this->success(data: [], message: 'Book removed from favorites');
    }
}