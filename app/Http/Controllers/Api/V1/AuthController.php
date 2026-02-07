<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\{RegistredRequest, LoginRequest};
use Illuminate\Http\{JsonResponse, Request};
use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

final class AuthController extends ApiController
{
    public function register(RegistredRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->created([
            'token' => $token,
            'data' => $user,
        ], 'User registered successfully');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || Hash::check($request->password, $user->password)) {

            return $this->unauthorized('Invalid crendatials');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'data' => $user,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return $this->success(message: 'Logged out successfully');
    }
}