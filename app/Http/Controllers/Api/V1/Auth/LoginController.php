<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use App\Models\User;

final class LoginController extends ApiController
{
    #[OA\Post(
        path: "/v1/login",
        summary: "Login user",
        description: "Authenticate user and return token",
        tags: ["Authentication"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password"],
            properties: [
                new OA\Property(property: "email", type: "string", format: "email", example: "naji@example.com"),
                new OA\Property(property: "password", type: "string", format: "password", example: "secret123")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Login successful",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "Login successful"),
                new OA\Property(
                    property: "data",
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "2|xyz789..."),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Sadek Naji"),
                                new OA\Property(property: "email", type: "string", example: "naji@example.com")
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
        response: 401,
        description: "Invalid credentials",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: false),
                new OA\Property(property: "message", type: "string", example: "Invalid credentials")
            ]
        )
    )]
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            
            return $this->unauthorized('Invalid credentials');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $user,
        ], 'Login successful');
    }
}