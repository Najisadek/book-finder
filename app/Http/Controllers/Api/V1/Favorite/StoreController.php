<?php 

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Favorite;

use Illuminate\Http\{Request, JsonResponse};
use App\Http\Controllers\Api\ApiController;
use OpenApi\Attributes as OA;
use App\Models\Book;

#[OA\Post(
    path: "/v1/favorites/store/{book}",
    summary: "Add book to favorites",
    description: "Add a book to the authenticated user's favorites list",
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
    description: "Book added to favorites or already in favorites",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Book added to favorites"),
            new OA\Property(
                property: "data",
                properties: [
                    new OA\Property(property: "book_id", type: "integer", example: 1)
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
final class StoreController extends ApiController
{
    public function __invoke(Request $request, Book $book): JsonResponse
    {
        $user = $request->user();

        if ($user->hasFavorited($book)) {
            return $this->success(['book_id' => $book->id], 'Book is already in your favorites.');
        }
        
        $user->addToFavorites($book);

        return $this->success(['book_id' => $book->id], 'Book added to favorites');
    }
}