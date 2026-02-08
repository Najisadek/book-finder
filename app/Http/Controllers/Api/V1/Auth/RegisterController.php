<?php 

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Requests\Api\V1\RegistredRequest;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use App\Models\User;

final class RegisterController extends ApiController
{
    #[OA\Post(
        path: "/v1/register",
        summary: "Register a new user",
        description: "Register a new user and return authentication token",
        tags: ["Authentication"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "email", "password", "password_confirmation"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Sadek Naji"),
                new OA\Property(property: "email", type: "string", format: "email", example: "naji@example.com"),
                new OA\Property(property: "password", type: "string", format: "password", example: "secret123"),
                new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "secret123")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "User registered successfully",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string", example: "User registered successfully"),
                new OA\Property(
                    property: "data",
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "1|abc123..."),
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
    #[OA\Response(response: 422, description: "Validation error")]
    public function __invoke(RegistredRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->created([
            'token' => $token,
            'user' => $user,
        ], 'User registered successfully');
    }
}