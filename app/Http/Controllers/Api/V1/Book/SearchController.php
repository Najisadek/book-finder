<?php 

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Book;

use App\Http\Requests\Api\V1\SearchBooksRequest;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\BookCollection;
use App\Services\GoogleBooksService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/v1/books/search",
    summary: "Search books in Google Books only for admin",
    description: "Search for books using the Google Books API. Returns paginated results.",
    security: [["bearerAuth" => []]],
    tags: ["Books"]
)]
#[OA\Parameter(
    name: "query",
    description: "Search query (book title, author, ISBN, etc.)",
    in: "query",
    required: true,
    schema: new OA\Schema(type: "string", example: "Harry Potter")
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
    description: "Number of items per page",
    in: "query",
    required: false,
    schema: new OA\Schema(type: "integer", default: 10, minimum: 1, maximum: 40, example: 10)
)]
#[OA\Response(
    response: 200,
    description: "Books retrieved successfully",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Books retrieved successfully"),
            new OA\Property(
                property: "data",
                properties: [
                    new OA\Property(
                        property: "books",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "google_books_id", type: "string", example: "zyTCAlFPjgYC"),
                                new OA\Property(property: "title", type: "string", example: "The Google Story"),
                                new OA\Property(property: "author", type: "string", example: "David A. Vise"),
                                new OA\Property(property: "isbn", type: "string", example: "9780553804577"),
                                new OA\Property(property: "cover_url", type: "string", example: "http://books.google.com/books/content?id=zyTCAlFPjgYC&printsec=frontcover&img=1&zoom=1")
                            ]
                        )
                    ),
                    new OA\Property(property: "total_items", type: "integer", example: 1234),
                    new OA\Property(property: "current_page", type: "integer", example: 1),
                    new OA\Property(property: "per_page", type: "integer", example: 10),
                    new OA\Property(property: "query", type: "string", example: "Harry Potter")
                ],
                type: "object"
            )
        ]
    )
)]
#[OA\Response(
    response: 404,
    description: "No books found",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: false),
            new OA\Property(property: "message", type: "string", example: "No books found")
        ]
    )
)]
#[OA\Response(
    response: 401,
    description: "Unauthenticated",
    content: new OA\JsonContent(ref: "#/components/schemas/ErrorResponse")
)]
#[OA\Response(
    response: 422,
    description: "Validation error",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
            new OA\Property(
                property: "errors",
                type: "object",
                example: ["query" => ["The query field is required."]]
            )
        ]
    )
)]
final class SearchController extends ApiController
{
    public function __construct(protected GoogleBooksService $googleService) {}

    public function __invoke(SearchBooksRequest $request): JsonResponse
    {
        $data = $request->validated();

        $query = $data['query'];

        $page = (int) ($data['page'] ?? 1);

        $perPage = (int) ($data['per_page'] ?? 10);

        $response = $this->googleService->search($query, $page, $perPage);
        
        if (empty($response['items'])) {

            return $this->notFound('No books found');
        }

        $books = $this->googleService->transformToSimplified($response['items']);

        return $this->success(
            data: new BookCollection($books, $response['totalItems'], $page, $perPage, $query),
            message: 'Books retrieved successfully'
        );
    }
}