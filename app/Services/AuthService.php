<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Attempt to authenticate a user and generate a JWT token
     *
     * @param array $credentials
     * @return array
     * @throws \Exception
     */
    public function attemptLogin(array $credentials): array
    {
        try {
            $validator = Validator::make($credentials, [
                'email' => 'required|string|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                throw new \Exception('Validation failed: ' . json_encode($validator->errors()), 422);
            }

            // Check if user exists
            $user = User::where('email', $credentials['email'])->first();
            if (!$user) {
                throw new \Exception('Invalid credentials', 401);
            }

            // Attempt to authenticate
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new \Exception('Invalid credentials', 401);
            }

            // Get authenticated user
            $user = Auth::user();

            $ttl = config('jwt.ttl', 60); // Default to 60 minutes if not set

            return [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $ttl * 60, // Convert minutes to seconds
                'user' => $user
            ];
        } catch (JWTException $e) {
            \Log::error('Login error: ' . $e->getMessage());
            throw new \Exception('Could not create token: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());

            // Rethrow with original status code if it's a validation or credential error
            if ($e->getCode() == 422 || $e->getCode() == 401) {
                throw $e;
            }

            throw new \Exception('Login failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Register a new user
     *
     * @param array $userData
     * @return array
     * @throws \Exception
     */
    public function registerUser(array $userData): array
    {
        try {
            $validator = Validator::make($userData, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'sometimes|string|in:user,admin',
            ]);

            if ($validator->fails()) {
                throw new \Exception('Validation failed: ' . json_encode($validator->errors()), 422);
            }

            // Create the user
            $user = new User();
            $user->name = $userData['name'];
            $user->email = $userData['email'];
            $user->password = Hash::make($userData['password']);
            $user->role = $userData['role'] ?? 'user';
            $user->save();

            // Generate token
            $token = JWTAuth::fromUser($user);

            // Get TTL from config
            $ttl = config('jwt.ttl', 60); // Default to 60 minutes if not set

            return [
                'status' => 'success',
                'message' => 'User registered successfully',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $ttl * 60, // Convert minutes to seconds
                'user' => $user
            ];
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());

            if ($e->getCode() == 422) {
                throw $e;
            }

            throw new \Exception('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Logout the user (invalidate the token)
     *
     * @return array
     * @throws \Exception
     */
    public function logout(): array
    {
        try {
            JWTAuth::parseToken()->invalidate();
            return [
                'status' => 'success',
                'message' => 'Successfully logged out'
            ];
        } catch (JWTException $e) {
            throw new \Exception('Failed to logout: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Refresh the user's token
     *
     * @return array
     * @throws \Exception
     */
    public function refreshToken(): array
    {
        try {
            $token = JWTAuth::parseToken()->refresh();
            $user = Auth::user();

            // Get TTL from config
            $ttl = config('jwt.ttl', 60); // Default to 60 minutes if not set

            return [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $ttl * 60, // Convert minutes to seconds
                'user' => $user
            ];
        } catch (JWTException $e) {
            throw new \Exception('Could not refresh token: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get the authenticated user
     *
     * @return array
     * @throws \Exception
     */
    public function getAuthenticatedUser(): array
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        return [
            'status' => 'success',
            'user' => $user
        ];
    }

}
