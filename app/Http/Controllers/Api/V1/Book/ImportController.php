<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Book;

use App\Http\Requests\Api\V1\ImportBookRequest;
use App\Http\Controllers\Api\ApiController;
use App\Services\GoogleBooksService;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use App\Models\Book;

#[OA\Post(
    path: "/v1/books/import",
    summary: "Import a book from Google Books only for admin",
    description: "Import a book into the local database using its Google Books ID. If the book already exists, returns the existing record.",
    security: [["bearerAuth" => []]],
    tags: ["Books"]
)]
#[OA\RequestBody(
    required: true,
    content: new OA\JsonContent(
        required: ["google_books_id"],
        properties: [
            new OA\Property(
                property: "google_books_id",
                description: "Google Books unique identifier",
                type: "string",
                example: "zyTCAlFPjgYC"
            )
        ]
    )
)]
#[OA\Response(
    response: 200,
    description: "Book already exists in database",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Book already exists in database"),
            new OA\Property(
                property: "data",
                properties: [
                    new OA\Property(
                        property: "book",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "google_books_id", type: "string", example: "zyTCAlFPjgYC"),
                            new OA\Property(property: "title", type: "string", example: "The Google Story"),
                            new OA\Property(property: "author", type: "string", example: "David A. Vise"),
                            new OA\Property(property: "isbn", type: "string", example: "9780553804577"),
                            new OA\Property(property: "cover_url", type: "string", example: "http://books.google.com/books/content?id=zyTCAlFPjgYC&printsec=frontcover&img=1&zoom=1")
                        ],
                        type: "object"
                    )
                ],
                type: "object"
            )
        ]
    )
)]
#[OA\Response(
    response: 201,
    description: "Book imported successfully",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Book imported successfully from Google Books"),
            new OA\Property(
                property: "data",
                properties: [
                    new OA\Property(
                        property: "book",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "google_books_id", type: "string", example: "zyTCAlFPjgYC"),
                            new OA\Property(property: "title", type: "string", example: "The Google Story"),
                            new OA\Property(property: "author", type: "string", example: "David A. Vise"),
                            new OA\Property(property: "isbn", type: "string", example: "9780553804577"),
                            new OA\Property(property: "cover_url", type: "string", example: "http://books.google.com/books/content?id=zyTCAlFPjgYC&printsec=frontcover&img=1&zoom=1")
                        ],
                        type: "object"
                    )
                ],
                type: "object"
            )
        ]
    )
)]
#[OA\Response(
    response: 404,
    description: "Book not found in Google Books API",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: false),
            new OA\Property(property: "message", type: "string", example: "Book not found in Google Books API")
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
                example: ["google_books_id" => ["The google books id field is required."]]
            )
        ]
    )
)]
#[OA\Response(
    response: 500,
    description: "Failed to import book",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: false),
            new OA\Property(property: "message", type: "string", example: "Failed to import book"),
            new OA\Property(
                property: "errors",
                properties: [
                    new OA\Property(property: "error", type: "string", example: "An unexpected error occurred")
                ],
                type: "object"
            )
        ]
    )
)]
final class ImportController extends ApiController
{
    public function __construct(protected GoogleBooksService $googleService) {}
    
    public function __invoke(ImportBookRequest $request): JsonResponse
    {
        $googleBooksId = $request->validated()['google_books_id'];

        $existingBook = Book::where('google_books_id', $googleBooksId)->first();
        
        if ($existingBook) {

            return $this->success([
                'book' => new BookResource($existingBook),
            ], 'Book already exists in database');
        }

        $bookData = $this->googleService->getBook($googleBooksId);
        
        if (!$bookData) {

            return $this->notFound('Book not found in Google Books API');
        }

        $transformed = $this->googleService->transformToSimplified([$bookData])[0];

        try {
            $book = DB::transaction(function () use ($transformed, $googleBooksId) {
                return Book::create([
                    'google_books_id' => $googleBooksId,
                    'title' => $transformed['title'],
                    'author' => $transformed['author'],
                    'isbn' => $transformed['isbn'],
                    'cover_url' => $transformed['cover_url'],
                ]);
            });

            return $this->created([
                'book' => new BookResource($book),
            ], 'Book imported successfully from Google Books');
            
        } catch (\Exception $e) {

            return $this->error('Failed to import book', errors:[
                'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred',
            ]);
        }
    }
}