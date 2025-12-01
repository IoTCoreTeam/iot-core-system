<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $userData): User
    {
        try {
            $user = User::create($userData);

            Log::info('User registered successfully', [
                'registered_user_id' => $user->id,
                'email' => $user->email,
            ]);

            return $user;
        } catch (\Throwable $e) {
            Log::error('Failed to register user', [
                'email' => $userData['email'] ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function login(array $credentials): array
    {
        try {
            $user = User::where('email', $credentials['email'])->first();

            Log::info('Login attempt received', [
                'email' => $credentials['email'],
                'user_exists' => (bool) $user,
            ]);

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                Log::warning('Login failed due to invalid credentials', [
                    'email' => $credentials['email'],
                ]);

                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            Auth::login($user);

            $token = $user->createToken('frontend-token')->accessToken;

            Log::info('User logged in successfully', [
                'user_id' => $user->id,
            ]);

            return [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to login user', [
                'email' => $credentials['email'] ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function getAuthenticatedUser(): User
    {
        try {
            $user = User::where('id', Auth::id())->firstOrFail();

            Log::info('Fetched authenticated user data', [
                'user_id' => $user->id,
            ]);

            return $user;
        } catch (\Throwable $e) {
            Log::error('Failed to fetch user data', [
                'auth_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function logout(?User $authUser): void
    {
        try {
            if ($authUser) {
                $authUser->token()->revoke();

                Log::info('User logged out successfully', [
                    'user_id' => $authUser->id,
                ]);
            } else {
                Log::notice('Logout called without an authenticated user');
            }
        } catch (\Throwable $e) {
            Log::error('Failed to logout', [
                'user_id' => $authUser?->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function changePassword(User $user, array $payload): void
    {
        try {
            DB::transaction(function () use ($user, $payload) {
                if (! Hash::check($payload['current_password'], $user->password)) {
                    Log::warning('Password change rejected due to invalid current password', [
                        'user_id' => $user->id,
                    ]);

                    throw ValidationException::withMessages([
                        'current_password' => ['The current password is incorrect.'],
                    ]);
                }

                $user->forceFill([
                    'password' => $payload['new_password'],
                ])->save();

                Log::info('User changed password successfully', [
                    'user_id' => $user->id,
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Failed to change password', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
