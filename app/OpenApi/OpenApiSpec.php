<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Book Finder API",
    version: "1.0.0",
    description: "RESTful API for Book Finder"
)]
#[OA\Server(
    url: "http://localhost:8000/api",
    description: "Local API server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
#[OA\Tag(
    name: "Authentication",
    description: "Authentication endpoints"
)]
#[OA\Tag(
    name: "Books",
    description: "Book management endpoints"
)]
#[OA\Tag(
    name: "Favorites",
    description: "User favorites management endpoints"
)]
#[OA\Schema(
    schema: "ErrorResponse",
    description: "Generic error response",
    properties: [
        new OA\Property(
            property: "success",
            type: "boolean",
            example: false
        ),
        new OA\Property(
            property: "message",
            type: "string",
            example: "An error occurred"
        )
    ],
    type: "object"
)]
#[OA\Schema(
    schema: "TooManyRequestsResponse",
    description: "Rate limit exceeded response",
    properties: [
        new OA\Property(
            property: "success",
            type: "boolean",
            example: false
        ),
        new OA\Property(
            property: "message",
            type: "string",
            example: "Too many login attempts. Please try again later."
        ),
        new OA\Property(
            property: "retry_after",
            type: "integer",
            example: 60
        )
    ],
    type: "object"
)]
class OpenApiSpec
{
}