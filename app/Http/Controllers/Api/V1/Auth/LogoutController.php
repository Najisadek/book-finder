<?php 

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use Illuminate\Http\{JsonResponse, Request};
use App\Http\Controllers\Api\ApiController;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: "/v1/logout",
    summary: "Logout user",
    description: "Revoke current user token",
    security: [["bearerAuth" => []]],
    tags: ["Authentication"]
)]
#[OA\Response(
    response: 200,
    description: "Logged out successfully",
    content: new OA\JsonContent(
        properties: [
            new OA\Property(property: "success", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Logged out successfully")
        ]
    )
)]
#[OA\Response(response: 401, description: "Unauthenticated")]
final class LogoutController extends ApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return $this->success(message: 'Logged out successfully');
    }
}