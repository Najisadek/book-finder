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
class OpenApiSpec
{
}