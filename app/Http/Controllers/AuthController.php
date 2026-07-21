<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    #[OA\Post(
        path: "/api/login",
        summary: "Authenticate a user and return a JWT token",
        requestBody: new OA\RequestBody(
            description: "User credentials",
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password")
                ]
            )
        ),
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Authentication successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "access_token", type: "string"),
                        new OA\Property(property: "token_type", type: "string", example: "bearer"),
                        new OA\Property(property: "expires_in", type: "integer", example: 3600),
                        new OA\Property(property: "user", type: "object", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Server error")
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $result = $this->authService->attemptLogin(
            $request->only('email', 'password')
        );

        return response()->json($result);
    }

    #[OA\Post(
        path: "/api/register",
        summary: "Register a new user and return a JWT token",
        requestBody: new OA\RequestBody(
            description: "New user data",
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "John Doe"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "password"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "password"),
                    new OA\Property(property: "role", type: "string", example: "user", enum: ["user", "admin"])
                ]
            )
        ),
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 201,
                description: "Registration successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "User registered successfully"),
                        new OA\Property(property: "access_token", type: "string"),
                        new OA\Property(property: "token_type", type: "string", example: "bearer"),
                        new OA\Property(property: "expires_in", type: "integer", example: 3600),
                        new OA\Property(property: "user", type: "object", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 500, description: "Server error")
        ]
    )]
    public function register(Request $request): JsonResponse
    {
        $result = $this->authService->registerUser($request->all());

        return response()->json($result, 201);
    }

    #[OA\Post(
        path: "/api/logout",
        summary: "Invalidate the user's JWT token",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logout successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "message", type: "string", example: "Successfully logged out")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 500, description: "Server error")
        ]
    )]
    public function logout(): JsonResponse
    {
        $result = $this->authService->logout();

        return response()->json($result);
    }

    #[OA\Post(
        path: "/api/refresh",
        summary: "Refresh the user's JWT token",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Token refreshed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "access_token", type: "string"),
                        new OA\Property(property: "token_type", type: "string", example: "bearer"),
                        new OA\Property(property: "expires_in", type: "integer", example: 3600),
                        new OA\Property(property: "user", type: "object", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 500, description: "Server error")
        ]
    )]
    public function refresh(): JsonResponse
    {
        $result = $this->authService->refreshToken();

        return response()->json($result);
    }

    #[OA\Get(
        path: "/api/user",
        summary: "Get the authenticated user's data",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "User data",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "string", example: "success"),
                        new OA\Property(property: "user", type: "object", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 500, description: "Server error")
        ]
    )]
    public function user(): JsonResponse
    {
        $result = $this->authService->getAuthenticatedUser();

        return response()->json($result);
    }
}
